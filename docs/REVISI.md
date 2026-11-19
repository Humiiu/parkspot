# Catatan & Rincian Lengkap Hasil Revisi Sidang Quality Plan UKK ParkSpot

Dokumen ini adalah **konsensus arsitektur final 99% bulletproof** hasil sidang debat multi-agent untuk proyek **ParkSpot** (14 November 2026). Dokumen ini menjadi pedoman tunggal dalam pembaruan Proposal UKK (Bab III), Web Laravel (`parkspot_web`), Mobile Flutter (`parkspot_mobile`), dan skema database Firebase Realtime Database.

---

## FONDASI UTAMA: 12 ENTITAS STANDAR SISTEM
Seluruh proposal, diagram, dan kode program menggunakan penamaan 12 entitas standar berikut (menggantikan awalan lama `tb_*` menjadi `master_*`, `trx_*`, dan `log_*`):

1. **`master_lokasi`**: Data mall/cabang (`id_lokasi`, `nama_lokasi`, `alamat`, `buffer_booking_menit`, `status_aktif`).
2. **`master_user`**: Data akun (`id_user`, `nama_lengkap`, `email`, `username`, `password_hash`, `role`, `assigned_id_lokasi`, `status_aktif`, serta indeks cepat `idx_email/{md5_email}`).
3. **`master_area_parkir`**: Lantai / Wing mall (`id_area`, `id_lokasi`, `nama_area`, `status_aktif`).
4. **`master_zona_parkir`**: Cluster berbasis *landmark* per lantai (misal: *Dekat Lobby Utama*, *Dekat Lift*) dengan kapasitas **maksimal 12 slot**, memiliki `prioritas_jarak_lobby`, `status_aktif`.
5. **`master_slot_parkir`**: Titik slot fisik (`id_slot`, `id_zona`, `kode_slot`, `posisi_grid` [1..12], `urutan_jarak` [1..12], `status_lampu` [hijau/oranye/merah], `plat_nomor`, `status_aktif`).
6. **`master_tarif`**: Pengaturan tarif per lokasi (`id_tarif`, `id_lokasi`, `jenis_kendaraan`, `tarif_per_jam`, `tarif_booking_awal`, `tarif_overstay_per_jam`).
7. **`master_kendaraan`**: Data fisik kendaraan unik (`plat_nomor_clean`, `jenis_kendaraan`, `warna`, `id_owner_utama`, `kode_keluarga`).
8. **`master_user_kendaraan`**: Relasi kepemilikan berbasis **Composite Key** (`{id_user}__{plat_nomor_clean}`, `peran_akses` [owner/family], `status_aktif`).
9. **`master_jadwal_shift`**: Pengaturan master jam kerja oleh Admin/Owner (`id_jadwal`, `id_lokasi`, `id_petugas`, `nama_shift` [Pagi/Siang], `jam_mulai`, `jam_selesai`, `tanggal_tugas`).
10. **`trx_shift_petugas`**: Siklus buka/tutup shift kasir, metode absensi (`nfc` / `password_darurat` + alasan darurat), rekonsiliasi kas (Blind Closing), dan audit selisih.
11. **`trx_parkir`**: Transaksi siklus hidup parkir (`id_parkir`, `id_lokasi`, `id_slot`, `plat_nomor_clean`, `guest_token`, `waktu_booking_mulai`, `waktu_booking_selesai`, `waktu_masuk`, `waktu_keluar`, `durasi_jam`, `biaya_total`, `status_bayar`, `metode_bayar`, `id_shift_keluar`, `reprint_count`).
12. **`log_aktivitas`**: Catatan jejak audit (*append-only*): login, gate in/out, tutup paksa shift, reset plat, selisih kas.

---

## RINCIAN PUTUSAN PER BUTIR REVISI

