<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected string $apiUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->clientKey = config('midtrans.client_key');
        $this->apiUrl = config('midtrans.api_url', 'https://api.sandbox.midtrans.com');
        $this->isProduction = (bool)config('midtrans.is_production', false);
    }

    /**
     * Buat request transaksi QRIS dinamis ke Midtrans Core API.
     */
    public function createQrisCharge(string $orderId, int $grossAmount): array
    {
        try {
            $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

            $payload = [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $grossAmount,
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(15)->post("{$this->apiUrl}/v2/charge", $payload);

            if ($response->successful()) {
                $data = $response->json();
                $qrUrl = null;

                if (!empty($data['actions']) && is_array($data['actions'])) {
                    foreach ($data['actions'] as $action) {
                        if (($action['name'] ?? '') === 'generate-qr-code') {
                            $qrUrl = $action['url'] ?? null;
                            break;
                        }
                    }
                }

                return [
                    'success' => true,
                    'status_code' => $data['status_code'] ?? '201',
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'order_id' => $data['order_id'] ?? $orderId,
                    'gross_amount' => (int)($data['gross_amount'] ?? $grossAmount),
                    'qr_string' => $data['qr_string'] ?? null,
                    'qr_url' => $qrUrl,
                    'expiry_time' => $data['expiry_time'] ?? null,
                    'raw' => $data,
                ];
            }

            Log::error('Midtrans QRIS Charge failed: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json('status_message') ?? 'Gagal membuat transaksi QRIS.',
                'raw' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('Midtrans QRIS Charge Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Cek status transaksi pembayaran langsung ke Midtrans API.
     */
    public function checkStatus(string $orderId): array
    {
        try {
            $authHeader = 'Basic ' . base64_encode($this->serverKey . ':');

            $response = Http::withHeaders([
                'Authorization' => $authHeader,
                'Accept' => 'application/json',
            ])->timeout(10)->get("{$this->apiUrl}/v2/{$orderId}/status");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('status_message') ?? 'Gagal mengecek status transaksi.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verifikasi keaslian notifikasi Webhook dari Midtrans menggunakan SHA-512.
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        $input = $orderId . $statusCode . $grossAmount . $this->serverKey;
        $expectedHash = hash('sha512', $input);

        return hash_equals($expectedHash, $signatureKey);
    }
}

