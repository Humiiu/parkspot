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
| **1** | Format Cetak Struk PDF 58mm & Direct Print | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **2** | Pematangan Fitur Cetak Struk (Backend & Frontend) | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **3** | Antisipasi Bentrok Booking Slot (Buffer Time 30 Menit) | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **4** | Layout Parkir Statis Konsisten (Maksimal 12 Slot / Zona) | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **5** | Registrasi Member, Composite Key & Anti-Troll | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **6** | Rekap Shift Petugas, Master Jadwal, Absensi Kartu & Audit Kas | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **7** | Final ERD 3 Zona Warna & Sinkronisasi Diagram | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |
| **8** | Arsitektur Multi-Lokasi / Multi-Mall | 🟢 Disepakati (Konsensus Final) | 🔴 Pending | 🔴 Pending | 🔴 Pending |

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
