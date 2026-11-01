<?php

namespace Tests\Feature;

use Tests\TestCase;

class ParkspotApiTest extends TestCase
{
    public function test_auth_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'username' => 'admin',
                        'role' => 'admin',
                    ],
                ],
            ]);
    }

    public function test_get_slots_returns_list_with_three_colors(): void
    {
        $response = $this->getJson('/api/parking/slots');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['kode_slot', 'id_area', 'status_lampu', 'urutan_jarak'],
                ],
            ]);
    }

    public function test_full_parking_flow_gate_in_and_gate_out(): void
    {
        // 1. Gate In
        $inResponse = $this->postJson('/api/parking/gate-in', [
            'plat_nomor' => 'B 7777 UKK',
            'jenis_kendaraan' => 'mobil',
            'id_area' => 1,
        ]);

        $inResponse->assertStatus(201);
        $ticketCode = $inResponse->json('data.id_parkir');
        $this->assertNotEmpty($ticketCode);

        // 2. Check Ticket Status
        $statusResponse = $this->getJson("/api/parking/ticket/{$ticketCode}");
        $statusResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'plat_nomor' => 'B 7777 UKK',
                    'status' => 'aktif',
                ],
            ]);

        // 3. Gate Out
        $outResponse = $this->postJson('/api/parking/gate-out', [
            'kode_tiket' => $ticketCode,
            'metode_bayar' => 'tunai',
        ]);

        $outResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'status' => 'selesai',
                    'status_bayar' => 'lunas',
                ],
            ]);
    }

    public function test_owner_summary_endpoint(): void
    {
        $response = $this->getJson('/api/owner/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'hari_ini' => ['total_kendaraan', 'total_pendapatan'],
                    'bulan_ini' => ['total_kendaraan', 'total_pendapatan'],
                    'keterisian_slot' => ['total', 'kosong', 'terisi', 'booking', 'aktif_parkir'],
                ],
            ]);
    }
}

