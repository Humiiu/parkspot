# Catatan Progres & Spesifikasi Desain Ulang PPT UKK — ParkSpot

> **Catatan Sementara untuk Claude (Sonnet / Opus):**  
> File ini berisi hasil audit lengkap atas PPT sebelumnya, seluruh ketentuan resmi dari sekolah/user, koordinat layout aman template, fakta proyek dari proposal, dan panduan teknis eksekusi.  
> **PENTING:** Setelah PPT selesai didesain ulang, digenerate, dan diverifikasi secara visual, **hapus file `CATATAN_DESAIN_PPT.md` ini** (beserta file preview sementara jika ada) agar tidak menumpuk di repositori.

---

## 1. Kondisi Saat Ini & Akar Masalah (Mengapa PPT Sebelumnya "Hancur")

File saat ini:
- Output terbaru: `PPT UKK 2026-2027 JUNDI FAIQ - PARKSPOT (REVISI).pptx` (14 slide)
- Draft awal: `PPT UKK 2026-2027 JUNDI FAIQ - PARKSPOT.pptx` (14 slide)
- Template resmi sekolah: `TEMPLATE PPT QP UKK 2026-2027.pptx` (14 slide, 16:9 `13.333" x 7.50"`)
- Referensi contoh siswa lain: `PPT UKK 2025-2026 AIDAN SAYYID.pptx` (12 slide)
- Script generator lama: `build_presentation.py` (715 baris) & `create_tech_badges.py` (54 baris)

### Audit Penyebab Kerusakan Visual & Ketidaksesuaian Aturan:
1. **Warna Font Salah / Tidak Kontras pada Cover (Slide 1) & Penutup (Slide 14):**
   - `TEMPLATE PPT QP UKK 2026-2027.pptx` memiliki kotak gradasi **Pink → Ungu (`#FD1697` → `#8C2BDF`, alpha 80%)** di Slide 1 (`Google Shape;109;p1`, posisi `left=1.67", top=1.02", width=11.16", height=5.83"`) dan gradasi full-screen (`0, 0, 13.333" x 7.50"`) di Slide 13 & Slide 14.
   - Template asli menggunakan warna teks **PUTIH (`#FFFFFF` / `lt1`)** di atas gradasi tersebut.
   - `build_presentation.py` justru mengganti warna teks Slide 1 menjadi `#B60D79` (magenta gelap) & `#2B2B2B` (abu gelap), dan Slide 14 menjadi `#B60D79` & `#1B365D` (navy gelap) di atas gradasi Pink-Ungu — hasilnya tabrakan warna dan nyaris tidak terbaca. Selain itu, judul Slide 1 diletakkan di `top=0.95"`, menabrak tepi atas kotak gradasi (`top=1.02"`).
2. **Menabrak Logo & Sidebar Background Template (`tpl_image2.png`):**
   - Seluruh slide konten (`Slide 2–12` di template) menggunakan gambar background `13.333" x 7.50"` (`ppt/media/image2.png`) yang berisi elemen permanen:
     - **Sidebar Pink kiri ("WE ARE THE PIONEER OF IT SCHOOL"):** `x = 0.43" .. 1.33"`, `y = 0.00" .. 3.49"`
     - **Logo Sekolah atas ("Bina Informatika Bintaro Vocational High School"):** `x = 1.55" .. 4.24"`, `y = 0.22" .. 0.76"`
     - **Garis Magenta Footer bawah:** `x = 0.42" .. 12.85"`, `y = 7.16" .. 7.19"`
   - Script lama menaruh judul di `left=1.20", top=0.55"` dan konten di `left=1.20", top=1.65"` sehingga **menabrak langsung sidebar pink di kiri dan logo sekolah di atas**.
3. **Melanggar Ketentuan Resmi Quality Plan UKK (Screenshot Aturan User):**
   - Jumlah slide saat ini **14 slide** → aturan mewajibkan **maksimal 10 slide**.
   - Memakai font `Raleway` → aturan mewajibkan **`Arial` atau `Times New Roman`** (termasuk rawan font fallback di komputer lain).
   - Ukuran teks lama `10–11.5 pt` (isi) dan `28 pt` (judul) → aturan mewajibkan **Judul 32 pt, Subjudul 28 pt, Deskripsi 24 pt**.
   - Kepadatan teks lama **10–18 baris paragraf padat per slide** → aturan mewajibkan **maksimal 6 baris per slide**.