### Poin 1 & 2: Format Struk 58mm & Dual-Action Print (PDF Dinamis DomPDF + Direct Print)
* **Latar Belakang**: Penguji meminta cukup PDF seukuran struk agar siswa tidak gagal ujian karena ketergantungan hardware thermal.
* **Solusi Dual-Action (Web Kasir POS & Karcis Tamu)**:
  1. **Tombol "Cetak Struk" (Direct Print via Hidden Iframe)**: Memuat route `/receipt/{type}/{idLokasi}/{idParkir}/print` dengan CSS `@page { size: 58mm auto; margin: 0; }` ke `<iframe class="hidden">` lalu memanggil `iframe.contentWindow.print()`. Thermal printer langsung menyemburkan kertas tanpa navbar browser.
  2. **Tombol "Unduh PDF" (DomPDF 58mm Dinamis)**: Menggunakan `barryvdh/laravel-dompdf` dengan lebar tetap `164.41 pt` (58mm) dan **tinggi kertas dihitung dinamis** dari jumlah baris struk ditambah ruang QR Base64 PNG, sehingga struk pendek tidak boros kertas dan struk panjang tidak terpotong.
  3. **Kunci Snapshot Harga (Anti-Kecurangan)**: Saat transaksi keluar berstatus `selesai`, nominal dan durasi dikunci permanen. Cetak ulang dilarang menghitung ulang argo tarif dan otomatis diberi cap **`[CETAK ULANG]`**.
  4. **Keamanan URL Tamu (Anti-IDOR) & Cadangan Karcis Hilang**: URL QR karcis masuk memuat parameter acak `?t={guest_token}` agar pengunjung lain tidak bisa menebak ID tiket untuk mengintip argo orang lain. Jika karcis kertas 58mm hilang/basah/rusak saat akan keluar, petugas dapat memproses kendaraan keluar lewat jalur cadangan **Input Pelat Nomor Keluar** di pos kasir.

---

### Poin 3: Antisipasi Bentrok Booking Slot & Penanganan Overstay
* **Latar Belakang**: Penguji menyoroti risiko Orang 1 booking `07:00–15:00` lalu Orang 2 booking tepat di jam `15:00`, rawan bentrok di lapangan jika Orang 1 ngaret.
* **Solusi Teknis**:
  1. **Buffer Otomatis 30 Menit**: Setiap booking otomatis mengunci slot dengan jeda pengaman: booking `07:00–15:00` otomatis mengunci slot hingga `15:30`. Orang 2 di aplikasi hanya bisa memilih slot tersebut mulai jam `15:30` ke atas.
  2. **Alur Aplikasi Time-First**: Pengguna mobile memilih **Mall $\rightarrow$ Tanggal, Jam Datang, & Durasi** terlebih dahulu, baru sistem menampilkan denah slot yang aman dan tidak bentrok di jam tersebut.
  3. **Relokasi Otomatis saat Overstay Ekstrem**: Jika Orang 1 ngaret melewati buffer 30 menit (jam 15:30 belum keluar) padahal Orang 2 sudah tiba di gerbang, logika `gate-in` backend otomatis **merelokasi tiket Orang 2 ke slot kosong terdekat** di zona yang sama tanpa biaya tambahan. Orang 1 dikenakan denda argo overstay progresif.
  4. **On-Demand Sync (Bebas Masalah Cron Windows)**: Status booking hangus (*no-show* lewat 15 menit) atau aktivasi lampu oranye H-30 menit diperiksa secara otomatis setiap kali ada request ke server (*lazy sync*), sehingga sistem tetap 100% akurat saat demo tanpa tergantung scheduler OS.

---

### Poin 4: Denah Parkir Maksimal 12 Slot vs Mall Besar (Bintaro Jaya Xchange)
* **Latar Belakang**: Penguji menyatakan mustahil menjejalkan ribuan lahan mall seluas Bintaro Xchange ke denah 2D di layar ponsel.
* **Solusi Hierarki 3 Tingkat (Mall $\rightarrow$ Lantai $\rightarrow$ Zona Landmark)**:
  1. Mall Bintaro Xchange dibagi menjadi beberapa Zona berbasis *landmark* (contoh: *Zona A - Dekat Lobby Utama*, *Zona B - Dekat Lift Timur*).
  2. **Setiap Zona dibatasi ketat maksimal 12 slot** (grid 2 baris $\times$ 6 kolom).
  3. Tampilan di Flutter dan Web Admin sangat bersih, responsif, tidak lag, dan konsisten di semua mall.
  4. **Algoritma "Pilihkan Slot Terdekat" (1-Klik)**: Pengunjung tidak perlu menggeser puluhan zona. Sistem memilihkan slot hijau dengan rumus skor terendah: `skor = (prioritas_jarak_lobby * 100) + urutan_jarak_slot`.

