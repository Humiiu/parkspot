<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExportReportTest extends TestCase
{
    public function test_export_transactions_csv(): void
    {
        $response = $this->get('/api/owner/export/transactions?format=csv');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        // Cek UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Cek Header Kolom
        $this->assertStringContainsString('"ID Transaksi"', $content);
        $this->assertStringContainsString('"Kode Tiket"', $content);
        $this->assertStringContainsString('"Plat Nomor"', $content);
        $this->assertStringContainsString('"Jenis Kendaraan"', $content);
        $this->assertStringContainsString('"Tarif Total (Rp)"', $content);
        $this->assertStringContainsString('"Status Transaksi"', $content);

        // Cek Bagian Ringkasan di bawah
        $this->assertStringContainsString('=== RINGKASAN REKAPITULASI ===', $content);
        $this->assertStringContainsString('Total Pendapatan (Rp)', $content);
    }

    public function test_export_transactions_json(): void
    {
        $response = $this->getJson('/api/owner/export/transactions?format=json');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'metadata' => ['generated_at', 'filters'],
                'summary' => [
                    'total_transaksi',
                    'total_selesai',
                    'total_aktif',
                    'total_dibatalkan',
                    'total_pendapatan',
                    'pendapatan_tunai',
                    'pendapatan_qris',
                    'total_mobil',
                    'total_motor',
                ],
                'transactions',
            ]);
    }

    public function test_export_financial_summary_csv(): void
    {
        $response = $this->get('/api/owner/export/financial-summary?format=csv');

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('LAPORAN REKAPITULASI KINERJA FINANSIAL & OPERASIONAL', $content);
        $this->assertStringContainsString('Total Pendapatan Parkir', $content);
        $this->assertStringContainsString('Penerimaan Tunai (Cash)', $content);
        $this->assertStringContainsString('Penerimaan Non-Tunai (QRIS)', $content);
    }

    public function test_artisan_export_command(): void
    {
        $this->artisan('parkspot:export-report', [
            '--format' => 'json',
            '--type' => 'summary',
        ])->assertSuccessful();
    }
}
