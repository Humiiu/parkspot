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

        // Urutkan berdasarkan urutan_jarak terendah (paling dekat)
        usort($candidates, function ($a, $b) {
            return ($a['urutan_jarak'] ?? 999) <=> ($b['urutan_jarak'] ?? 999);
        });

        return $candidates[0];
    }

    /**
     * Proses Masuk Kendaraan (Gate In)
     */
    public function processGateIn(
        string $platNomor,
        string $jenisKendaraan = 'mobil',
        string $kategori = 'reguler',
        int|string $idArea = 1,
        ?string $idUser = null
    ): array {
        $slot = $this->findNearestAvailableSlot($idArea);

        if (!$slot) {
            return [
                'success' => false,
                'message' => 'Semua slot parkir di area ini sedang penuh!',
            ];
        }

        $kodeSlot = $slot['kode_slot'] ?? $slot['key'];
        $now = Carbon::now();
        $waktuMasukStr = $now->toDateTimeString();

        // 1. Ambil tarif yang sesuai
        $tarifKey = 'tarif_' . strtolower($jenisKendaraan) . '_' . strtolower($kategori);
        $tarifData = $this->firebase->get("tb_tarif/{$tarifKey}");
        $tarifPerJam = $tarifData['tarif_per_jam'] ?? ($jenisKendaraan === 'motor' ? 2000 : 5000);

        // 2. Generate kode tiket unik
        $kodeTiket = 'TRX-' . $now->format('Ymd') . '-' . strtoupper(Str::random(5));

        // 3. Update status slot menjadi MERAH
        $this->firebase->update("tb_slot_parkir/{$kodeSlot}", [
            'status_lampu' => 'merah',
            'plat_nomor' => strtoupper($platNomor),
        ]);

        // 4. Update kapasitas terisi pada area
        $area = $this->firebase->get("tb_area_parkir/area_{$idArea}");
        $terisiSaatIni = $area['terisi'] ?? 0;
        $this->firebase->update("tb_area_parkir/area_{$idArea}", [
            'terisi' => $terisiSaatIni + 1,
        ]);

        // 5. Simpan data transaksi
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
            'biaya' => $tarifPerJam, // Tarif jam pertama
            'status' => 'aktif',
            'status_bayar' => 'belum',
            'id_user' => $idUser ?? 'guest',
        ];

        $this->firebase->set("tb_transaksi/{$kodeTiket}", $transaksiData);

        // 6. Catat log aktivitas
        $this->logActivity(
            $idUser ?? 'petugas',
            "Kendaraan {$platNomor} ({$jenisKendaraan}) masuk ke slot {$kodeSlot}. Tiket: {$kodeTiket}"
        );

        return [
            'success' => true,
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
        $waktuMasuk = Carbon::parse($transaksi['waktu_masuk']);
        $waktuSekarang = Carbon::now();

        // Selisih dalam menit
        $totalMenit = max(1, $waktuMasuk->diffInMinutes($waktuSekarang));
        // Hitung jam dengan pembulatan ke atas (min 1 jam)
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

        // 4. Catat log aktivitas
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

