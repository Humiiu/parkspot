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
| **1** | Format Cetak Struk PDF 58mm & Direct Print | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |
| **2** | Pematangan Fitur Cetak Struk (Backend & Frontend) | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |
| **3** | Antisipasi Bentrok Booking Slot (Buffer Time 30 Menit) | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |
| **4** | Layout Parkir Statis Konsisten (Maksimal 12 Slot / Zona) | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |
| **5** | Registrasi Member, Composite Key & Anti-Troll | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |
| **6** | Rekap Shift Petugas, Master Jadwal, Absensi Kartu & Audit Kas | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |
| **7** | Final Logical ERD (12 Entitas) & Sinkronisasi Diagram | 🟢 Disepakati (Konsensus Final) | 🟡 ERD Final Tertempel (Tinggal Tempel Diagram UML) | 🔴 Pending | 🔴 Pending |
| **8** | Arsitektur Multi-Lokasi / Multi-Mall | 🟢 Disepakati (Konsensus Final) | 🟡 Teks/Tabel/Pseudocode Selesai (Tinggal Tempel Diagram) | 🔴 Pending | 🔴 Pending |

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
| **17** | `Diagram ParkSpot UKK-Sequence Rekap (OWNER) (Revisi)` | 🟡 Sedang Dikerjakan | 🟡 Sedang Dikerjakan | 🔴 Antre | Sedang dibangun Subagent Builder & diaudit Auditor v2 |

---

## Catatan Subagent & Safety State
- Subagent teamwork (`15741e8f-084c-4b8f-bf42-ef1787791374`) telah ditutup dengan aman setelah terkena kuota API.
- Seluruh artefak, log transkrip, dan prompt pipeline tersimpan aman di direktori `.system_generated/logs/` dan brain session.
- File asli milik pengguna di folder `diagram/` tetap utuh dan terisolasi dari folder `diagram/revisi/`.
- Sesi dapat dilanjutkan kapan saja dengan fokus pertama: me-review Diagram 4 (`Diagram ParkSpot UKK-Activity CRUD Area & Slot (Revisi)`).

---

## Rencana Tahapan Eksekusi Bertahap (Next Steps)
Sesuai aturan di [`AGENTS.md`](file:///c:/xampp/htdocs/parkspot/AGENTS.md), eksekusi dilakukan secara bertahap satu per satu:

1. **Tahap 1: Sinkronisasi Dokumen Proposal UKK (Bab III)**
   - Perbarui penamaan entitas menjadi `master_*`, `trx_*`, dan `log_*`.
   - Perbarui Diagram Use Case, Activity, Sequence, dan Pseudocode (Struk 58mm, Shift, Buffer Booking, Multi-Mall).
   - Buat Skema ERD 3 Zona Warna + Tabel Pemetaan ke Firebase RTDB.
2. **Tahap 2: Refactor Database & Backend Web Laravel (`parkspot_web`)**
   - Refactor model/service ke 11 entitas standar.
   - Implementasi generator PDF 58mm dinamis (`barryvdh/laravel-dompdf`) & cetak iframe kasir.
   - Implementasi Master Jadwal Shift, Absensi Kasir (NFC/Barcode/PIN), & Blind Cash Closing.
   - Implementasi partisi data per mall di `database.rules.json` (`.indexOn`).
3. **Tahap 3: Penyesuaian Aplikasi Mobile Flutter (`parkspot_mobile`)**
   - Tambah layar pemilih Mall (Multi-Mall Switcher).
   - Tampilan denah 12 slot per zona berbasis grid 2x6.
   - Form booking time-first dengan buffer 30 menit.
   - Fitur sharing plat mobil keluarga (Kode Keluarga 6 digit).
   - Fitur Absensi Shift via NFC HP Samsung A54 (opsi petugas).
