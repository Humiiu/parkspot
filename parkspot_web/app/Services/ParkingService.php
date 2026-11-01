<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class ParkingService
{
    protected FirebaseService $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Cari slot kosong (hijau) terdekat berdasarkan urutan_jarak.
     */
    public function findNearestAvailableSlot(int|string $idArea = 1): ?array
    {
        $slots = $this->firebase->get('tb_slot_parkir') ?? [];
        $candidates = [];

        foreach ($slots as $key => $slot) {
            if (
                isset($slot['status_lampu'], $slot['id_area']) &&
                $slot['status_lampu'] === 'hijau' &&
                (string)$slot['id_area'] === (string)$idArea
            ) {
                $candidates[] = array_merge($slot, ['key' => $key]);
            }
        }

        if (empty($candidates)) {
            return null;
        }

        usort($candidates, function ($a, $b) {
            return ($a['urutan_jarak'] ?? 999) <=> ($b['urutan_jarak'] ?? 999);
        });

        return $candidates[0];
    }

    /**
     * Cari apakah plat nomor memiliki reservasi/booking aktif yang belum masuk.
     */
    public function findActiveBookingByPlate(string $platNomor): ?array
    {
        $cleanPlate = strtoupper(str_replace(' ', '', $platNomor));
        $transactions = $this->firebase->get('tb_transaksi') ?? [];

        foreach ($transactions as $key => $trx) {
            if (
                ($trx['status'] ?? '') === 'booking' &&
                empty($trx['waktu_masuk'])
            ) {
                $trxPlate = strtoupper(str_replace(' ', '', $trx['plat_nomor'] ?? ''));
                if ($trxPlate === $cleanPlate) {
                    return array_merge($trx, ['key' => $key]);
                }
            }
        }

        return null;
    }

    /**
     * Proses Masuk Kendaraan (Gate In)
     * Otomatis membedakan apakah kendaraan memiliki reservasi (booking) atau reguler.
     */
    public function processGateIn(
        string $platNomor,
        string $jenisKendaraan = 'mobil',
        string $kategori = 'reguler',
        int|string $idArea = 1,
        ?string $idUser = null
    ): array {
        $now = Carbon::now();
        $waktuMasukStr = $now->toDateTimeString();

        // 1. Cek apakah ada booking aktif untuk plat nomor ini
        $activeBooking = $this->findActiveBookingByPlate($platNomor);

        if ($activeBooking) {
            $kodeSlot = $activeBooking['id_slot'];
            $kodeTiket = $activeBooking['id_parkir'];
            $idArea = $activeBooking['id_area'] ?? $idArea;

            // Ubah lampu dari ORANYE menjadi MERAH
            $this->firebase->update("tb_slot_parkir/{$kodeSlot}", [
                'status_lampu' => 'merah',
                'plat_nomor' => strtoupper($platNomor),
            ]);

            // Tambah kuota terisi pada area
            $area = $this->firebase->get("tb_area_parkir/area_{$idArea}");
            $terisiSaatIni = $area['terisi'] ?? 0;
            $this->firebase->update("tb_area_parkir/area_{$idArea}", [
                'terisi' => $terisiSaatIni + 1,
            ]);

            // Update transaksi booking menjadi aktif
            $updateData = [
                'waktu_masuk' => $waktuMasukStr,
                'status' => 'aktif',
            ];
            $this->firebase->update("tb_transaksi/{$kodeTiket}", $updateData);

            $this->logActivity(
                $idUser ?? 'petugas',
                "Kendaraan reservasi {$platNomor} tiba dan masuk ke slot {$kodeSlot}. Tiket: {$kodeTiket}"
            );

            return [
                'success' => true,
                'is_booking' => true,
                'message' => "Kendaraan reservasi terverifikasi. Masuk ke slot {$kodeSlot}.",
                'data' => array_merge($activeBooking, $updateData, [
                    'nama_area' => $area['nama_area'] ?? "Lantai {$idArea}",
                ]),
            ];
        }

        // 2. Jika bukan booking, cari slot kosong (hijau) terdekat
        $slot = $this->findNearestAvailableSlot($idArea);

        if (!$slot) {
            return [
                'success' => false,
                'message' => 'Semua slot parkir di area ini sedang penuh!',
            ];
        }

        $kodeSlot = $slot['kode_slot'] ?? $slot['key'];

        // Ambil tarif
        $tarifKey = 'tarif_' . strtolower($jenisKendaraan) . '_' . strtolower($kategori);
        $tarifData = $this->firebase->get("tb_tarif/{$tarifKey}");
        $tarifPerJam = $tarifData['tarif_per_jam'] ?? ($jenisKendaraan === 'motor' ? 2000 : 5000);

        // Generate kode tiket unik
        $kodeTiket = 'TRX-' . $now->format('Ymd') . '-' . strtoupper(Str::random(5));

        // Ubah lampu menjadi MERAH
        $this->firebase->update("tb_slot_parkir/{$kodeSlot}", [
            'status_lampu' => 'merah',
            'plat_nomor' => strtoupper($platNomor),
        ]);

        // Tambah kuota terisi
        $area = $this->firebase->get("tb_area_parkir/area_{$idArea}");
        $terisiSaatIni = $area['terisi'] ?? 0;
        $this->firebase->update("tb_area_parkir/area_{$idArea}", [
            'terisi' => $terisiSaatIni + 1,
        ]);

        // Simpan data transaksi
        $transaksiData = [
            'id_parkir' => $kodeTiket,
            'kode_qr' => $kodeTiket,
            'plat_nomor' => strtoupper($platNomor),
            'jenis_kendaraan' => strtolower($jenisKendaraan),
            'id_slot' => $kodeSlot,
            'id_area' => $idArea,
            'waktu_masuk' => $waktuMasukStr,
            'waktu_keluar' => null,
            'id_tarif' => $tarifKey,
            'tarif_per_jam' => $tarifPerJam,
            'durasi' => 0,
            'biaya' => $tarifPerJam,
            'status' => 'aktif',
            'status_bayar' => 'belum',
            'id_user' => $idUser ?? 'guest',
        ];

        $this->firebase->set("tb_transaksi/{$kodeTiket}", $transaksiData);

        $this->logActivity(
            $idUser ?? 'petugas',
            "Kendaraan reguler {$platNomor} ({$jenisKendaraan}) masuk ke slot {$kodeSlot}. Tiket: {$kodeTiket}"
        );

        return [
            'success' => true,
            'is_booking' => false,
            'data' => array_merge($transaksiData, [
                'nama_area' => $area['nama_area'] ?? "Lantai {$idArea}",
            ]),
        ];
    }

    /**
     * Hitung billing / tagihan berjalan berdasarkan waktu masuk dan sekarang.
     */
    public function calculateCurrentBill(array $transaksi): array
    {
        $waktuMasuk = Carbon::parse($transaksi['waktu_masuk'] ?? now());
        $waktuSekarang = Carbon::now();

        $totalMenit = max(1, $waktuMasuk->diffInMinutes($waktuSekarang));
        $durasiJam = (int)ceil($totalMenit / 60);

        $tarifPerJam = $transaksi['tarif_per_jam'] ?? 5000;
        $totalBiaya = $durasiJam * $tarifPerJam;

        return [
            'durasi_menit' => $totalMenit,
            'durasi_jam' => $durasiJam,
            'tarif_per_jam' => $tarifPerJam,
            'total_biaya' => $totalBiaya,
            'waktu_masuk' => $waktuMasuk->toDateTimeString(),
            'waktu_sekarang' => $waktuSekarang->toDateTimeString(),
        ];
    }

    /**
     * Proses Keluar Kendaraan (Gate Out)
     */
    public function processGateOut(string $kodeTiket, string $metodeBayar = 'tunai', ?string $idPetugas = null): array
    {
        $transaksi = $this->firebase->get("tb_transaksi/{$kodeTiket}");

        if (!$transaksi) {
            return [
                'success' => false,
                'message' => "Tiket {$kodeTiket} tidak ditemukan!",
            ];
        }

        if (($transaksi['status'] ?? '') === 'selesai') {
            return [
                'success' => false,
                'message' => "Tiket {$kodeTiket} sudah pernah diselesaikan sebelumnya!",
                'data' => $transaksi,
            ];
        }

        $waktuKeluar = Carbon::now();
        $bill = $this->calculateCurrentBill($transaksi);
        $kodeSlot = $transaksi['id_slot'];
        $idArea = $transaksi['id_area'] ?? 1;

        // 1. Bebaskan slot parkir menjadi HIJAU
        $this->firebase->update("tb_slot_parkir/{$kodeSlot}", [
            'status_lampu' => 'hijau',
            'plat_nomor' => '',
        ]);

        // 2. Kurangi kapasitas terisi pada area
        $area = $this->firebase->get("tb_area_parkir/area_{$idArea}");
        $terisiSaatIni = max(0, ($area['terisi'] ?? 1) - 1);
        $this->firebase->update("tb_area_parkir/area_{$idArea}", [
            'terisi' => $terisiSaatIni,
        ]);

        // 3. Update status transaksi
        $updateData = [
            'waktu_keluar' => $waktuKeluar->toDateTimeString(),
            'durasi' => $bill['durasi_jam'],
            'biaya' => $bill['total_biaya'],
            'metode_bayar' => $metodeBayar,
            'status' => 'selesai',
            'status_bayar' => 'lunas',
        ];

        $this->firebase->update("tb_transaksi/{$kodeTiket}", $updateData);

        $this->logActivity(
            $idPetugas ?? 'petugas',
            "Kendaraan {$transaksi['plat_nomor']} keluar dari slot {$kodeSlot}. Biaya: Rp" . number_format($bill['total_biaya'])
        );

        return [
            'success' => true,
            'message' => 'Transaksi keluar berhasil diproses.',
            'data' => array_merge($transaksi, $updateData),
        ];
    }

    /**
     * Proses Reservasi / Booking Slot (Member)
     */
    public function processBooking(
        string $kodeSlot,
        string $platNomor,
        string $jamBooking,
        string $idUser,
        string $jenisKendaraan = 'mobil'
    ): array {
        $slot = $this->firebase->get("tb_slot_parkir/{$kodeSlot}");

        if (!$slot) {
            return ['success' => false, 'message' => "Slot {$kodeSlot} tidak valid."];
        }

        if (($slot['status_lampu'] ?? '') !== 'hijau') {
            return ['success' => false, 'message' => "Slot {$kodeSlot} sedang tidak tersedia!"];
        }

        $now = Carbon::now();
        $kodeTiket = 'BKG-' . $now->format('Ymd') . '-' . strtoupper(Str::random(5));
        $tarifKey = 'tarif_' . strtolower($jenisKendaraan) . '_booking';
        $tarifData = $this->firebase->get("tb_tarif/{$tarifKey}");
        $tarifPerJam = $tarifData['tarif_per_jam'] ?? 8000;

        // Ubah lampu jadi ORANYE
        $this->firebase->update("tb_slot_parkir/{$kodeSlot}", [
            'status_lampu' => 'oranye',
            'plat_nomor' => strtoupper($platNomor),
        ]);

        $bookingData = [
            'id_parkir' => $kodeTiket,
            'kode_qr' => $kodeTiket,
            'plat_nomor' => strtoupper($platNomor),
            'jenis_kendaraan' => strtolower($jenisKendaraan),
            'id_slot' => $kodeSlot,
            'id_area' => $slot['id_area'] ?? 1,
            'jam_booking' => $jamBooking,
            'created_at' => $now->toDateTimeString(),
            'waktu_masuk' => null,
            'waktu_keluar' => null,
            'id_tarif' => $tarifKey,
            'tarif_per_jam' => $tarifPerJam,
            'durasi' => 0,
            'biaya' => $tarifPerJam,
            'status' => 'booking',
            'status_bayar' => 'belum',
            'id_user' => $idUser,
        ];

        $this->firebase->set("tb_transaksi/{$kodeTiket}", $bookingData);

        $this->logActivity(
            $idUser,
            "Member {$idUser} booking slot {$kodeSlot} untuk jam {$jamBooking}."
        );

        return [
            'success' => true,
            'message' => "Slot {$kodeSlot} berhasil di-booking!",
            'data' => $bookingData,
        ];
    }

    /**
     * LOGIKA PENGAWAS: Otomatis membatalkan reservasi (booking) yang melewati batas toleransi 15 menit.
     * Mengembalikan lampu dari ORANYE kembali ke HIJAU.
     */
    public function checkAndCancelExpiredBookings(int $toleranceMinutes = 15): array
    {
        $transactions = $this->firebase->get('tb_transaksi') ?? [];
        $cancelled = [];
        $now = Carbon::now();

        foreach ($transactions as $key => $trx) {
            if (
                ($trx['status'] ?? '') === 'booking' &&
                empty($trx['waktu_masuk'])
            ) {
                // Tentukan waktu target booking
                $targetTimeStr = $trx['jam_booking'] ?? $trx['created_at'] ?? null;
                if (!$targetTimeStr) continue;

                try {
                    if (str_contains($targetTimeStr, '-')) {
                        $targetTime = Carbon::parse($targetTimeStr);
                    } else {
                        $targetTime = Carbon::parse($now->toDateString() . ' ' . $targetTimeStr);
                    }
                } catch (\Throwable $e) {
                    $targetTime = Carbon::parse($trx['created_at'] ?? now());
                }

                // Cek apakah waktu saat ini melebihi target waktu + batas toleransi (15 menit)
                $deadline = $targetTime->copy()->addMinutes($toleranceMinutes);

                if ($now->greaterThan($deadline)) {
                    $kodeSlot = $trx['id_slot'] ?? '';
                    $idParkir = $trx['id_parkir'] ?? $key;
                    $platNomor = $trx['plat_nomor'] ?? '';

                    // 1. Kembalikan lampu slot dari ORANYE -> HIJAU
                    if (!empty($kodeSlot)) {
                        $this->firebase->update("tb_slot_parkir/{$kodeSlot}", [
                            'status_lampu' => 'hijau',
                            'plat_nomor' => '',
                        ]);
                    }

                    // 2. Update status transaksi menjadi dibatalkan_otomatis
                    $this->firebase->update("tb_transaksi/{$idParkir}", [
                        'status' => 'dibatalkan_otomatis',
                        'waktu_batal' => $now->toDateTimeString(),
                        'alasan_batal' => "Melewati batas waktu toleransi kedatangan {$toleranceMinutes} menit.",
                    ]);

                    // 3. Catat ke log aktivitas
                    $this->logActivity(
                        'system',
                        "Booking {$idParkir} (slot {$kodeSlot}, plat {$platNomor}) dibatalkan otomatis karena melewati toleransi {$toleranceMinutes} menit."
                    );

                    $cancelled[] = [
                        'id_parkir' => $idParkir,
                        'plat_nomor' => $platNomor,
                        'kode_slot' => $kodeSlot,
                        'jam_booking' => $targetTimeStr,
                        'deadline' => $deadline->toDateTimeString(),
                    ];
                }
            }
        }

        return [
            'success' => true,
            'total_cancelled' => count($cancelled),
            'cancelled_items' => $cancelled,
        ];
    }

    /**
     * Catat audit trail aktivitas ke node tb_log_aktivitas.
     */
    public function logActivity(string|int $idUser, string $aktivitas): void
    {
        $logKey = 'LOG-' . Carbon::now()->format('YmdHis') . '-' . Str::random(4);

        $this->firebase->set("tb_log_aktivitas/{$logKey}", [
            'id_log' => $logKey,
            'id_user' => (string)$idUser,
            'aktivitas' => $aktivitas,
            'waktu_aktivitas' => Carbon::now()->toDateTimeString(),
        ]);
    }
}
