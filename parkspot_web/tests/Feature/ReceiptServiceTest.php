<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReceiptServiceTest extends TestCase
{
    public function test_entry_and_exit_thermal_receipt_formatting(): void
    {
        // 1. Gate In kendaraan
        $inResponse = $this->postJson('/api/parking/gate-in', [
            'plat_nomor' => 'B 8888 STR',
            'jenis_kendaraan' => 'mobil',
            'id_area' => 1,
        ]);
        $inResponse->assertStatus(201);
        $ticketId = $inResponse->json('data.id_parkir');

        // 2. Cek Karcis Masuk (58mm)
        $entryReceiptResponse = $this->getJson("/api/parking/receipt/entry/{$ticketId}");
        $entryReceiptResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'entry_ticket',
                    'width' => 32,
                    'id_parkir' => $ticketId,
                    'plat_nomor' => 'B 8888 STR',
                ],
            ]);

        $rawEntry = $entryReceiptResponse->json('data.raw_text');
        $this->assertStringContainsString('PARKSPOT - TIKET MASUK', $rawEntry);
        $this->assertStringContainsString('B 8888 STR', $rawEntry);
        $this->assertStringContainsString($ticketId, $rawEntry);
        $this->assertNotEmpty($entryReceiptResponse->json('data.qr_data'));

        // Pastikan setiap baris teks tidak melebihi 32 karakter (standar kertas 58mm)
        $lines = explode("\n", $rawEntry);
        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(32, strlen($line), "Baris melebihi 32 karakter: '{$line}'");
        }

        // 3. Gate Out
        $outResponse = $this->postJson('/api/parking/gate-out', [
            'kode_tiket' => $ticketId,
            'metode_bayar' => 'tunai',
        ]);
        $outResponse->assertStatus(200);

        // 4. Cek Struk Bukti Bayar Keluar (58mm)
        $exitReceiptResponse = $this->getJson("/api/parking/receipt/exit/{$ticketId}?petugas=Jundi");
        $exitReceiptResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'exit_receipt',
                    'width' => 32,
                    'id_parkir' => $ticketId,
                    'status_bayar' => 'LUNAS',
                ],
            ]);

        $rawExit = $exitReceiptResponse->json('data.raw_text');
        $this->assertStringContainsString('PARKSPOT - BUKTI BAYAR', $rawExit);
        $this->assertStringContainsString('TOTAL BAYAR', $rawExit);
        $this->assertStringContainsString('Petugas: Jundi', $rawExit);

        // Pastikan baris struk keluar juga tidak melebihi 32 karakter
        $exitLines = explode("\n", $rawExit);
        foreach ($exitLines as $line) {
            $this->assertLessThanOrEqual(32, strlen($line), "Baris struk keluar melebihi 32 karakter: '{$line}'");
        }
    }
}

