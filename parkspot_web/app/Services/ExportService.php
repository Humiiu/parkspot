<?php

namespace App\Services;

use Carbon\Carbon;

class ExportService
{
    protected FirebaseService $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Ambil dan filter data transaksi dari Firebase RTDB.
     */
    public function getFilteredTransactions(array $filters = []): array
    {
        $all = $this->firebase->get('tb_transaksi') ?? [];
        $result = [];

        $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : null;
        $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : null;
        $status = $filters['status'] ?? null;
        $jenisKendaraan = $filters['jenis_kendaraan'] ?? null;
        $metodeBayar = $filters['metode_bayar'] ?? null;

        foreach ($all as $key => $t) {
            $t['id_transaksi'] = $t['id_parkir'] ?? $key;

            $waktu = $t['waktu_masuk'] ?? null;
            if ($waktu) {
                $waktuCarbon = Carbon::parse($waktu);
                if ($startDate && $waktuCarbon->lt($startDate)) {
                    continue;
                }
                if ($endDate && $waktuCarbon->gt($endDate)) {
                    continue;
                }
            } elseif ($startDate || $endDate) {
                // Jika transaksi belum memiliki waktu masuk (misal booking belum tiba), lewati jika ada filter tanggal
                continue;
            }

            if ($status && ($t['status'] ?? '') !== $status) {
                continue;
            }

            if ($jenisKendaraan && strtolower($t['jenis_kendaraan'] ?? '') !== strtolower($jenisKendaraan)) {
                continue;
            }

            if ($metodeBayar && strtolower($t['metode_bayar'] ?? '') !== strtolower($metodeBayar)) {
                continue;
            }

            $result[] = $t;
        }

        // Urutkan dari waktu masuk terbaru
        usort($result, function ($a, $b) {
            return strcmp($b['waktu_masuk'] ?? '', $a['waktu_masuk'] ?? '');
        });

        return $result;
    }

    /**
     * Hitung metrik finansial dan operasional dari data transaksi.
     */
    public function calculateMetrics(array $transactions): array
    {
        $totalTransaksi = count($transactions);
        $totalSelesai = 0;
        $totalAktif = 0;
        $totalDibatalkan = 0;

        $totalPendapatan = 0;
        $pendapatanTunai = 0;
        $pendapatanQris = 0;

        $totalMobil = 0;
        $totalMotor = 0;
        $totalMenitParkir = 0;

        foreach ($transactions as $t) {
            $status = $t['status'] ?? '';
            $biaya = (int)($t['biaya'] ?? 0);
            $metode = strtolower($t['metode_bayar'] ?? '');
            $jenis = strtolower($t['jenis_kendaraan'] ?? '');

            if ($status === 'selesai') {
                $totalSelesai++;
                $totalPendapatan += $biaya;

                if ($metode === 'tunai') {
                    $pendapatanTunai += $biaya;
                } elseif ($metode === 'qris') {
                    $pendapatanQris += $biaya;
                }

                $menit = (int)($t['durasi_menit'] ?? 0) + ((int)($t['durasi_jam'] ?? 0) * 60);
                $totalMenitParkir += $menit;
            } elseif ($status === 'aktif') {
                $totalAktif++;
            } elseif (in_array($status, ['dibatalkan', 'dibatalkan_otomatis'])) {
                $totalDibatalkan++;
            }

            if ($jenis === 'mobil') {
                $totalMobil++;
            } elseif ($jenis === 'motor') {
                $totalMotor++;
            }
        }

        $rataRataMenit = $totalSelesai > 0 ? round($totalMenitParkir / $totalSelesai) : 0;
        $rataRataPendapatan = $totalSelesai > 0 ? round($totalPendapatan / $totalSelesai) : 0;

        return [
            'total_transaksi' => $totalTransaksi,
            'total_selesai' => $totalSelesai,
            'total_aktif' => $totalAktif,
            'total_dibatalkan' => $totalDibatalkan,
            'total_pendapatan' => $totalPendapatan,
            'pendapatan_tunai' => $pendapatanTunai,
            'pendapatan_qris' => $pendapatanQris,
            'total_mobil' => $totalMobil,
            'total_motor' => $totalMotor,
            'rata_rata_durasi_menit' => $rataRataMenit,
            'rata_rata_pendapatan' => $rataRataPendapatan,
        ];
    }

