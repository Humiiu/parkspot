<?php

namespace App\Console\Commands;

use App\Services\ParkingService;
use Illuminate\Console\Command;

class CheckBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parkspot:check-bookings {--tolerance=15 : Batas toleransi kedatangan dalam menit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa dan batalkan otomatis booking yang melewati batas toleransi waktu (kembalikan lampu jadi hijau)';

    /**
     * Execute the console command.
     */
    public function handle(ParkingService $parking): int
    {
        $tolerance = (int)$this->option('tolerance');
        $this->info("Memeriksa status reservasi booking parkir (toleransi: {$tolerance} menit)...");

        $result = $parking->checkAndCancelExpiredBookings($tolerance);

        $total = $result['total_cancelled'] ?? 0;

        if ($total > 0) {
            $this->warn("Ditemukan {$total} booking yang melewati batas toleransi:");

            $rows = [];
            foreach ($result['cancelled_items'] as $item) {
                $rows[] = [
                    $item['id_parkir'],
                    $item['plat_nomor'],
                    $item['kode_slot'],
                    $item['jam_booking'],
                    $item['deadline'],
                    'Hijau (Batal Otomatis)',
                ];
            }

            $this->table(
                ['ID Tiket', 'Plat Nomor', 'Slot', 'Jam Booking', 'Batas Toleransi', 'Status Lampu'],
                $rows
            );
        } else {
            $this->info("✓ Tidak ada booking yang melewati batas waktu. Semua slot booking masih dalam batas toleransi.");
        }

        return Command::SUCCESS;
    }
}