4. **Bug `python-pptx` Formatting:**
   - Script lama banyak menulis `p.text = ...` lalu mengubah `p.font.size/color/name` di level paragraph (`defRPr`), bukan di level `run` (`r.font`). Banyak XML `<a:rPr>` tercatat kosong (`None`) sehingga rentan tertimpa style bawaan template. **Wajib atur font, ukuran, warna, dan bold di level `run`!**
5. **Path Hardcode & Environment Python:**
   - `build_presentation.py` (baris 11–16) dan `create_tech_badges.py` (baris 5) masih hardcode `c:\xampp\htdocs\parkspot\...`, padahal repo berada di `c:\Users\jundi\StudioProjects\parkspot`. Wajib ubah ke path relatif (`os.path.dirname(os.path.abspath(__file__))`).
   - Di Python `3.14.7` PC ini: `PIL` (Pillow) dan `python-docx` sudah terpasang, tetapi `python-pptx` **belum terpasang** (`pip install python-pptx` terlebih dahulu sebelum menjalankan script).
6. **Inkonsistensi Fakta Konten dengan Proposal (`PROPOSAL PARKSPOT UKK.docx`):**
   - Warna indikator slot booking di Slide 10 tertulis `Kuning`, sedangkan di Slide 4, Slide 12, dan seluruh Proposal tertulis **Oranye** (`Hijau = Kosong`, `Oranye = Booking`, `Merah = Terisi`). **Gunakan Oranye.**
   - Nama tabel/node database di Slide 9 lama (`users`, `areas`, `slots`, `vehicles`, `rates`, `parkings`, `activity_logs`) berbeda dengan Proposal Tabel 3.3 & Lampiran 1 (`tb_user`, `tb_area_parkir`, `tb_slot_parkir`, `tb_tarif`, `tb_kendaraan`, `tb_transaksi`, `tb_log_aktivitas`). **Samakan dengan Proposal & ERD (`tb_*`).**

---

## 2. Aturan Wajib Desain & Konten (HARUS DIPATUHI 100%)

### A. Ketentuan Resmi Slide Presentasi Quality Plan UKK (dari User)
1. **Ukuran Judul:** **`32 pt`**
2. **Ukuran Subjudul:** **`28 pt`**
3. **Ukuran Deskripsi Subjudul:** **`24 pt`**
4. **Kepadatan per Slide:** **Maksimal 6 baris teks dalam 1 slide**
5. **Jenis Font:** **`Arial`** atau `Times New Roman` → **Pilih `Arial`** (seragam dengan theme font template `Arial / Arial`, modern, tajam di proyektor)
6. **Jumlah Slide:** **Maksimal 10 Slide** (buat **tepat 10 slide**)
7. **Isi Slide:** Poin-poin penting sesuai materi sidang (singkat, padat, bukan paragraf panjang)
8. **Template:** Menggunakan **`TEMPLATE PPT QP UKK 2026-2027.pptx`** (SMK Bina Informatika Bintaro)

> **Tips Penting agar `24 pt` Muat dalam Maksimal 6 Baris & Terlihat Bagus:**
> - Dengan font `24 pt`, tiap poin harus ringkas (**1 baris per poin**, sekitar 4–8 kata, hindari kalimat panjang yang wrap jadi 2–3 baris).
> - Gunakan gaya **presentasi eksekutif**: judul singkat (`32 pt`), subjudul/label (`28 pt`), dan 3–5 poin pendek (`24 pt`), dipadukan dengan visual (diagram/wireframe/card bersih/badge) agar tidak hambar dan tetap nyaman dipandang.

### B. 8 Poin Materi Sidang Wajib (dari `WhatsApp Image 2026-10-27 at 13.11.49.jpeg`)
1. Judul dan nama siswa (perkenalan)
2. Proses bisnis sistem yang akan diusulkan:
   - Sistem yang dibuat
   - Menggunakan program apa
   - Mengapa menggunakan program yang dipilih
