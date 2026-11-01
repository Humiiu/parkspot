<?php

namespace Tests\Feature;

use App\Services\FirebaseService;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    public function test_booking_lifecycle_arrival_and_auto_cancel(): void
    {
        $firebase = app(FirebaseService::class);

        // -------------------------------------------------------------
        // SKENARIO 1: Booking Berhasil & Tiba Tepat Waktu (Oranye -> Merah)
        // -------------------------------------------------------------
        // Pastikan slot A-03 hijau terlebih dahulu
        $firebase->update('tb_slot_parkir/A-03', ['status_lampu' => 'hijau', 'plat_nomor' => '']);

        // 1. Member Booking Slot A-03
        $bookResponse = $this->postJson('/api/parking/booking', [
            'kode_slot' => 'A-03',
            'plat_nomor' => 'B 1111 ARR',
            'jam_booking' => now()->format('H:i'),
            'id_user' => 'user_member1',
            'jenis_kendaraan' => 'mobil',
        ]);
        $bookResponse->assertStatus(201);

        // Cek lampu slot berubah jadi ORANYE
        $slotA03 = $firebase->get('tb_slot_parkir/A-03');
        $this->assertEquals('oranye', $slotA03['status_lampu']);

        // 2. Kendaraan tiba di Gate In
        $inResponse = $this->postJson('/api/parking/gate-in', [
            'plat_nomor' => 'B 1111 ARR',
            'jenis_kendaraan' => 'mobil',
            'id_area' => 1,
        ]);
        $inResponse->assertStatus(201);
        $this->assertTrue($inResponse->json('is_booking'));

        // Cek lampu slot berubah jadi MERAH
        $slotA03AfterIn = $firebase->get('tb_slot_parkir/A-03');
        $this->assertEquals('merah', $slotA03AfterIn['status_lampu']);

        // Keluar agar bersih
        $ticketId = $inResponse->json('data.id_parkir');
        $this->postJson('/api/parking/gate-out', ['kode_tiket' => $ticketId]);

        // -------------------------------------------------------------
        // SKENARIO 2: Booking Lewat Batas Toleransi 15 Menit (Oranye -> Hijau)
        // -------------------------------------------------------------
        // Pastikan slot A-04 hijau
        $firebase->update('tb_slot_parkir/A-04', ['status_lampu' => 'hijau', 'plat_nomor' => '']);

        // 1. Member Booking Slot A-04 dengan jam booking 30 menit yang lalu (sudah lewat toleransi)
        $pastTime = now()->subMinutes(30)->toDateTimeString();
        $bookPastResponse = $this->postJson('/api/parking/booking', [
            'kode_slot' => 'A-04',
            'plat_nomor' => 'B 2222 EXP',
            'jam_booking' => $pastTime,
            'id_user' => 'user_member1',
            'jenis_kendaraan' => 'mobil',
        ]);
        $bookPastResponse->assertStatus(201);
        $pastTicketId = $bookPastResponse->json('data.id_parkir');

        // Pastikan lampu slot A-04 sempat menjadi ORANYE
        $slotA04 = $firebase->get('tb_slot_parkir/A-04');
        $this->assertEquals('oranye', $slotA04['status_lampu']);

        // 2. Jalankan Logika Pengawas Auto-Cancel (Toleransi 15 Menit)
        $checkResponse = $this->postJson('/api/parking/check-bookings?tolerance=15');
        $checkResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $checkResponse->json('total_cancelled'));

        // 3. Verifikasi lampu slot A-04 KEMBALI MENJADI HIJAU!
        $slotA04AfterCheck = $firebase->get('tb_slot_parkir/A-04');
        $this->assertEquals('hijau', $slotA04AfterCheck['status_lampu']);
        $this->assertEmpty($slotA04AfterCheck['plat_nomor']);

        // 4. Verifikasi status transaksi berubah jadi dibatalkan_otomatis
        $cancelledTrx = $firebase->get("tb_transaksi/{$pastTicketId}");
        $this->assertEquals('dibatalkan_otomatis', $cancelledTrx['status']);
    }
}
