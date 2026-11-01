<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use App\Services\FirebaseService;
use App\Services\ParkingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    protected FirebaseService $firebase;
    protected ParkingService $parking;

    public function __construct(FirebaseService $firebase, ParkingService $parking)
    {
        $this->firebase = $firebase;
        $this->parking = $parking;
    }

    // =========================================================================
    // MODUL CRUD USER (Admin)
    // =========================================================================

    public function getUsers(): JsonResponse
    {
        $users = $this->firebase->get('tb_user') ?? [];
        $result = [];
        foreach ($users as $key => $u) {
            unset($u['password']);
            $result[] = array_merge($u, ['key' => $key]);
        }
        return response()->json(['success' => true, 'data' => $result]);
    }

    public function saveUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_user' => 'nullable|string',
            'nama_lengkap' => 'required|string|max:100',
            'username' => 'required|string|max:50',
            'password' => 'nullable|string|min:4',
            'role' => 'required|in:admin,petugas,owner,member',
            'status_aktif' => 'required|boolean',
        ]);

        $idUser = $validated['id_user'] ?? ('user_' . Str::lower(Str::random(6)));
        $existing = $this->firebase->get("tb_user/{$idUser}") ?? [];

        $payload = [
            'id_user' => $idUser,
            'nama_lengkap' => $validated['nama_lengkap'],
            'username' => strtolower($validated['username']),
            'role' => $validated['role'],
            'status_aktif' => (int)$validated['status_aktif'],
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        } elseif (!empty($existing['password'])) {
            $payload['password'] = $existing['password'];
        } else {
            $payload['password'] = Hash::make('password123');
        }

        $this->firebase->set("tb_user/{$idUser}", $payload);

        $this->parking->logActivity('admin', "Admin menyimpan data user: {$validated['nama_lengkap']} ({$validated['role']})");

        unset($payload['password']);
        return response()->json(['success' => true, 'message' => 'User berhasil disimpan.', 'data' => $payload]);
    }

    public function deleteUser(string $idUser): JsonResponse
    {
        $this->firebase->delete("tb_user/{$idUser}");
        $this->parking->logActivity('admin', "Admin menghapus data user ID: {$idUser}");
        return response()->json(['success' => true, 'message' => 'User berhasil dihapus.']);
    }

    // =========================================================================
    // MODUL CRUD TARIF (Admin)
    // =========================================================================

    public function updateTariff(Request $request, string $key): JsonResponse
    {
        $validated = $request->validate([
            'tarif_per_jam' => 'required|integer|min:0',
        ]);

        $this->firebase->update("tb_tarif/{$key}", [
            'tarif_per_jam' => $validated['tarif_per_jam'],
        ]);

        $this->parking->logActivity('admin', "Admin mengubah tarif {$key} menjadi Rp" . number_format($validated['tarif_per_jam']));

        return response()->json(['success' => true, 'message' => 'Tarif berhasil diperbarui.']);
    }

    // =========================================================================
    // MODUL AUDIT LOG (Admin)
    // =========================================================================

    public function getLogs(): JsonResponse
    {
        $logs = $this->firebase->get('tb_log_aktivitas') ?? [];
        $result = array_values($logs);

        usort($result, function ($a, $b) {
            return strcmp($b['waktu_aktivitas'] ?? '', $a['waktu_aktivitas'] ?? '');
        });

        return response()->json(['success' => true, 'data' => $result]);
    }

    // =========================================================================
    // MODUL DASHBOARD & REKAP LAPORAN (Owner)
    // =========================================================================

    public function getOwnerSummary(): JsonResponse
    {
        $transactions = $this->firebase->get('tb_transaksi') ?? [];
        $slots = $this->firebase->get('tb_slot_parkir') ?? [];

        $totalKendaraanHariIni = 0;
        $totalPendapatanHariIni = 0;
        $totalKendaraanBulanIni = 0;
        $totalPendapatanBulanIni = 0;
        $kendaraanAktifParkir = 0;

        $today = Carbon::today();
        $thisMonth = Carbon::now()->format('Y-m');

        foreach ($transactions as $t) {
            $waktuMasuk = isset($t['waktu_masuk']) ? Carbon::parse($t['waktu_masuk']) : null;
            $status = $t['status'] ?? '';
            $biaya = $t['biaya'] ?? 0;

            if ($status === 'aktif') {
                $kendaraanAktifParkir++;
            }

            if ($waktuMasuk) {
                if ($waktuMasuk->isToday()) {
                    $totalKendaraanHariIni++;
                    if ($status === 'selesai') {
                        $totalPendapatanHariIni += $biaya;
                    }
                }

                if ($waktuMasuk->format('Y-m') === $thisMonth) {
                    $totalKendaraanBulanIni++;
                    if ($status === 'selesai') {
                        $totalPendapatanBulanIni += $biaya;
                    }
                }
            }
        }

        $totalSlot = count($slots);
        $slotKosong = count(array_filter($slots, fn($s) => ($s['status_lampu'] ?? '') === 'hijau'));
        $slotTerisi = count(array_filter($slots, fn($s) => ($s['status_lampu'] ?? '') === 'merah'));
        $slotBooking = count(array_filter($slots, fn($s) => ($s['status_lampu'] ?? '') === 'oranye'));

        return response()->json([
            'success' => true,
            'data' => [
                'hari_ini' => [
                    'total_kendaraan' => $totalKendaraanHariIni,
                    'total_pendapatan' => $totalPendapatanHariIni,
                ],
                'bulan_ini' => [
                    'total_kendaraan' => $totalKendaraanBulanIni,
                    'total_pendapatan' => $totalPendapatanBulanIni,
                ],
                'keterisian_slot' => [
                    'total' => $totalSlot,
                    'kosong' => $slotKosong,
                    'terisi' => $slotTerisi,
                    'booking' => $slotBooking,
                    'aktif_parkir' => $kendaraanAktifParkir,
                ],
            ],
        ]);
    }

    public function getReports(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $transactions = $this->firebase->get('tb_transaksi') ?? [];
        $filtered = [];
        $totalOmzet = 0;

        foreach ($transactions as $key => $t) {
            $waktu = $t['waktu_masuk'] ?? '';
            if (empty($waktu)) continue;

            $dateOnly = substr($waktu, 0, 10);

            if ($startDate && $dateOnly < $startDate) continue;
            if ($endDate && $dateOnly > $endDate) continue;

            $filtered[] = array_merge($t, ['key' => $key]);
            if (($t['status'] ?? '') === 'selesai') {
                $totalOmzet += ($t['biaya'] ?? 0);
            }
        }

        usort($filtered, function ($a, $b) {
            return strcmp($b['waktu_masuk'] ?? '', $a['waktu_masuk'] ?? '');
        });

        return response()->json([
            'success' => true,
            'summary' => [
                'total_transaksi' => count($filtered),
                'total_omzet' => $totalOmzet,
            ],
            'data' => $filtered,
        ]);
    }

    /**
     * Ekspor detail transaksi parkir (CSV / JSON) untuk Owner.
     */
    public function exportTransactions(Request $request, ExportService $export): Response|JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'status' => $request->query('status'),
            'jenis_kendaraan' => $request->query('jenis_kendaraan'),
            'metode_bayar' => $request->query('metode_bayar'),
        ];

        $format = strtolower($request->query('format', 'csv'));
        $transactions = $export->getFilteredTransactions($filters);
        $metrics = $export->calculateMetrics($transactions);

        if ($format === 'json') {
            return response()->json($export->generateReportJson($transactions, $metrics, $filters));
        }

        $csv = $export->generateTransactionsCsv($transactions, $metrics);
        $filename = 'laporan_transaksi_parkspot_' . date('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    /**
     * Ekspor ringkasan eksekutif finansial (CSV / JSON) untuk Owner.
     */
    public function exportFinancialSummary(Request $request, ExportService $export): Response|JsonResponse
    {
        $filters = [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
        ];

        $format = strtolower($request->query('format', 'csv'));
        $transactions = $export->getFilteredTransactions($filters);
        $metrics = $export->calculateMetrics($transactions);

        if ($format === 'json') {
            return response()->json([
                'success' => true,
                'metadata' => [
                    'generated_at' => Carbon::now()->toIso8601String(),
                    'filters' => $filters,
                ],
                'summary' => $metrics,
            ]);
        }

        $csv = $export->generateFinancialSummaryCsv($metrics, $filters);
        $filename = 'ringkasan_finansial_parkspot_' . date('Ymd_His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}