3. Kompetitor / persaingan (atau sistem yang ada saat ini / berjalan)
4. Use Case Diagram
5. Activity Diagram
6. Database
7. Design Rancangan
8. Kesimpulan

---

## 3. Koordinat Aman & Panduan Warna Template (`TEMPLATE PPT QP UKK 2026-2027.pptx`)

Ukuran slide: **`13.333" x 7.500"`** (Widescreen 16:9).

### A. Zona Aman Slide Konten (Latar Putih — `Slide 2` s.d. `Slide 9`)
- **Area TERLARANG (jangan ditumpuk teks/card/gambar):**
  - Kiri atas (Sidebar Pink): `x = 0.00" .. 1.40"`, `y = 0.00" .. 3.55"`
  - Atas kiri (Logo SMK Bina Informatika): `x = 1.45" .. 4.35"`, `y = 0.00" .. 0.82"`
  - Bawah (Garis Magenta Footer): `y >= 7.05"`
- **Area AMAN:**
  - **Judul Slide (`32 pt` Bold):** `left = 1.55"`, `top = 0.85"`, `width = 11.20"`, `height = 0.65"`
  - **Konten Utama (Teks, Card, Diagram, Wireframe):**
    - `left = 1.55"` (margin kanan `0.53"` → batas kanan `12.80"`, total lebar konten **`11.25"`**)
    - `top = 1.60"` hingga `bottom = 6.95"` (total tinggi konten **`5.35"`**)
  - *Catatan:* Untuk gambar/diagram di bagian bawah (`y > 3.60"`), tetap sejajarkan `left >= 1.55"` agar layout rapi konsisten.

### B. Palet Warna yang Benar & Kontras
- **Slide 1 (Cover) & Slide 10 (Penutup)** — berada di atas kotak gradasi Pink-Ungu template (`#FD1697` → `#8C2BDF`):
  - **Semua Teks Wajib PUTIH (`#FFFFFF`)** (boleh pakai `#FCE7F3` sangat terang untuk label/tahun bila perlu, tetap kontras tinggi).
  - Pada Slide 1, seluruh teks wajib berada di dalam area kotak gradasi `Google Shape;109;p1` (`left = 1.67"`, `top = 1.02"`, `width = 11.16"`, `height = 5.83"`), misalnya mulai `left = 2.00"`, `top = 1.35"`, `width = 10.50"`.
  - Pada Slide 10 (gunakan Slide 13 `TERIMA KASIH` atau Slide 14 `TANYA JAWAB` dari template; di sebelah kanan bawah ada ilustrasi bawaan template `x=5.56"..13.33", y=1.97"..7.15"`), pastikan teks `TERIMA KASIH & TANYA JAWAB` berwarna **PUTIH (`#FFFFFF`)** dan terbaca jelas (posisikan di tengah/kiri atau beri kontras bersih agar tidak tertutup ilustrasi).
- **Slide 2–9 (Konten Latar Putih):**
  - **Judul (`32 pt`, Bold):** `#B60D79` (Magenta resmi sekolah / template)
  - **Subjudul (`28 pt`, Bold):** `#1B365D` (Deep Navy) atau `#B60D79` (Magenta aksen)
  - **Deskripsi (`24 pt`, Regular/Bold):** `#1E293B` (Dark Slate — tajam & mudah dibaca)
  - **Card / Panel Visual (opsional untuk mempercantik):**
    - Fill `#F8FAFC` atau `#FFFFFF`, border tipis `1 pt` `#DCE2EC`, ditambah aksen garis tipis `#B60D79` atau `#8C2BDF` (di atas atau kiri card).
    - Pastikan padding dalam card cukup dan tidak membuat teks `24 pt` meluber.
  - **Warna Indikator Slot Parkir (konsisten):**
    - Hijau `#16A34A` = Kosong
    - Oranye `#EA580C` = Booking
    - Merah `#DC2626` = Terisi

