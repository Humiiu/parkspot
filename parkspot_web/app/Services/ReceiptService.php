<?php

namespace App\Services;

use Carbon\Carbon;

class ReceiptService
{
    public const RECEIPT_WIDTH = 32; // Standar lebar karakter kertas kasir 58mm (Font A)

    /**
     * Format Karcis Masuk (Gate In Ticket) 58mm sesuai Tabel 7 Proposal UKK.
     */
    public function formatEntryTicket(array $transaksi, ?string $namaArea = null): array
    {
        $idParkir = $transaksi['id_parkir'] ?? 'TRX-UNKNOWN';
        $platNomor = strtoupper($transaksi['plat_nomor'] ?? '-');
        $jenis = strtoupper($transaksi['jenis_kendaraan'] ?? 'MOBIL');
        $slot = $transaksi['id_slot'] ?? '-';
        $area = $namaArea ?? ($transaksi['nama_area'] ?? 'LANTAI 1');
        
        // Singkat nama area jika panjang (misal: "Lantai 1 - Zona A" -> "LANTAI 1")
        $shortArea = strtoupper(explode(' - ', $area)[0]);

        $waktuMasuk = isset($transaksi['waktu_masuk'])
            ? Carbon::parse($transaksi['waktu_masuk'])
            : Carbon::now();

        $waktuStr = $waktuMasuk->format('d/m/Y | H:i') . ' WIB';

        // URL tujuan untuk Guest jika QR di-scan oleh kamera HP
        $qrData = $idParkir;
        $guestWebUrl = url("/guest/ticket/{$idParkir}");

        $lines = [];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->center('PARKSPOT - TIKET MASUK'), 'align' => 'center', 'bold' => true];
        $lines[] = ['text' => $this->center($waktuStr), 'align' => 'center'];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->fitWidth("PLAT : {$platNomor} ({$jenis})"), 'align' => 'left'];

        $slotLine = "SLOT : {$shortArea} -> [ {$slot} ]";
        if (strlen($slotLine) > self::RECEIPT_WIDTH) {
            $lines[] = ['text' => $this->fitWidth("AREA : {$shortArea}"), 'align' => 'left'];
            $lines[] = ['text' => $this->fitWidth("SLOT : [ {$slot} ]"), 'align' => 'left', 'bold' => true];
        } else {
            $lines[] = ['text' => $slotLine, 'align' => 'left', 'bold' => true];
        }

        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->center('[ KODE QR UNIK ]'), 'align' => 'center', 'is_qr' => true, 'qr_data' => $guestWebUrl];
        $lines[] = ['text' => $this->center('Scan QR untuk lihat denah,'), 'align' => 'center'];
        $lines[] = ['text' => $this->center('cek tagihan & bayar QRIS'), 'align' => 'center'];
        $lines[] = ['text' => $this->center("#{$idParkir}"), 'align' => 'center', 'bold' => true];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->center('Simpan Karcis Ini'), 'align' => 'center'];
        $lines[] = ['text' => $this->center('Jangan Ditinggal di Kendaraan'), 'align' => 'center'];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];

        $rawText = implode("\n", array_map(fn($l) => $l['text'], $lines));

        return [
            'type' => 'entry_ticket',
            'width' => self::RECEIPT_WIDTH,
            'id_parkir' => $idParkir,
            'plat_nomor' => $platNomor,
            'id_slot' => $slot,
            'qr_data' => $guestWebUrl,
            'qr_code_plain' => $qrData,
            'raw_text' => $rawText,
            'lines' => $lines,
        ];
    }

    /**
     * Format Struk Pembayaran Keluar (Gate Out Receipt) 58mm sesuai Tabel 7 Proposal UKK.
     */
    public function formatExitReceipt(array $transaksi, string $petugasName = 'Petugas 1'): array
    {
        $idParkir = $transaksi['id_parkir'] ?? 'TRX-UNKNOWN';
        $platNomor = strtoupper($transaksi['plat_nomor'] ?? '-');
        $slot = $transaksi['id_slot'] ?? '-';
        $durasi = $transaksi['durasi'] ?? 1;
        $biaya = $transaksi['biaya'] ?? 0;
        $metode = strtoupper($transaksi['metode_bayar'] ?? 'TUNAI');
        $statusBayar = strtoupper($transaksi['status_bayar'] ?? 'LUNAS');

        $waktuMasuk = isset($transaksi['waktu_masuk'])
            ? Carbon::parse($transaksi['waktu_masuk'])->format('d/m/y H:i')
            : '-';
        $waktuKeluar = isset($transaksi['waktu_keluar'])
            ? Carbon::parse($transaksi['waktu_keluar'])->format('d/m/y H:i')
            : Carbon::now()->format('d/m/y H:i');

        $lines = [];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->center('PARKSPOT - BUKTI BAYAR'), 'align' => 'center', 'bold' => true];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->twoColumns('No. Tiket', "#{$idParkir}"), 'align' => 'left'];
        $lines[] = ['text' => $this->twoColumns('Plat / Slot', "{$platNomor} / {$slot}"), 'align' => 'left'];
        $lines[] = ['text' => $this->twoColumns('Masuk', $waktuMasuk), 'align' => 'left'];
        $lines[] = ['text' => $this->twoColumns('Keluar', $waktuKeluar), 'align' => 'left'];
        $lines[] = ['text' => $this->twoColumns('Durasi', "{$durasi} Jam"), 'align' => 'left'];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->twoColumns('TOTAL BAYAR', 'Rp ' . number_format($biaya, 0, ',', '.')), 'align' => 'left', 'bold' => true];
        $lines[] = ['text' => $this->twoColumns('Status', "{$statusBayar} ({$metode})"), 'align' => 'left'];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];
        $lines[] = ['text' => $this->center($this->fitWidth("Petugas: {$petugasName} | Terima Kasih")), 'align' => 'center'];
        $lines[] = ['text' => $this->center('Semoga Perjalanan Anda Aman'), 'align' => 'center'];
        $lines[] = ['text' => $this->divider(), 'align' => 'center'];

        $rawText = implode("\n", array_map(fn($l) => $l['text'], $lines));

        return [
            'type' => 'exit_receipt',
            'width' => self::RECEIPT_WIDTH,
            'id_parkir' => $idParkir,
            'plat_nomor' => $platNomor,
            'id_slot' => $slot,
            'total_biaya' => $biaya,
            'metode_bayar' => $metode,
            'status_bayar' => $statusBayar,
            'raw_text' => $rawText,
            'lines' => $lines,
        ];
    }

    /**
     * Helper: teks rata tengah pada lebar 32 karakter.
     */
    public function center(string $text, int $width = self::RECEIPT_WIDTH): string
    {
        $len = strlen($text);
        if ($len >= $width) {
            return substr($text, 0, $width);
        }

        $leftPad = (int)floor(($width - $len) / 2);
        return str_repeat(' ', $leftPad) . $text;
    }

    /**
     * Helper: teks dua kolom (kiri & kanan) pas 32 karakter.
     */
    public function twoColumns(string $left, string $right, int $width = self::RECEIPT_WIDTH): string
    {
        $totalLen = strlen($left) + strlen($right);
        if ($totalLen >= $width) {
            // Potong sisi kiri jika terlalu panjang
            $maxLeft = $width - strlen($right) - 1;
            $left = substr($left, 0, max(1, $maxLeft));
            $space = 1;
        } else {
            $space = $width - (strlen($left) + strlen($right));
        }

        return $left . str_repeat(' ', $space) . $right;
    }

    /**
     * Helper: batasi panjang maksimal 32 karakter.
     */
    public function fitWidth(string $text, int $width = self::RECEIPT_WIDTH): string
    {
        return strlen($text) > $width ? substr($text, 0, $width) : $text;
    }

    /**
     * Helper: garis pembatas putus-putus 32 karakter.
     */
    public function divider(int $width = self::RECEIPT_WIDTH): string
    {
        return str_repeat('-', $width);
    }
}
