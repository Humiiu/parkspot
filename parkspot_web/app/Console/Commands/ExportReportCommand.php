<?php

namespace App\Console\Commands;

use App\Services\ExportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExportReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parkspot:export-report
                            {--from= : Tanggal awal filter (YYYY-MM-DD)}
                            {--to= : Tanggal akhir filter (YYYY-MM-DD)}
                            {--format=csv : Format ekspor (csv atau json)}
                            {--type=transactions : Tipe laporan (transactions atau summary)}
                            {--output= : Jalur file output khusus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ekspor rekapitulasi transaksi & keuangan parkir ParkSpot (CSV/JSON)';

    /**
     * Execute the console command.
     */
    public function handle(ExportService $export): int
    {
        $from = $this->option('from');
        $to = $this->option('to');
        $format = strtolower($this->option('format') ?: 'csv');
        $type = strtolower($this->option('type') ?: 'transactions');
        $output = $this->option('output');

        $this->info("Menyiapkan ekspor data ParkSpot...");
        $this->line("Periode : " . ($from ?: 'Awal') . " s/d " . ($to ?: 'Sekarang'));
        $this->line("Format  : " . strtoupper($format));
        $this->line("Tipe    : " . strtoupper($type));

        $filters = [
            'start_date' => $from,
            'end_date' => $to,
        ];

        $transactions = $export->getFilteredTransactions($filters);
        $metrics = $export->calculateMetrics($transactions);

        // Tampilkan metrik ringkas di terminal
        $this->newLine();
        $this->table(
            ['Metrik Finansial & Operasional', 'Nilai'],
            [
                ['Total Transaksi', $metrics['total_transaksi']],
                ['Transaksi Selesai', $metrics['total_selesai']],
                ['Transaksi Aktif (Parkir)', $metrics['total_aktif']],
                ['Booking Dibatalkan', $metrics['total_dibatalkan']],
                ['Total Pendapatan', 'Rp ' . number_format($metrics['total_pendapatan'], 0, ',', '.')],
                ['Penerimaan Tunai', 'Rp ' . number_format($metrics['pendapatan_tunai'], 0, ',', '.')],
                ['Penerimaan QRIS', 'Rp ' . number_format($metrics['pendapatan_qris'], 0, ',', '.')],
                ['Volume Mobil / Motor', "{$metrics['total_mobil']} Mobil / {$metrics['total_motor']} Motor"],
            ]
        );

        // Siapkan konten ekspor
        if ($format === 'json') {
            if ($type === 'summary') {
                $content = json_encode([
                    'success' => true,
                    'metadata' => ['generated_at' => Carbon::now()->toIso8601String(), 'filters' => $filters],
                    'summary' => $metrics,
                ], JSON_PRETTY_PRINT);
            } else {
                $content = json_encode($export->generateReportJson($transactions, $metrics, $filters), JSON_PRETTY_PRINT);
            }
            $extension = 'json';
        } else {
            if ($type === 'summary') {
                $content = $export->generateFinancialSummaryCsv($metrics, $filters);
            } else {
                $content = $export->generateTransactionsCsv($transactions, $metrics);
            }
            $extension = 'csv';
        }

        // Tentukan path penyimpanan
        if ($output) {
            $filePath = $output;
        } else {
            $exportDir = storage_path('app/exports');
            if (!File::isDirectory($exportDir)) {
                File::makeDirectory($exportDir, 0755, true);
            }
            $fileName = "parkspot_{$type}_" . date('Ymd_His') . ".{$extension}";
            $filePath = $exportDir . DIRECTORY_SEPARATOR . $fileName;
        }

        File::put($filePath, $content);

        $this->info("✓ Berhasil diekspor ke: {$filePath}");
        return Command::SUCCESS;
    }
}

