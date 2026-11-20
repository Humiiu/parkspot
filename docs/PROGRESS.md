# Tracker Progres Revisi UKK ParkSpot

Dokumen ini memantau status pengerjaan setiap butir revisi hasil sidang Quality Plan UKK (14 November 2026).

## Status Legend
- 🔴 **Belum Dimulai (Pending)**
- 🟡 **Sedang Dikerjakan (In Progress)**
- 🟢 **Selesai & Terverifikasi (Done)**

---

## Status Hasil Pembahasan & Keputusan Arsitektur

| No | Poin Revisi Penguji | Status Diskusi / Konsep | Status Proposal | Status Kode Web (Laravel) | Status Kode Mobile (Flutter) |
| :---: | :--- | :---: | :---: | :---: | :---: |
| **1** | Format Cetak Struk PDF 58mm & Direct Print | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **2** | Pematangan Fitur Cetak Struk (Backend & Frontend) | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **3** | Antisipasi Bentrok Booking Slot (Buffer Time 30 Menit) | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **4** | Layout Parkir Statis Konsisten (Maksimal 12 Slot / Zona) | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **5** | Registrasi Member, Composite Key & Anti-Troll | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **6** | Rekap Shift Petugas, Master Jadwal, Absensi Kartu & Audit Kas | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **7** | Final Logical ERD (12 Entitas) & Sinkronisasi Diagram | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |
| **8** | Arsitektur Multi-Lokasi / Multi-Mall | 🟢 Disepakati (Konsensus Final) | 🟢 Selesai 100% (Diagram & Halaman Sinkron) | 🔴 Pending | 🔴 Pending |

---

## Status Pelacakan 17 Diagram UKK di `diagram/revisi/`
*(Aturan: Eksekusi wajib 1 per 1 diagram dan WAJIB menunggu ACC pengguna sebelum lanjut ke diagram berikutnya)*