---

### Poin 5: Registrasi Member, Composite Key, Mobil Keluarga & Anti-Troll
* **Latar Belakang**: Penjelasan istilah *Composite Key* (CK) dari pembimbing untuk kasus 1 mobil dipakai 2 akun email, serta pencegahan orang iseng.
* **Solusi Teknis**:
  1. **Penerapan Composite Key (CK)**:
     - Di `master_user`: `id_user` dan `email` adalah Candidate Key unik.
     - Pada tabel kepemilikan `master_user_kendaraan`, kuncinya adalah **Composite Key**: **`{id_user}__{plat_nomor_clean}`**.
  2. **Skenario Mobil Keluarga (Maksimal 2 Akun)**:
     - Pendaftar pertama plat nomor menjadi `owner_utama` dan sistem menerbitkan **Kode Keluarga (6 Karakter)**.
     - Anggota keluarga (email kedua) yang ingin mendaftarkan plat yang sama wajib memasukkan kode keluarga tersebut (maksimal dibatasi 2 akun).
  3. **Solusi Anti-Orang Iseng / Troll**:
     - Booking mewajibkan pembayaran jam pertama (*non-refundable*) dan dibatasi 1 booking aktif per akun (mencegah spam booking liar).
     - Plat nomor yang sedang parkir aktif di mall otomatis dikunci oleh indeks `idx_active_plate` sehingga tidak bisa dipakai booking oleh akun keluarga lain.
     - Jika ada orang iseng mendaftarkan plat orang lain, pemilik sah cukup datang membawa STNK fisik ke meja informasi, dan Admin memiliki tombol **"Reset / Cabut Kepemilikan Plat"** di dashboard.

---

### Poin 6: Rekap Shift Petugas, Master Jadwal Shift, Absensi Kartu & Audit Kas
* **Latar Belakang**: Audit jika grafik sistem mencatat Rp 12 juta tapi uang fisik hanya Rp 10 juta, serta pencegahan abuse buka-tutup shift liar oleh kasir.
* **Solusi Terstruktur**:
  1. **Master Jadwal Shift & Tutup Paksa Shift (Admin & Owner)**:
     - Jam kerja ditentukan oleh Admin maupun Owner (hak kelola shift Owner disamakan dengan Admin): **Shift 1 (Pagi: 07:00–15:00)** dan **Shift 2 (Siang: 15:00–23:00)**.
     - Petugas hanya bisa bertugas sesuai jadwal yang di-plot oleh Admin/Owner.
     - Jika petugas lupa/mendadak tidak klik Tutup Shift, Admin maupun Owner dapat melakukan **Tutup Paksa Shift** (dicatat ke `log_aktivitas`) agar shift berikutnya tidak macet.
     - Format ekspor laporan rekap Owner: **PDF & Excel** (standar industri).
  2. **Absensi Buka Shift Tanpa Beli Alat Mahal (2 Pilihan Resmi — Opsi B Anti-Abuse)**:
     - **Opsi Utama (Tap Kartu NFC via HP)**: Petugas menempelkan Kartu Identitas ke sensor NFC di HP Samsung Galaxy A54 milik pengguna $\rightarrow$ Web kasir di komputer otomatis terbuka (*clock-in*, `metode_absen = "nfc"`).
     - **Opsi Cadangan Darurat (Password Akun Petugas + 3 Pengaman Anti-Abuse)**: Jika kartu tertinggal atau NFC bermasalah, petugas dapat memakai **password akun petugasnya sendiri** dengan 3 kunci pengaman wajib:
       1. Tetap divalidasi harus sesuai **Master Jadwal Shift** (`master_jadwal_shift`) & cabang tugas (`assigned_id_lokasi`).
       2. Wajib mengisi **alasan darurat** $\rightarrow$ ditandai `metode_absen = "password_darurat"` dan dicatat otomatis ke `log_aktivitas` (notifikasi ke Admin/Owner).
       3. Hanya dapat dibuka dari **perangkat komputer Pos Kasir** (sesi perangkat pos terdaftar, mencegah titip absen dari HP pribadi di rumah).
     - *(Catatan: Opsi Barcode/QR Kartu Siswa dan 6 Digit PIN Kasir dihapus sesuai keputusan pengguna.)*
  3. **Pemisahan Kas Tunai vs QRIS**:
     - Sistem memisahkan `total_tunai_sistem` (wajib disetor fisik ke laci) dari `total_qris_sistem` (langsung masuk ke rekening bank owner).
  4. **Blind Cash Closing**:
     - Saat tutup shift, sistem menyembunyikan nominal kas komputer. Kasir wajib menghitung uang di laci dan menginput `uang_fisik_setor`.
     - Sistem menghitung `selisih_kas = uang_fisik_setor - uang_tunai_sistem`. Jika minus (misal $-Rp 2.000.000$), baris shift di dashboard Admin & Owner otomatis menyala merah untuk diaudit lengkap dengan durasi jam kerja, jumlah mobil yang dilayani, dan berita acara kasir.
  5. **Transaksi Lintas Shift**: Mobil yang masuk di shift pagi dan keluar di shift siang diikat pada `id_shift_keluar`, sehingga setoran kasir siang selalu klop 100% dengan transaksi keluar yang ia layani.

