# Panduan & Aturan Proyek ParkSpot (Antigravity Rules)

Rules ini berlaku untuk seluruh proses pengerjaan dan pengembangan proyek **ParkSpot** (Proposal UKK, Web Laravel, Mobile Flutter, Database Firebase, Presentasi/PPT) dan **WAJIB DIBACA SERTA DIPATUHI OLEH SETIAP MODEL/ASISTEN AI**.

---

## 1. Tentang Proyek & Referensi Utama
- **Nama Proyek**: ParkSpot (Sistem Pengelolaan Parkir Terpadu Berbasis Web & Mobile - UKK Paket 2).
- **Komponen Sistem**:
  - **Web (Laravel + Tailwind)**: Pos Gerbang Masuk & Keluar (Petugas), Dashboard Master & Monitoring Denah (Admin), Rekap Finansial & Shift (Owner), Web Mobile Karcis & Billing (Pengunjung Tamu / Guest).
  - **Mobile (Flutter)**: Aplikasi Pelanggan Member (Booking Slot Terdekat, Status Parkir Berjalan, QR Tiket, Bayar Non-Tunai QRIS).
  - **Database**: Firebase Realtime Database.
- **Arsitektur Dokumentasi (Dispatcher Pattern)**:
  - File `AGENTS.md` ini berfungsi sebagai **Core Rules & Dispatcher** (pengarah utama).
  - Rincian latar belakang masalah revisi, solusi teknis, dan catatan penguji disimpan di: [`docs/REVISI.md`](file:///c:/xampp/htdocs/parkspot/docs/REVISI.md).
  - Lembar pelacakan progres pengerjaan disimpan di: [`docs/PROGRESS.md`](file:///c:/xampp/htdocs/parkspot/docs/PROGRESS.md).

---

## 2. Siklus Kerja Disiplin & Instruction Set (WAJIB DIPATUHI)

### A. Ritual Pra-Eksekusi ("Baca Bismillah / Mantapkan Instruksi Dulu")
- Sebelum mengubah kode, mengedit dokumen proposal, membuat file, atau menjalankan perintah apa pun:
  - **Wajib membaca dan me-review kembali seluruh instruksi pengguna** secara saksama.
  - Review dokumen acuan terkait di [`docs/REVISI.md`](file:///c:/xampp/htdocs/parkspot/docs/REVISI.md) dan [`docs/PROGRESS.md`](file:///c:/xampp/htdocs/parkspot/docs/PROGRESS.md).
  - Mantapkan pemahaman terlebih dahulu, jangan tergesa-gesa menulis kode atau mengedit teks tanpa arah yang pasti.

### B. Zero Assumptions & Scope Guardrail (Dilarang Keras Berasumsi)
- **Dilarang keras berasumsi sendiri** jika menemukan instruksi yang ambigu atau belum jelas. **Tanyakan langsung ke pengguna!**
- **Dilarang melakukan hal-hal di luar perintah pengguna** (jangan menambah dependency sembarangan, mengubah skema tabel di luar instruksi, atau menambah fitur ekstra tanpa diminta).
- Jika memiliki ide inovatif atau usulan arsitektur: **usulkan dan diskusikan terlebih dahulu**, jangan dieksekusi sebelum disetujui pengguna.

### C. Protokol Uji Kritis & Debat ("Grill-Me & Red Teaming" Sebelum Disimpan)
- **Dilarang langsung mengiyakan (yes-man) atau menyimpan catatan begitu saja**:
  - Asisten **wajib mencari celah**, titik lemah (*weak points*), potensi pertanyaan jebakan dari penguji sidang, serta risiko teknis dari setiap ide atau poin revisi yang diajukan pengguna.
  - Asisten **wajib mendebat secara konstruktif**, mengajukan pertanyaan balik, dan menyodorkan opsi solusi atau saran alternatif.
  - Tujuannya agar solusi yang diambil benar-benar matang (*bulletproof*), tahan banting saat diuji penguji, dan tidak menimbulkan rasa over-confidence yang semu.
  - Catatan revisi dan eksekusi baru boleh difinalisasi setelah melalui perdebatan dan disepakati bersama.

### D. Eksekusi Bertahap (Satu per Satu)
- Kerjakan tugas revisi atau fitur secara bertahap, **satu per satu** sesuai urutan rencana yang disepakati.
- **Dilarang borongan** atau melompati langkah pengerjaan tanpa kendali.

### D. Double-Check & Verifikasi Wajib
- Sebelum menyatakan suatu tugas, perbaikan proposal, atau kode selesai:
  - Lakukan **double-check** menyeluruh terhadap dokumen atau file kode yang diubah.
  - Untuk kode: pastikan syntax bersih, tidak menimbulkan error baru (*zero regressions*), dan logikanya sesuai skema revisi.
  - Untuk proposal: pastikan konsistensi antar-bab (Use Case, Activity, Sequence, ERD, Pseudocode, dan Wireframe sinkron).

### E. Pencatatan Progres Terpusat
- Setiap pencapaian, perubahan, atau penyelesaian task:
  - **Wajib dicatat dan diperbarui** statusnya di [`docs/PROGRESS.md`](file:///c:/xampp/htdocs/parkspot/docs/PROGRESS.md) dan [`docs/REVISI.md`](file:///c:/xampp/htdocs/parkspot/docs/REVISI.md).
  - Pastikan daftar ceklis selalu mencerminkan kondisi riil sistem saat ini.

### F. Protokol Git Commit Antar-Perangkat (Sync & Backup)
- Setiap kali suatu milestone, tugas revisi, atau fitur selesai dan terverifikasi:
  - **Wajib lakukan Git Commit** dengan gaya pesan: **Bahasa Indonesia santai/gaul, huruf kecil semua di awal (tanpa huruf kapital), yang penting ada inti informasinya** (contoh: `revisi cetak struk pdf`, `benerin tabel master_user`, `pindah device lagi`, `tambah jeda buffer booking`).
  - *Catatan*: Gaya santai dan huruf kecil ini KHUSUS untuk pesan `git commit` saja. Respons chat asisten TETAP rapi, profesional, dan terstruktur.
  - Untuk tweak kecil/minor: biarkan terakumulasi atau tanyakan ke pengguna sebelum commit.
  - Berikan pengingat push git agar riwayat pengerjaan aman tersinkronisasi antar-perangkat (PC Desktop & Laptop Vivobook).

### G. Pemisahan Spesifikasi Teknis & Progres
- File `AGENTS.md` dijaga tetap ringkas dan tidak membengkak agar hemat token. Seluruh detail teknis, skema revisi per nomor, dan status pengerjaan wajib didelegasikan ke folder `docs/`.

---

## 3. Protokol Penanganan Masalah & Diagnosa Bug
Saat menangani bug, error, atau penyesuaian logika:
1. Analisis dan jelaskan terlebih dahulu **akar masalah (root cause)** secara jelas dan ringkas.
2. Paparkan opsi **solusi penyelesaiannya**.
3. **Konfirmasikan ke pengguna** sebelum melakukan tindakan perubahan besar.