    /**
     * Generate format CSV untuk daftar transaksi detail (kompatibel Excel dengan UTF-8 BOM).
     */
    public function generateTransactionsCsv(array $transactions, array $metrics = []): string
    {
        $fp = fopen('php://temp', 'r+');

        // Header Kolom Tabel
        fputcsv($fp, [
            'No',
            'ID Transaksi',
            'Kode Tiket',
            'Plat Nomor',
            'Jenis Kendaraan',
            'Area Parkir',
            'Slot',
            'Waktu Masuk',
            'Waktu Keluar',
            'Durasi (Jam:Menit)',
            'Tarif Total (Rp)',
            'Metode Bayar',
            'Status Bayar',
            'Status Transaksi',
        ]);

        $no = 1;
        foreach ($transactions as $t) {
            $jam = (int)($t['durasi_jam'] ?? 0);
            $menit = (int)($t['durasi_menit'] ?? 0);
            $durasiStr = sprintf('%d Jam %02d Mnt', $jam, $menit);

            fputcsv($fp, [
                $no++,
                $t['id_parkir'] ?? ($t['id_transaksi'] ?? '-'),
                $t['kode_tiket'] ?? ($t['id_parkir'] ?? '-'),
                strtoupper($t['plat_nomor'] ?? '-'),
                strtoupper($t['jenis_kendaraan'] ?? '-'),
                $t['nama_area'] ?? ('Area ' . ($t['id_area'] ?? '1')),
                $t['id_slot'] ?? '-',
                $t['waktu_masuk'] ?? '-',
                $t['waktu_keluar'] ?? '-',
                $durasiStr,
                number_format((int)($t['biaya'] ?? 0), 0, ',', '.'),
                strtoupper($t['metode_bayar'] ?? '-'),
                strtoupper($t['status_bayar'] ?? 'BELUM'),
                strtoupper($t['status'] ?? '-'),
            ]);
        }

        // Jika metrik tersedia, tambahkan baris ringkasan di bawah
        if (!empty($metrics)) {
            fputcsv($fp, []); // Baris kosong
            fputcsv($fp, ['=== RINGKASAN REKAPITULASI ===']);
            fputcsv($fp, ['Total Transaksi', $metrics['total_transaksi']]);
            fputcsv($fp, ['Transaksi Selesai', $metrics['total_selesai']]);
            fputcsv($fp, ['Transaksi Aktif', $metrics['total_aktif']]);
            fputcsv($fp, ['Booking Dibatalkan', $metrics['total_dibatalkan']]);
            fputcsv($fp, ['Total Pendapatan (Rp)', number_format($metrics['total_pendapatan'], 0, ',', '.')]);
            fputcsv($fp, ['Pendapatan Tunai (Rp)', number_format($metrics['pendapatan_tunai'], 0, ',', '.')]);
            fputcsv($fp, ['Pendapatan Non-Tunai / QRIS (Rp)', number_format($metrics['pendapatan_qris'], 0, ',', '.')]);
        }

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        // Tambahkan UTF-8 BOM (\xEF\xBB\xBF) agar Microsoft Excel langsung membaca aksen/format dengan benar
        return "\xEF\xBB\xBF" . $csvContent;
    }

    /**
     * Generate format CSV ringkasan eksekutif finansial khusus Owner.
     */
    public function generateFinancialSummaryCsv(array $metrics, array $filters = []): string
    {
        $fp = fopen('php://temp', 'r+');

        fputcsv($fp, ['LAPORAN REKAPITULASI KINERJA FINANSIAL & OPERASIONAL']);
        fputcsv($fp, ['Sistem ParkSpot - Pengelolaan Parkir Terpadu']);
        fputcsv($fp, ['Tanggal Unduh', Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB']);
        fputcsv($fp, ['Periode Filter', ($filters['start_date'] ?? 'Awal') . ' s/d ' . ($filters['end_date'] ?? 'Sekarang')]);
        fputcsv($fp, []);

        fputcsv($fp, ['METRIK KEUANGAN', 'NILAI']);
        fputcsv($fp, ['Total Pendapatan Parkir', 'Rp ' . number_format($metrics['total_pendapatan'], 0, ',', '.')]);
        fputcsv($fp, ['Penerimaan Tunai (Cash)', 'Rp ' . number_format($metrics['pendapatan_tunai'], 0, ',', '.')]);
        fputcsv($fp, ['Penerimaan Non-Tunai (QRIS)', 'Rp ' . number_format($metrics['pendapatan_qris'], 0, ',', '.')]);
        fputcsv($fp, ['Rata-rata Pendapatan per Transaksi', 'Rp ' . number_format($metrics['rata_rata_pendapatan'], 0, ',', '.')]);
        fputcsv($fp, []);

        fputcsv($fp, ['METRIK OPERASIONAL & VOLUME KENDARAAN', 'JUMLAH']);
        fputcsv($fp, ['Total Volume Transaksi', $metrics['total_transaksi']]);
        fputcsv($fp, ['Transaksi Selesai (Lunas)', $metrics['total_selesai']]);
        fputcsv($fp, ['Kendaraan Masih Parkir (Aktif)', $metrics['total_aktif']]);
        fputcsv($fp, ['Booking Dibatalkan Otomatis', $metrics['total_dibatalkan']]);
        fputcsv($fp, ['Volume Mobil', $metrics['total_mobil']]);
        fputcsv($fp, ['Volume Motor', $metrics['total_motor']]);
        fputcsv($fp, ['Rata-rata Durasi Parkir (Menit)', $metrics['rata_rata_durasi_menit'] . ' Menit']);

        rewind($fp);
        $csvContent = stream_get_contents($fp);
        fclose($fp);

        return "\xEF\xBB\xBF" . $csvContent;
    }

    /**
     * Generate format JSON terstruktur untuk API / Mobile App.
     */
    public function generateReportJson(array $transactions, array $metrics, array $filters = []): array
    {
        return [
            'success' => true,
            'metadata' => [
                'generated_at' => Carbon::now()->toIso8601String(),
                'filters' => $filters,
            ],
            'summary' => $metrics,
            'transactions' => $transactions,
        ];
    }
}

