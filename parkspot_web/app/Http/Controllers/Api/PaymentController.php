<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Services\MidtransService;
use App\Services\ParkingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected FirebaseService $firebase;
    protected ParkingService $parking;
    protected MidtransService $midtrans;

    public function __construct(
        FirebaseService $firebase,
        ParkingService $parking,
        MidtransService $midtrans
    ) {
        $this->firebase = $firebase;
        $this->parking = $parking;
        $this->midtrans = $midtrans;
    }

    /**
     * Buat kode QRIS Midtrans untuk tiket parkir tertentu.
     */
    public function createQris(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_parkir' => 'required|string',
        ]);

        $idParkir = $validated['id_parkir'];
        $transaksi = $this->firebase->get("tb_transaksi/{$idParkir}");

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket parkir tidak ditemukan.',
            ], 404);
        }

        if (($transaksi['status_bayar'] ?? '') === 'lunas') {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini sudah lunas sebelumnya.',
                'data' => $transaksi,
            ], 400);
        }

        // Hitung tagihan terkini
        $bill = $this->parking->calculateCurrentBill($transaksi);
        $totalBiaya = max(1000, $bill['total_biaya']); // Minimal Rp 1.000 untuk Midtrans

        // Buat order_id unik untuk Midtrans (idParkir + timestamp unik agar bisa di-charge ulang jika expired)
        $midtransOrderId = $idParkir . '-QR' . time();

        $chargeResult = $this->midtrans->createQrisCharge($midtransOrderId, $totalBiaya);

        if (!$chargeResult['success']) {
            return response()->json($chargeResult, 400);
        }

        // Simpan referensi pembayaran di Firebase
        $this->firebase->update("tb_transaksi/{$idParkir}", [
            'midtrans_order_id' => $midtransOrderId,
            'qr_string' => $chargeResult['qr_string'],
            'qr_url' => $chargeResult['qr_url'],
            'tagihan_terakhir' => $totalBiaya,
            'expiry_qris' => $chargeResult['expiry_time'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QRIS berhasil dibuat.',
            'data' => [
                'id_parkir' => $idParkir,
                'midtrans_order_id' => $midtransOrderId,
                'plat_nomor' => $transaksi['plat_nomor'] ?? '',
                'total_biaya' => $totalBiaya,
                'durasi_jam' => $bill['durasi_jam'],
                'qr_string' => $chargeResult['qr_string'],
                'qr_url' => $chargeResult['qr_url'],
                'expiry_time' => $chargeResult['expiry_time'],
                'simulator_url' => 'https://simulator.sandbox.midtrans.com/qris/index',
            ],
        ], 201);
    }

    /**
     * Webhook HTTP Notification dari Midtrans.
     * Dipanggil otomatis oleh server Midtrans saat transaksi dibayar.
     */
    public function handleCallback(Request $request): JsonResponse
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received: ', $payload);

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $transactionStatus = $payload['transaction_status'] ?? '';

        if (empty($orderId)) {
            return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
        }

        // Verifikasi Signature jika signature_key ada
        if (!empty($signatureKey)) {
            $isValid = $this->midtrans->verifySignature($orderId, $statusCode, $grossAmount, $signatureKey);
            if (!$isValid) {
                Log::warning("Midtrans callback signature mismatch for order {$orderId}");
            }
        }

        // Ambil ID tiket dasar (memisahkan akhiran -QRxxxx)
        $idParkir = explode('-QR', $orderId)[0];

        // Jika transaksi berhasil dibayar (settlement / capture)
        if (in_array($transactionStatus, ['settlement', 'capture'])) {
            $this->firebase->update("tb_transaksi/{$idParkir}", [
                'status_bayar' => 'lunas',
                'metode_bayar' => 'qris',
                'waktu_bayar' => Carbon::now()->toDateTimeString(),
                'biaya' => (int)$grossAmount,
            ]);

            $this->parking->logActivity(
                'midtrans',
                "Pembayaran QRIS lunas untuk tiket {$idParkir} sebesar Rp" . number_format((int)$grossAmount)
            );
        }

        return response()->json(['status' => 'OK']);
    }

    /**
     * Endpoint Simulasi Bayar Sukses (Sangat berguna saat demo ujian UKK offline).
     */
    public function simulateSuccess(string $idParkir): JsonResponse
    {
        $transaksi = $this->firebase->get("tb_transaksi/{$idParkir}");

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
        }

        $now = Carbon::now();
        $bill = $this->parking->calculateCurrentBill($transaksi);

        $this->firebase->update("tb_transaksi/{$idParkir}", [
            'status_bayar' => 'lunas',
            'metode_bayar' => 'qris_simulasi',
            'waktu_bayar' => $now->toDateTimeString(),
            'biaya' => $bill['total_biaya'],
        ]);

        $this->parking->logActivity(
            'system',
            "Simulasi pembayaran QRIS berhasil untuk tiket {$idParkir} sebesar Rp" . number_format($bill['total_biaya'])
        );

        return response()->json([
            'success' => true,
            'message' => 'Simulasi pembayaran QRIS berhasil. Tiket telah berstatus LUNAS.',
            'data' => array_merge($transaksi, [
                'status_bayar' => 'lunas',
                'metode_bayar' => 'qris_simulasi',
                'waktu_bayar' => $now->toDateTimeString(),
                'biaya' => $bill['total_biaya'],
            ]),
        ]);
    }
}