| No | Nama Diagram | Status PNG | Status .drawio | Status ACC Pengguna | Keterangan |
| :---: | :--- | :---: | :---: | :---: | :--- |
| **1** | `Diagram ParkSpot UKK-DFD LEVEL 0 (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 5 entitas persis asli, Opsi B NFC/Password Darurat, ekspor PDF/Excel, Auditor v2 PASS |
| **2** | `Diagram ParkSpot UKK-Activity Buka & Tutup Shift Kasir` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 2 cabang Opsi B (NFC HP & Password Darurat), hapus Barcode & PIN, Auditor v2 PASS |
| **3** | `Diagram ParkSpot UKK-Sequence Buka & Tutup Shift Kasir` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | Opsi B NFC/Password Darurat, 0 PIN/Barcode, Dark Mode friendly, Auditor v2 PASS |
| **4** | `Diagram ParkSpot UKK-Activity CRUD Area & Slot (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | Struktur 3 cabang `Pilih Aksi?` persis milik pengguna, XML bersih & sinkron |
| **5** | `Diagram ParkSpot UKK-Sequence CRUD Area & Slot (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 4 lifeline, validasi maks 12 slot/zona (alt), XML bersih & sinkron |
| **6** | `Diagram ParkSpot UKK-Activity CRUD Data Kendaraan (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 3 cabang (Tambah kode keluarga, edit, reset plat), Dark Mode friendly, Auditor v2 PASS |
| **7** | `Diagram ParkSpot UKK-Sequence CRUD Kendaraan (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 4 lifeline, 1..13 urut, soft delete & idx_active_plate, Dark Mode, Auditor v2 PASS |
| **8** | `Diagram ParkSpot UKK-Activity CRUD Master Lokasi & Jadwal Shift` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | Admin/Owner kelola lokasi, jadwal shift & Tutup Paksa Shift, Dark Mode, Auditor v2 PASS |
| **9** | `Diagram ParkSpot UKK-Sequence CRUD Master Lokasi & Jadwal Shift` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 4 lifeline, 1..17 urut, 3 cabang alt (Lokasi/Jadwal/Tutup Paksa), Dark Mode, Auditor v2 PASS |
| **10** | `Diagram ParkSpot UKK-Activity Input Pelat & Pencarian Slot Terdekat (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 3 swimlane, cabang booking + relokasi overstay + idx_active_plate, Dark Mode, Auditor v2 PASS |
| **11** | `Diagram ParkSpot UKK-Sequence Input Pelat & Pencarian Slot (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 5 lifeline, 1..14 urut, booking+relokasi overstay+idx_active_plate, Dark Mode, Auditor v2 PASS |
| **12** | `Diagram ParkSpot UKK-Activity Pengecekan & Booking (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | Time-first, On-Demand Sync, buffer 30m, relokasi overstay + Info Slot Baru, Dark Mode, Auditor v2 PASS |
| **13** | `Diagram ParkSpot UKK-Sequence Pengecekan & Booking (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 5 lifeline, 1..17 urut, Webhook/Cek Status, Info Slot Pengganti, Dark Mode, Auditor v2 PASS |
| **14** | `Diagram ParkSpot UKK-Activity Keluar Gerbang & Print Struk (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | Scan QR / Input Pelat Hilang, Dual-Action 58mm, snapshot lock, lepas idx_active_plate, Auditor v2 PASS |
| **15** | `Diagram ParkSpot UKK-Sequence Keluar Gerbang & Cetak Struk (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 5 lifeline, 1..17 urut, 2 alt (Bayar + Dual-Action 58mm), Dark Mode, Auditor v2 PASS |
| **16** | `Diagram ParkSpot UKK-Activity Rekap (OWNER) (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | Multi-mal, pisah Tunai/QRIS, audit selisih & absen darurat, ekspor PDF/Excel, Auditor v2 PASS |
| **17** | `Diagram ParkSpot UKK-Sequence Rekap (OWNER) (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Auto-Lanjut) | 4 lifeline, 1..13 urut, ekspor PDF/Excel, audit selisih & absen darurat, Auditor v2 PASS |

---

## Status Pelacakan 13 Diagram Revisi Lanjutan + Revisi DFD (19 November 2026)
*(Aturan: 1 diagram per giliran, WAJIB menunggu ACC pengguna, lalu update PROGRESS.md + commit & push)*

| No | Nama Diagram (`diagram/revisi/`) | Status PNG | Status .drawio | Status ACC Pengguna | Keterangan |
| :---: | :--- | :---: | :---: | :---: | :--- |
| **1/13** | `Diagram ParkSpot UKK-Activity CRUD Data User (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | `master_user`, `master_lokasi`, `idx_email`, validasi duplikasi & shift aktif, tanpa PIN/barcode |
| **2/13** | `Diagram ParkSpot UKK-Sequence CRUD Data User (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 4 lifeline, 1..26 urut, nested alt validasi duplikasi & shift aktif, tanpa PIN/barcode |
| **3/13** | `Diagram ParkSpot UKK-Activity Login-Logout (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | `master_user` & `idx_email`, cek `status_aktif == true`, `assigned_id_lokasi`, catat login/logout ke `log_aktivitas` |
| **4/13** | `Diagram ParkSpot UKK-Sequence Login & Logout (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 4 lifeline, 1..17 urut, 3 cabang alt login, logout hanya tutup sesi |
| **5/13** | `Diagram ParkSpot UKK-Activity CRUD Tarif Parkir (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | Per lokasi mal, 4 field tarif, cegah tarif ganda & nonaktifkan saat ada parkir/booking aktif, log_aktivitas |
| **6/13** | `Diagram ParkSpot UKK-Sequence CRUD Tarif Parkir (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | 4 lifeline, 1..28 urut, nested alt duplikasi & parkir/booking aktif, kembali ke Admin |
| **7/13** | `Diagram ParkSpot UKK-Activity Log Aktivitas (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 Di-ACC | Filter mal/tanggal/jenis, baca `log_aktivitas/{id_lokasi}` + `master_user`, read-only, sorot merah |
| **8/13** | `Diagram ParkSpot UKK-Sequence Log Aktivitas (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | 4 lifeline, 1..9 urut, rasio H/W 0.58, read-only & sorot merah, kembali ke Admin |
| **9/13** | `Diagram ParkSpot UKK-Activity Lihat Slot & Tagihan (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | 2 jalur Member/Guest, guest_token, durasi bulat ke atas + overstay, grid 2x6, status lunas |
| **10/13** | `Diagram ParkSpot UKK-Sequence Lihat Slot & Tagihan (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | 4 lifeline, 1..16 urut, rasio H/W 0.84, 2 jalur Member/Guest, kembali ke aktor |
| **11/13** | `Diagram ParkSpot UKK-Activity Pembayaran (QRIS) (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | 3 swimlane (Member/Guest, Sistem, Midtrans Sandbox), webhook/cek status, UJI-09 retry |
| **12/13** | `Diagram ParkSpot UKK-Sequence Pembayaran (QRIS) (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | 5 lifeline (termasuk Midtrans Sandbox), 1..16 urut, rasio H/W 0.70, kembali ke aktor |
| **13/13** | `Diagram ParkSpot UKK-Use Case (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Re-layout 0 tabrakan) | Tata letak 3 kolom (Admin/Owner kiri, Petugas/Member kanan, Guest/Midtrans bawah, shared di tengah), 0 garis menembus elips |
| **Bonus 1** | `Diagram ParkSpot UKK-DFD LEVEL 0 (Revisi)` *(Update teks Petugas)* | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | Ganti "Pembaruan Status Slot Overstay" $\rightarrow$ "Pindai QR Booking / Input Pelat Masuk" |
| **Bonus 2** | `Diagram ParkSpot UKK-Activity Keluar Gerbang & Print Struk (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | Tambah keputusan "Waktu keluar melewati jam yang sudah dibayar?" (tagih selisih tunai/QRIS) |
| **Bonus 3** | `Diagram ParkSpot UKK-Sequence Keluar Gerbang & Cetak Struk (Revisi)` | 🟢 Selesai | 🟢 Selesai | 🟢 PASS (Batch ACC) | 5 lifeline, 1..21 urut, rasio H/W 0.92, alt selisih jam QRIS, sinkron Activity |

---

## Review & Perbaikan Format Proposal (19 November 2026)
- [x] Review menyeluruh `PROPOSAL PARKSPOT UKK.docx` (margin, spasi, font, gambar, gaya bahasa).
- [x] Cover disamakan ke ukuran A4 (sebelumnya Letter).
- [x] Section break sebelum BAB I: halaman awal pakai nomor romawi (i, ii, ...), BAB I mulai dari halaman 1.
- [x] Heading Activity/Sequence 13 & 14 dan Pseudocode 3 & 4 diganti dari Calibri ke Times New Roman bold; caption Gambar 3.14/3.15/3.28/3.29 ditebalkan.
- [x] Pseudocode 1-4 diseragamkan ke Consolas 9pt spasi 1,15.
- [x] Paragraf page-break diganti "page break before" (tidak ada halaman kosong), caption tidak lagi terpisah dari gambar (keep with next).
- [x] Header tabel 1.1 & 3.1-3.6 diulang otomatis di halaman lanjutan, baris tabel tidak terpotong.
- [x] Spasi Daftar Isi diseragamkan; Daftar Isi/Tabel/Gambar/Lampiran di-update.
- [x] Daftar terima kasih Kata Pengantar rata kiri; Simpulan poin 4 disamakan formatnya.
- [x] Tambah paragraf penjelas untuk 14 Diagram Sequence.
- [x] Nama pembimbing ditulis lengkap: Santosa Wijayanto, S.T., M.Kom. (Kata Pengantar & Lembar Persetujuan).
- [x] Deskripsi wireframe 3.39 & 3.40 disinkronkan dengan diagram (opsi A, lihat REVISI.md).
- [ ] Saran (BAB IV B) masih dikosongkan sementara (keputusan pengguna).
- [x] Absensi disamakan ke Opsi B (NFC + password darurat) di 10 tempat + Tabel 3.3 & Lampiran JSON (hapus pin_kasir, tambah metode_absen_tutup & alasan_absen_darurat sesuai erd_parkspot.dbml).

### Tahap 2 Perbaikan Proposal (19 November 2026)
- [x] Setiap subbab (Heading 2) mulai di halaman baru; setiap Diagram Activity mulai di halaman baru.
- [x] Penjelasan diagram + gambar selalu satu halaman (Activity 8 = 80%, Activity 13 = 84%, Sequence 13 = 88%).
- [x] Daftar Isi/Tabel/Gambar: baris kedua judul menjorok, nomor halaman tetap di kanan (diatur lewat gaya TOC 1-3).
- [x] Isi dari diagram revisi ditambahkan: tutup paksa shift (Admin & Owner), jam shift, pengaman password darurat, karcis hilang (ketik pelat), 1 booking aktif/akun, lampu oranye H-30 menit, booking batal >15 menit, rumus slot terdekat, denah 2x6, tanda merah absen darurat.
- [x] Tabel 3.2: Owner ikut kelola jadwal & tutup paksa shift. Tabel 3.5: tambah UJI-17 s.d. UJI-20.
- [x] Istilah berat diganti (utility-first, payment gateway, Pre-Exit, Audit Trail, Billing, DP, omzet, istilah Tabel Jadwal); "A. Simpulan" & "B. Saran".
- [ ] Menunggu Antigravity: revisi 12 diagram lama (nama tabel tb_* -> 12 entitas standar) + Use Case (fitur shift). Setelah selesai: tempel gambar baru & samakan teks penjelasan.
- [ ] Belum diputuskan: use case Petugas "Jaga Slot Booking" (usulan: "Pindai QR Booking Member di Gerbang Masuk").

---

## Catatan Subagent & Safety State
- Seluruh **17/17 diagram revisi** (`.drawio` + `.png`) di folder `diagram/revisi/` telah **SELESAI 100%**, lulus audit independen **`diagram-auditor-v2`**, serta telah di-commit & push ke GitHub.
- File asli milik pengguna di folder `diagram/` tetap utuh 100% dan terisolasi dari folder `diagram/revisi/`.
- **Dokumen Proposal (`PROPOSAL PARKSPOT UKK.docx`)**: Seluruh 18 diagram (17 revisi + 1 Use Case) telah **DITEMPEL SEMPURNA** ke naskah Word. Seluruh penanda placeholder kuning telah dihapus bersih.
- **Sinkronisasi Daftar & Nomor Halaman**: `DAFTAR TABEL` (7 tabel), `DAFTAR GAMBAR` (43 gambar), `DAFTAR LAMPIRAN`, dan `DAFTAR ISI` telah **diperbarui dan diverifikasi menggunakan Word COM** dengan **0 error bookmark (`Error! Bookmark not defined.`)**.
- **Skema DBML**: Field `pin_kasir` dan `barcode` telah dibersihkan dari `diagram/erd_parkspot.dbml`, 100% sinkron dengan Opsi B (NFC + Password Darurat).
- **Status Tahap 1**: 🟢 **100% SELESAI & TUNTAS**. Siap melangkah ke **Tahap 2 (Backend Web Laravel)**.

---

## Rencana Tahapan Eksekusi Bertahap (Next Steps)
Sesuai aturan di [`AGENTS.md`](file:///c:/xampp/htdocs/parkspot/AGENTS.md), eksekusi dilakukan secara bertahap satu per satu:

1. **Tahap 1: Sinkronisasi Dokumen Proposal UKK (Bab III)** 🟢 **(SELESAI 100%)**
   - [x] Perbarui penamaan entitas menjadi `master_*`, `trx_*`, dan `log_*`.
   - [x] Perbarui Diagram Use Case, Activity, Sequence, dan Pseudocode (Struk 58mm, Shift, Buffer Booking, Multi-Mall).
   - [x] Buat Skema ERD 3 Zona Warna + Tabel Pemetaan ke Firebase RTDB.
   - [x] Tempel seluruh 18 gambar diagram revisi ke dalam `PROPOSAL PARKSPOT UKK.docx`.
   - [x] Perbarui Daftar Isi, Daftar Tabel (7 tabel), Daftar Gambar (43 gambar), dan Daftar Lampiran beserta nomor halamannya.
2. **Tahap 2: Refactor Database & Backend Web Laravel (`parkspot_web`)**
   - Refactor model/service ke 12 entitas standar.
   - Implementasi generator PDF 58mm dinamis (`barryvdh/laravel-dompdf`) & cetak iframe kasir.
   - Implementasi Master Jadwal Shift, Tutup Paksa Shift, Absensi Kasir (NFC HP / Password Darurat Opsi B), & Blind Cash Closing.
   - Implementasi partisi data per mall di `database.rules.json` (`.indexOn`).
3. **Tahap 3: Penyesuaian Aplikasi Mobile Flutter (`parkspot_mobile`)**
   - Tambah layar pemilih Mall (Multi-Mall Switcher).
   - Tampilan denah 12 slot per zona berbasis grid 2x6.
   - Form booking time-first dengan buffer 30 menit.
   - Fitur sharing plat mobil keluarga (Kode Keluarga 6 digit).
   - Fitur Absensi Shift via NFC HP Samsung A54 (opsi petugas).