### C. Cara Menjadikan Template 14 Slide → Tepat 10 Slide Secara Aman
Template asli memiliki 14 slide:
- Index `0`: Cover (gradasi Pink-Ungu)
- Index `1..11`: 11 slide konten putih (`tpl_image2.png`)
- Index `12`: Penutup `TERIMA KASIH` (gradasi Pink-Ungu + ilustrasi)
- Index `13`: Penutup `TANYA JAWAB` (gradasi Pink-Ungu + ilustrasi)

**Langkah yang disarankan:**
- Gunakan index `0` (Slide 1 Cover), index `1..8` (8 slide konten putih: Slide 2–9), dan index `12` atau `13` (Slide 10 Penutup).
- Hapus 4 slide yang tidak dipakai (`index 9, 10, 11`, dan salah satu dari `12`/`13`) via XML `prs.slides._sldIdLst` + `prs.part.drop_rel(rId)` dari index terbesar ke terkecil agar tidak merusak XML relasi.
- **Jangan hapus background `<p:bg>`**, cukup bersihkan shape teks placeholder lama pada slide konten 2–9.

---

## 4. Rekomendasi Pemetaan 10 Slide (Semua Aturan Terpenuhi)

| No | Judul Slide (`32 pt`) | Elemen Visual & Aset | Konten Singkat (`28 pt` subjudul / `24 pt` isi, **≤ 6 baris/slide**) |
|---|---|---|---|
| **1** | **QUALITY PLAN UKK — PARKSPOT** *(Cover)* | Kotak gradasi Pink-Ungu template (Teks **Putih `#FFFFFF`**) | Subjudul (`28 pt`): *Sistem Pengelolaan Parkir Terpadu (Web & Mobile)*<br>Deskripsi (`24 pt`, max 5 baris): Nama (`Jundi Faiq Mahbubillah`), Kelas/NIS (`XII RPL • 24183041`), Paket 2 (`Pengembangan Aplikasi Parkir`), Tahun `2026–2027` |
| **2** | **PROSES BISNIS SISTEM** | 4 card / alur step berurutan horizontal/grid rapi | **1. Gate Masuk:** Input pelat, alokasi slot terdekat otomatis, karcis QR.<br>**2. Tamu (Guest Web):** Scan QR karcis, lihat denah & tagihan.<br>**3. Member (Mobile):** Booking slot (Oranye), bayar QRIS pre-exit.<br>**4. Gate Keluar:** Scan QR, hitung tarif otomatis, cetak struk. *(≤ 6 baris total)* |
| **3** | **TEKNOLOGI & ALASAN PEMILIHAN** | 5 badge di `ppt_assets/badges/` (atau card modern ikon/warna) | **Laravel 11:** Web pos, admin, owner & API aman.<br>**Flutter:** Aplikasi mobile member cross-platform cepat.<br>**Firebase RTDB:** Sinkronisasi status slot & transaksi real-time.<br>**Tailwind & Midtrans:** UI web cepat & simulasi QRIS. *(≤ 6 baris total)* |
| **4** | **ANALISIS KOMPETITOR & SISTEM BERJALAN** | Tabel / 3 kolom perbandingan bersih (Parkir Biasa vs Parkee/Secure vs ParkSpot) | Perbandingan ringkas (header + 4 baris aspek = 5 baris):<br>• Cari Slot (Manual vs Kuota/Sensor vs **Slot Terdekat Tanpa Sensor**)<br>• Akses Tamu (Kertas vs Kasir vs **Karcis QR + Web Tamu**)<br>• Booking (Tidak ada vs Terbatas vs **Booking Oranye + Batas Waktu**)<br>• Bayar (Tunai keluar vs Gerbang keluar vs **QRIS dari Dalam Mal**) |
| **5** | **USE CASE DIAGRAM** | Kiri: `diagram/Diagram ParkSpot UKK-Use Case.drawio.png` (aspect `0.74`) | Kanan: Subjudul (`28 pt`): *5 Hak Akses Pengguna* + 5 poin (`24 pt`, 1 baris/poin):<br>• **Admin:** Kelola user, area, slot, tarif, log<br>• **Petugas:** Input masuk, jaga booking, kasir keluar<br>• **Owner:** Pantau statistik & rekap transaksi<br>• **Member:** Booking slot & bayar QRIS mobile<br>• **Guest:** Cek denah & tagihan via QR web |
| **6** | **ACTIVITY DIAGRAM (ALUR INTI)** | Gambar `Diagram ParkSpot UKK-activity kendaraan masuk.drawio.png` & `Diagram ParkSpot UKK-Activity Keluar gerbang & Print Struk.drawio.png` | Subjudul + max 4–5 poin singkat (`24 pt`):<br>• Petugas input pelat & jenis kendaraan di gate masuk<br>• Sistem pilih slot kosong terdekat & cetak karcis QR<br>• Pembayaran tunai di kasir atau QRIS pre-exit<br>• Scan tiket keluar, palang buka, slot kembali Hijau |
| **7** | **PERANCANGAN DATABASE (ERD)** | Kiri: `diagram/Diagram ParkSpot UKK-ERD.drawio.png` (aspect `1.42`) | Kanan: Subjudul (`28 pt`): *7 Node Firebase RTDB* + poin ringkas (`24 pt`, ≤ 5 baris):<br>• `tb_user` & `tb_kendaraan`: Akun multi-role & pelat<br>• `tb_area_parkir` & `tb_slot_parkir`: Zona & status 3 warna<br>• `tb_tarif`: Tarif reguler & booking per jenis<br>• `tb_transaksi`: Tiket QR, durasi, biaya & lunas<br>• `tb_log_aktivitas`: Catatan audit aktivitas sistem |
| **8** | **DESAIN RANCANGAN ANTARMUKA** | Gabungan wireframe terpilih dari `wireframes/` (`petugas_gate_in.png`, `admin_denah_slot.png`, `owner_dashboard.png`, `member_beranda.png`, `guest_karcis_digital.png`) | Subjudul (`28 pt`) + 4 poin singkat (`24 pt`):<br>• **Web Petugas & Admin:** Pos gate & denah 3 warna<br>• **Web Owner:** Dashboard omzet & laporan ekspor<br>• **Mobile Member:** Cek slot, booking & bayar QRIS<br>• **Web Guest:** Karcis digital & panduan tanpa install app |
| **9** | **KESIMPULAN HASIL PROJEK** | 4 card bernomor atau daftar poin terstruktur rapi | Subjudul (`28 pt`): *Pencapaian Utama ParkSpot* + 4–5 poin padat (`24 pt`):<br>• Integrasi Web Laravel & Mobile Flutter via Firebase real-time<br>• Penempatan slot terdekat otomatis hemat biaya tanpa sensor<br>• Karcis QR Guest & QRIS Sandbox mempercepat antrean keluar<br>• Hak akses Admin, Petugas, Owner & Member berjalan terpadu |
| **10** | **TERIMA KASIH & TANYA JAWAB** | Slide penutup template (gradasi Pink-Ungu + ilustrasi, Teks **Putih `#FFFFFF`**) | Judul (`32 pt` / besar jelas): **TERIMA KASIH**<br>Subjudul (`28 pt`): **Sesi Tanya Jawab & Masukan Penguji**<br>Deskripsi (`24 pt`): *Jundi Faiq Mahbubillah • XII RPL • ParkSpot* |

