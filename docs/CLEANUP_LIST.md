# Daftar File & Folder Sementara (Pra-Produksi ParkSpot)

Dokumen ini mencatat seluruh file bantu, skrip generator, arsip sementara, dan *draft* yang **wajib dibersihkan/dihapus** ketika **Tahap 1 (Proposal & Diagram)** selesai dan proyek mulai masuk ke **Tahap 2 (Proses Produksi / Ngoding Web Laravel & Mobile Flutter)** agar struktur repositori bersih dan profesional.

> **Status Pembersihan**: 🔴 Menunggu masuk tahap produksi (belum dihapus karena sebagian masih digunakan untuk revisi Proposal/PPT).

---

## 1. Skrip Generator & Teks Sementara (Root)
- [ ] `build_presentation.py` — skrip generator PPT lama
- [ ] `build_presentation_redesign.py` — skrip generator PPT redesign
- [ ] `create_tech_badges.py` — skrip pembuat badge teknologi PPT
- [ ] `2026-11-11-143429-halo.txt` — log teks sementara
- [ ] `PROPOSAL PARKSPOT UKK.txt` — hasil dump teks proposal sementara
- [ ] `CATATAN_DESAIN_PPT.md` — catatan desain PPT lama

## 2. File Arsip, Gambar Referensi & Draft PPT Lama (Root)
- [ ] `diagram.zip` — arsip zip sementara
- [ ] `diagram/revisi.zip` — arsip zip diagram revisi sementara
- [ ] `wireframes.zip` — arsip zip sementara
- [ ] `image.png` — gambar tangkapan layar sementara
- [ ] `revisi.jpeg` — foto catatan revisi (sudah dicatat rapi di `docs/REVISI.md`)
- [ ] `WhatsApp Image 2026-10-27 at 13.11.49.jpeg` — foto referensi lama
- [ ] `PPT_PARKSPOT_OUTPUT_TMP.pptx` — file output sementara PPT
- [ ] `PPT UKK 2025-2026 AIDAN SAYYID.pptx` — PPT referensi sekolah
- [ ] `TEMPLATE PPT QP UKK 2026-2027.pptx` — template kosong PPT
- [ ] Draft PPT lama lainnya (`ParkSpot - Presentasi UKK 2026-2027*.pptx` / versi lama selain 1 file PPT final yang dipilih)

## 3. Folder Eksperimen & Arsip Lama
- [ ] `scratch/` — folder uji coba script & test `.docx`
- [ ] `proposal lama/` — koleksi versi proposal sebelum final
- [ ] `ppt_assets/` — aset sementara generator PPT *(hapus setelah PPT final selesai)*

## 4. File Source Bantu Diagram (Hapus Setelah Diagram Final Masuk Proposal)
- [ ] `diagram/puml/` — file sumber PlantUML (`.puml`)
- [x] `diagram/puml_png/` — *(sudah dihapus bersih)*
- [ ] `diagram/erd_parkspot.dbml` — file sumber DBML `dbdiagram.io` *(hapus jika `diagram/erd.png` sudah final dan tidak perlu diedit lagi)*

## 5. Pembersihan Penutup
- [ ] `docs/CLEANUP_LIST.md` *(hapus file ini paling terakhir setelah seluruh daftar di atas bersih)*

---

## Daftar File & Folder INTI (WAJIB DIPERTAHANKAN — JANGAN DIHAPUS!)
- `parkspot_web/` (Kode utama Laravel)
- `parkspot_mobile/` (Kode utama Flutter)
- `database.rules.json`, `firebase.json`, `.firebaserc`, `.gitignore`
- `PROPOSAL PARKSPOT UKK.docx` (Final) & 1 file PPT Final
- `docs/REVISI.md`, `docs/PROGRESS.md`, `docs/CATATAN_BELAJAR_PRESENTASI_PARKSPOT.docx`
- `P2-SPK Rekayasa Perangkat Lunak.pdf`
- `diagram/` (hanya berisi gambar final yang dipakai di Proposal/PPT)