---

### Poin 7: Final ERD 3 Zona Warna & Pemetaan NoSQL Firebase
* **Solusi**:
  1. **Logical ERD 3 Zona Rapi**:
     - **Zona Master (Biru)**: `master_lokasi`, `master_user`, `master_area_parkir`, `master_zona_parkir`, `master_slot_parkir`, `master_tarif`, `master_kendaraan`, `master_user_kendaraan`, `master_jadwal_shift`.
     - **Zona Transaksi (Hijau)**: `trx_shift_petugas`, `trx_parkir`.
     - **Zona Audit (Abu-abu)**: `log_aktivitas`.
  2. **Aturan Soft Delete (`status_aktif = false`)**: Menggantikan ketiadaan `ON DELETE RESTRICT` di Firebase NoSQL agar riwayat transaksi dan audit lama tidak pernah korup saat data master dinonaktifkan (selaras dengan tipe `boolean` di ERD & JSON Proposal).
  3. **Pembaruan Diagram**: Use Case, Activity Diagram, dan Sequence Diagram di Proposal UKK Bab III ditambahkan alur Rekap Shift, Jadwal Shift, Pemilihan Mall, dan Buffer Booking.

---

### Poin 8: Fitur Multi-Lokasi / Multi-Mall
* **Solusi**:
  1. **Partisi Path Firebase**: Node operasional dipartisi per `/{id_lokasi}/...` (`master_slot_parkir/{id_lokasi}/...`, `trx_parkir/{id_lokasi}/...`).
  2. **Optimasi `.indexOn`**: Menambahkan index pada `database.rules.json` agar query pembacaan data per lokasi super cepat dan tidak mengunduh data mall lain.
  3. **Middleware Otorisasi Kasir**: Akun Petugas dikunci di sisi server sesuai cabang tugasnya (`assigned_id_lokasi`), mencegah petugas mall A memproses tiket mall B.
  4. **Persistent Mall Switcher**: Member di mobile memilih lokasi mall di awal dengan tampilan kartu mall (nama, alamat, tarif, slot kosong *real-time*).

---

### Catatan Review Proposal (19 November 2026): Sinkronisasi Deskripsi Wireframe (Opsi A)
* **Masalah**: Deskripsi Gambar 3.39 menyebut slot booking yang tidak didatangi dilepas manual oleh Petugas, dan Gambar 3.40 menyebut "toleransi 15 menit ke gerbang keluar" setelah bayar QRIS. Aturan itu tidak ada di Activity, Sequence, Pseudocode, maupun Rencana Pengujian.
* **Keputusan (Opsi A)**: Gambar wireframe tetap (rancangan sementara), tapi kalimat deskripsinya disamakan dengan diagram:
  1. **3.39**: Member memilih lokasi mal, jam kedatangan, dan durasi dulu, lalu memilih slot. Booking yang tidak didatangi lebih dari 15 menit **dibatalkan otomatis oleh sistem** dan slot kembali hijau (selaras UJI-08 & Poin 3 On-Demand Sync).
  2. **3.40**: Aturan "toleransi 15 menit ke gerbang keluar" **dihapus**. Setelah bayar QRIS, status tagihan menjadi lunas dan Petugas cukup memindai QR lalu membuka palang.