> **Catatan tentang Ukuran Font vs Tabel/Caption:**  
> Pastikan seluruh judul slide = `32 pt`, subjudul = `28 pt`, teks deskripsi/poin utama = `24 pt`, font = `Arial`, maksimal 6 baris teks per slide! Jika menyertakan tabel di Slide 4, jaga isinya super singkat (maks 5 baris tabel termasuk header, font besar/terbaca, tidak sesak).

---

## 5. Daftar Aset & Dimensi Asli di Workspace

### A. Wireframes (`wireframes/`)
- `admin_denah_slot.png` (`2560x2460`, aspect `1.04`)
- `admin_log_aktivitas.png` (`2560x3136`, aspect `0.82`)
- `admin_master_area.png` (`2880x2574`, aspect `1.12`)
- `owner_dashboard.png` (`2560x2536`, aspect `1.01`)
- `owner_laporan.png` (`2560x2076`, aspect `1.23`)
- `petugas_gate_in.png` (`2560x2774`, aspect `0.92`)
- `petugas_modal_kasir.png` (`2560x2048`, aspect `1.25`)
- `member_beranda.png` (`780x2300`, aspect `0.34` — portrait tinggi)
- `member_booking.png` (`780x2352`, aspect `0.33` — portrait tinggi)
- `member_qris.png` (`780x1924`, aspect `0.41` — portrait tinggi)
- `guest_denah_panduan.png` (`780x2510`, aspect `0.31` — portrait tinggi)
- `guest_karcis_digital.png` (`780x2606`, aspect `0.30` — portrait tinggi)

