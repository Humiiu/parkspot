<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirebaseService;
use App\Services\ParkingService;
use App\Services\ReceiptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParkingController extends Controller
{
    protected FirebaseService $firebase;
    protected ParkingService $parking;

    public function __construct(FirebaseService $firebase, ParkingService $parking)
    {
        $this->firebase = $firebase;
        $this->parking = $parking;
    }

    /**
     * Ambil data seluruh slot parkir dengan status warna (hijau, oranye, merah).
     */
    public function getSlots(Request $request): JsonResponse
    {
        $slots = $this->firebase->get('tb_slot_parkir') ?? [];
        $areaFilter = $request->query('id_area');

        $result = [];
        foreach ($slots as $key => $slot) {
            if ($areaFilter !== null && (string)($slot['id_area'] ?? '') !== (string)$areaFilter) {
                continue;
            }
            $result[] = array_merge($slot, ['key' => $key]);
        }

        // Urutkan berdasarkan urutan jarak
        usort($result, function ($a, $b) {
            return ($a['urutan_jarak'] ?? 999) <=> ($b['urutan_jarak'] ?? 999);
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Ambil daftar area parkir beserta kapasitas dan sisa slot.
     */
    public function getAreas(): JsonResponse
    {
        $areas = $this->firebase->get('tb_area_parkir') ?? [];
        $result = [];

        foreach ($areas as $key => $area) {
            $kapasitas = $area['kapasitas'] ?? 0;
            $terisi = $area['terisi'] ?? 0;
            $tersedia = max(0, $kapasitas - $terisi);

            $result[] = array_merge($area, [
                'key' => $key,
                'tersedia' => $tersedia,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Input kendaraan masuk (Gate In oleh Petugas).
     */
    public function gateIn(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plat_nomor' => 'required|string|max:15',
            'jenis_kendaraan' => 'required|in:mobil,motor',
            'id_area' => 'nullable|integer',
            'id_user' => 'nullable|string',
        ]);

        $res = $this->parking->processGateIn(
            platNomor: $validated['plat_nomor'],
            jenisKendaraan: $validated['jenis_kendaraan'],
            kategori: 'reguler',
            idArea: $validated['id_area'] ?? 1,
            idUser: $validated['id_user'] ?? null
        );

        if (!$res['success']) {
            return response()->json($res, 400);
        }

        return response()->json([
            'success' => true,
            'is_booking' => $res['is_booking'] ?? false,
            'message' => $res['message'] ?? 'Kendaraan berhasil masuk dan slot telah dialokasikan.',
            'data' => $res['data'],
        ], 201);
    }

    /**
     * Cek status tiket dan argo tagihan berjalan (untuk Web Guest scan QR / Member).
     */
    public function getTicketStatus(string $kodeTiket): JsonResponse
    {
        $transaksi = $this->firebase->get("tb_transaksi/{$kodeTiket}");

        if (!$transaksi) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket parkir tidak ditemukan.',
            ], 404);
        }

        $billing = ($transaksi['status'] === 'selesai')
            ? [
                'durasi_jam' => $transaksi['durasi'] ?? 1,
                'total_biaya' => $transaksi['biaya'] ?? 0,
                'status_bayar' => $transaksi['status_bayar'] ?? 'lunas',
            ]
            : $this->parking->calculateCurrentBill($transaksi);

        return response()->json([
            'success' => true,
            'data' => array_merge($transaksi, [
                'billing_sekarang' => $billing,
            ]),
        ]);
    }

    /**
     * Transaksi keluar kendaraan (Gate Out oleh Petugas di kasir palang keluar).
     */
    public function gateOut(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_tiket' => 'required|string',
            'metode_bayar' => 'nullable|in:tunai,qris',
            'id_petugas' => 'nullable|string',
        ]);

        $res = $this->parking->processGateOut(
            kodeTiket: $validated['kode_tiket'],
            metodeBayar: $validated['metode_bayar'] ?? 'tunai',
            idPetugas: $validated['id_petugas'] ?? 'petugas'
        );

        if (!$res['success']) {
            return response()->json($res, 400);
        }

        return response()->json($res);
    }

    /**
     * Reservasi / Booking slot parkir berbatas waktu (Member via Mobile App).
     */
    public function bookSlot(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kode_slot' => 'required|string',
            'plat_nomor' => 'required|string',
            'jam_booking' => 'required|string',
            'id_user' => 'required|string',
            'jenis_kendaraan' => 'required|in:mobil,motor',
        ]);

        $res = $this->parking->processBooking(
            kodeSlot: $validated['kode_slot'],
            platNomor: $validated['plat_nomor'],
            jamBooking: $validated['jam_booking'],
            idUser: $validated['id_user'],
            jenisKendaraan: $validated['jenis_kendaraan']
        );

        if (!$res['success']) {
            return response()->json($res, 400);
        }

        return response()->json($res, 201);
    }

    /**
     * Ambil daftar tarif resmi parkir.
     */
    public function getTariffs(): JsonResponse
    {
        $tariffs = $this->firebase->get('tb_tarif') ?? [];
        return response()->json([
            'success' => true,
            'data' => $tariffs,
        ]);
    }

    /**
     * Periksa dan batalkan otomatis booking yang melewati batas toleransi waktu.
     */
    public function checkBookings(Request $request): JsonResponse
    {
        $tolerance = (int)($request->query('tolerance', 15));
        $result = $this->parking->checkAndCancelExpiredBookings($tolerance);

        return response()->json($result);
    }

    /**
     * Format Karcis Masuk (58mm) siap kirim ke printer thermal.
     */
    public function getEntryReceipt(string $idParkir, ReceiptService $receiptService): JsonResponse
    {
        $transaksi = $this->firebase->get("tb_transaksi/{$idParkir}");

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
        }

        $idArea = $transaksi['id_area'] ?? 1;
        $area = $this->firebase->get("tb_area_parkir/area_{$idArea}");
        $namaArea = $area['nama_area'] ?? "Lantai {$idArea}";

        $receipt = $receiptService->formatEntryTicket($transaksi, $namaArea);

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }

    /**
     * Format Struk Pembayaran Keluar (58mm) siap kirim ke printer thermal.
     */
    public function getExitReceipt(string $idParkir, Request $request, ReceiptService $receiptService): JsonResponse
    {
        $transaksi = $this->firebase->get("tb_transaksi/{$idParkir}");

        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
        }

        $petugas = $request->query('petugas', 'Petugas 1');
        $receipt = $receiptService->formatExitReceipt($transaksi, $petugas);

        return response()->json([
            'success' => true,
            'data' => $receipt,
        ]);
    }
}

