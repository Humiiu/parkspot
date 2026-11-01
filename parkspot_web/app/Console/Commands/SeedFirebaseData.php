<?php

namespace App\Console\Commands;

use App\Services\FirebaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SeedFirebaseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parkspot:seed-firebase';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed initial UKK ParkSpot data directly into Firebase Realtime Database';

    /**
     * Execute the console command.
     */
    public function handle(FirebaseService $firebase): int
    {
        $this->info('Memulai seeding data awal ParkSpot ke Firebase Realtime Database...');

        // 1. Data tb_user
        $this->comment('1. Seeding tb_user...');
        $users = [
            'user_admin' => [
                'id_user' => 'user_admin',
                'nama_lengkap' => 'Administrator ParkSpot',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status_aktif' => 1,
            ],
            'user_petugas' => [
                'id_user' => 'user_petugas',
                'nama_lengkap' => 'Petugas Gerbang 1',
                'username' => 'petugas',
                'password' => Hash::make('petugas123'),
                'role' => 'petugas',
                'status_aktif' => 1,
            ],
            'user_owner' => [
                'id_user' => 'user_owner',
                'nama_lengkap' => 'Owner ParkSpot',
                'username' => 'owner',
                'password' => Hash::make('owner123'),
                'role' => 'owner',
                'status_aktif' => 1,
            ],
            'user_member1' => [
                'id_user' => 'user_member1',
                'nama_lengkap' => 'Jundi Faiq Mahbubillah',
                'username' => 'jundi',
                'password' => Hash::make('jundi123'),
                'role' => 'member',
                'status_aktif' => 1,
            ],
        ];
        $firebase->set('tb_user', $users);

        // 2. Data tb_area_parkir
        $this->comment('2. Seeding tb_area_parkir...');
        $areas = [
            'area_1' => [
                'id_area' => 1,
                'nama_area' => 'Lantai 1 - Zona A',
                'kapasitas' => 10,
                'terisi' => 0,
            ],
            'area_2' => [
                'id_area' => 2,
                'nama_area' => 'Lantai 2 - Zona B',
                'kapasitas' => 10,
                'terisi' => 0,
            ],
        ];
        $firebase->set('tb_area_parkir', $areas);

        // 3. Data tb_slot_parkir (Indikator 3 warna: hijau, oranye, merah)
        $this->comment('3. Seeding tb_slot_parkir (20 slot dengan urutan jarak)...');
        $slots = [];

        // Lantai 1: A-01 s/d A-10
        for ($i = 1; $i <= 10; $i++) {
            $kode = sprintf('A-%02d', $i);
            $slots[$kode] = [
                'id_slot' => $kode,
                'id_area' => 1,
                'kode_slot' => $kode,
                'urutan_jarak' => $i,
                'status_lampu' => 'hijau',
                'plat_nomor' => '',
            ];
        }

        // Lantai 2: B-01 s/d B-10
        for ($i = 1; $i <= 10; $i++) {
            $kode = sprintf('B-%02d', $i);
            $slots[$kode] = [
                'id_slot' => $kode,
                'id_area' => 2,
                'kode_slot' => $kode,
                'urutan_jarak' => $i,
                'status_lampu' => 'hijau',
                'plat_nomor' => '',
            ];
        }
        $firebase->set('tb_slot_parkir', $slots);

        // 4. Data tb_tarif
        $this->comment('4. Seeding tb_tarif...');
        $tariffs = [
            'tarif_mobil_reguler' => [
                'id_tarif' => 1,
                'jenis_kendaraan' => 'mobil',
                'kategori' => 'reguler',
                'tarif_per_jam' => 5000,
            ],
            'tarif_mobil_booking' => [
                'id_tarif' => 2,
                'jenis_kendaraan' => 'mobil',
                'kategori' => 'booking',
                'tarif_per_jam' => 8000,
            ],
            'tarif_motor_reguler' => [
                'id_tarif' => 3,
                'jenis_kendaraan' => 'motor',
                'kategori' => 'reguler',
                'tarif_per_jam' => 2000,
            ],
            'tarif_motor_booking' => [
                'id_tarif' => 4,
                'jenis_kendaraan' => 'motor',
                'kategori' => 'booking',
                'tarif_per_jam' => 4000,
            ],
        ];
        $firebase->set('tb_tarif', $tariffs);

        // 5. Data tb_kendaraan
        $this->comment('5. Seeding tb_kendaraan...');
        $vehicles = [
            'kdr_1' => [
                'id_kendaraan' => 'kdr_1',
                'plat_nomor' => 'B 1234 JFM',
                'jenis_kendaraan' => 'mobil',
                'warna' => 'Hitam',
                'pemilik' => 'Jundi Faiq M',
                'id_user' => 'user_member1',
            ],
        ];
        $firebase->set('tb_kendaraan', $vehicles);

        // 6. Inisialisasi node kosong tb_transaksi & tb_log_aktivitas
        $this->comment('6. Memastikan node tb_transaksi & tb_log_aktivitas siap...');
        // Catat log inisialisasi awal
        $firebase->set('tb_log_aktivitas/LOG-INIT', [
            'id_log' => 'LOG-INIT',
            'id_user' => 'system',
            'aktivitas' => 'Inisialisasi sistem basis data ParkSpot UKK Paket 2',
            'waktu_aktivitas' => now()->toDateTimeString(),
        ]);

        $this->info('✓ Seeding basis data Firebase selesai dengan sukses!');
        return Command::SUCCESS;
    }
}