> **Perhatian Aspect Ratio:** Gambar mobile/guest memiliki rasio sangat tinggi (`0.30–0.41`). Jangan dipaksa masuk ke kotak terlalu lebar/pendek agar tidak jadi kecil sekali; berikan tinggi yang cukup (mis. `height = 3.8"–4.4"`) atau crop/tata rapi berdampingan dengan web landscape/square.

### B. Diagram Utama (`diagram/`)
- `Diagram ParkSpot UKK-Use Case.drawio.png` (`992x1342`, aspect `0.74`)
- `Diagram ParkSpot UKK-ERD.drawio.png` (`2283x1608`, aspect `1.42`)
- `Diagram ParkSpot UKK-DFD LEVEL 0.drawio.png` (`1462x912`, aspect `1.60`)
- `Diagram ParkSpot UKK-FlowChart.drawio.png` (`2162x922`, aspect `2.34`)
- `Diagram ParkSpot UKK-activity kendaraan masuk.drawio.png` (`1738x732`, aspect `2.37`)
- `Diagram ParkSpot UKK-Activity Keluar gerbang & Print Struk.drawio.png` (`1242x1722`, aspect `0.72`)

### C. Badges (`ppt_assets/badges/`)
- `laravel.png`, `flutter.png`, `firebase.png`, `tailwind.png`, `midtrans.png` (`380x120 px`). Dapat dipakai atau diperbaiki ulang di `create_tech_badges.py` (mis. gunakan font `Arial` agar seragam).

---

## 6. Checklist Verifikasi Wajib untuk Claude (Setelah Mengubah `build_presentation.py`)

1. **Install `python-pptx` bila belum ada:** `pip install python-pptx`
2. **Perbaiki path di `build_presentation.py` & `create_tech_badges.py`** menjadi dinamis (`BASE_DIR = os.path.dirname(os.path.abspath(__file__))`).
3. **Jalankan generator** dan simpan ke `PPT UKK 2026-2027 JUNDI FAIQ - PARKSPOT (REVISI).pptx` (dan update `PPT UKK 2026-2027 JUNDI FAIQ - PARKSPOT.pptx` atau hanya versi revisi sesuai konvensi).
4. **Cek programatik XML:**
   - Total slide == **10** (≤ 10).
   - Font semua run == **`Arial`**.
   - Ukuran font judul == `32 pt`, subjudul == `28 pt`, deskripsi == `24 pt`.
   - Tidak ada teks menumpuk area logo (`x=1.45..4.35", y=0..0.82"`), sidebar pink (`x=0..1.40", y=0..3.55"`), atau footer (`y>=7.05"`).
   - Warna teks Cover & Penutup == Putih `#FFFFFF` di atas gradasi Pink-Ungu.
5. **Render & Inspeksi Visual Nyata (WAJIB!):**
   - Microsoft PowerPoint terpasang di `C:\Program Files\Microsoft Office\root\Office16\POWERPNT.EXE`.
   - Ekspor seluruh 10 slide ke PNG sementara menggunakan PowerPoint COM (`Export` / `SaveAs`) ke folder sementara, lalu periksa setiap gambar slide secara visual untuk memastikan tidak ada teks terpotong, overlap, atau kontras buruk.
6. **Bersih-Bersih:**
   - Hapus folder/gambar PNG preview sementara.
   - **Hapus file `CATATAN_DESAIN_PPT.md` ini** setelah PPT terverifikasi rapi.
