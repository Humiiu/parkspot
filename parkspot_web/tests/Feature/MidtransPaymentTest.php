<?php

namespace Tests\Feature;

use Tests\TestCase;

class MidtransPaymentTest extends TestCase
{
    public function test_create_qris_for_active_ticket(): void
    {
        // 1. Masuk kendaraan
        $inResponse = $this->postJson('/api/parking/gate-in', [
            'plat_nomor' => 'B 5555 JFM',
            'jenis_kendaraan' => 'mobil',
            'id_area' => 1,
        ]);
        $inResponse->assertStatus(201);
        $ticketId = $inResponse->json('data.id_parkir');

        // 2. Request QRIS
        $qrisResponse = $this->postJson('/api/payment/create-qris', [
            'id_parkir' => $ticketId,
        ]);

        $qrisResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'QRIS berhasil dibuat.',
                'data' => [
                    'id_parkir' => $ticketId,
                ],
            ]);

        $this->assertNotEmpty($qrisResponse->json('data.qr_string'));
        $this->assertNotEmpty($qrisResponse->json('data.qr_url'));

        // 3. Simulasi Pelunasan
        $simResponse = $this->postJson("/api/payment/simulate-success/{$ticketId}");
        $simResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status_bayar' => 'lunas',
                ],
            ]);

        // 4. Gate Out (harus berhasil karena sudah lunas)
        $outResponse = $this->postJson('/api/parking/gate-out', [
            'kode_tiket' => $ticketId,
            'metode_bayar' => 'qris',
        ]);
        $outResponse->assertStatus(200);
    }
}
