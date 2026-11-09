import os
import pptx
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.enum.shapes import MSO_SHAPE
from PIL import Image

def generate_deck():
    template_path = r'c:\xampp\htdocs\parkspot\TEMPLATE PPT QP UKK 2026-2027.pptx'
    output_path = r'c:\xampp\htdocs\parkspot\PPT UKK 2026-2027 JUNDI FAIQ - PARKSPOT (REVISI).pptx'
    output_path_alt = r'c:\xampp\htdocs\parkspot\PPT UKK 2026-2027 JUNDI FAIQ - PARKSPOT.pptx'
    diagram_dir = r'c:\xampp\htdocs\parkspot\diagram'
    wireframe_dir = r'c:\xampp\htdocs\parkspot\wireframes'
    badge_dir = r'c:\xampp\htdocs\parkspot\ppt_assets\badges'

    prs = Presentation(template_path)
    slides = prs.slides

    # Colors matching school identity & clean aesthetic
    COLOR_TITLE = RGBColor(0xB6, 0x0D, 0x79)   # SMK Bina Informatika Magenta/Maroon
    COLOR_BODY = RGBColor(0x2B, 0x2B, 0x2B)    # Dark Charcoal
    COLOR_MUTED = RGBColor(0x55, 0x55, 0x55)   # Muted Gray
    COLOR_PRIMARY = RGBColor(0x1B, 0x36, 0x5D) # Deep Navy
    COLOR_CARD_BG = RGBColor(0xF9, 0xFA, 0xFB) # Very subtle off-white
    COLOR_BORDER = RGBColor(0xDC, 0xE2, 0xEC)  # Clean border
    COLOR_SUCCESS = RGBColor(0x15, 0x80, 0x3D) # Emerald Green for Pass/Success
    FONT_NAME = 'Raleway'

    def set_title(slide, title_text):
        title_shape = None
        for s in slide.shapes:
            if s.has_text_frame and s.top < Inches(1.5):
                title_shape = s
                break
        if title_shape is None:
            title_shape = slide.shapes.add_textbox(Inches(1.2), Inches(0.55), Inches(11.0), Inches(0.85))
        
        tf = title_shape.text_frame
        tf.word_wrap = True
        tf.clear()
        p = tf.paragraphs[0]
        p.text = title_text
        p.font.name = FONT_NAME
        p.font.size = Pt(28)
        p.font.bold = True
        p.font.color.rgb = COLOR_TITLE
        p.alignment = PP_ALIGN.LEFT
        return title_shape

    def clear_slide(slide):
        for s in list(slide.shapes):
            sp = s._element
            sp.getparent().remove(sp)

    def add_card(slide, left, top, width, height, title, items, title_color=COLOR_TITLE, title_size=15, body_size=11):
        card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
        card.fill.solid()
        card.fill.fore_color.rgb = COLOR_CARD_BG
        card.line.color.rgb = COLOR_BORDER
        card.line.width = Pt(1.1)

        tb = slide.shapes.add_textbox(left + Inches(0.18), top + Inches(0.14), width - Inches(0.36), height - Inches(0.28))
        tf = tb.text_frame
        tf.word_wrap = True
        tf.margin_top = Inches(0.04)
        tf.margin_left = Inches(0.04)
        tf.margin_right = Inches(0.04)
        tf.margin_bottom = Inches(0.04)

        if title:
            p_title = tf.paragraphs[0]
            p_title.text = title
            p_title.font.name = FONT_NAME
            p_title.font.size = Pt(title_size)
            p_title.font.bold = True
            p_title.font.color.rgb = title_color
            p_title.space_after = Pt(6)

        for i, item in enumerate(items):
            p = tf.add_paragraph() if (title or i > 0) else tf.paragraphs[0]
            p.font.name = FONT_NAME
            p.font.size = Pt(body_size)
            p.font.color.rgb = COLOR_BODY
            p.space_after = Pt(3)

            if isinstance(item, tuple):
                r1 = p.add_run()
                r1.text = "• " + item[0] + " "
                r1.font.bold = True
                r1.font.name = FONT_NAME
                r1.font.size = Pt(body_size)
                r1.font.color.rgb = COLOR_BODY

                r2 = p.add_run()
                r2.text = item[1]
                r2.font.bold = False
                r2.font.name = FONT_NAME
                r2.font.size = Pt(body_size)
                r2.font.color.rgb = COLOR_BODY
            else:
                p.text = "• " + item

        return card

    def add_fitted_picture(slide, img_path, box_left, box_top, box_w, box_h, add_frame=True):
        if not os.path.exists(img_path):
            print(f"Warning: Image not found {img_path}")
            return None

        with Image.open(img_path) as im:
            orig_w, orig_h = im.size
        aspect = orig_w / orig_h
        box_aspect = box_w / box_h

        if box_aspect > aspect:
            h = box_h
            w = int(h * aspect)
        else:
            w = box_w
            h = int(w / aspect)

        left = int(box_left + (box_w - w) / 2)
        top = int(box_top + (box_h - h) / 2)

        if add_frame:
            bg_card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, box_left, box_top, box_w, box_h)
            bg_card.fill.solid()
            bg_card.fill.fore_color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
            bg_card.line.color.rgb = COLOR_BORDER
            bg_card.line.width = Pt(1)

        pic = slide.shapes.add_picture(img_path, left, top, width=w, height=h)
        return pic

    # =========================================================================
    # SLIDE 1: COVER / QUALITY PLAN
    # =========================================================================
    s1 = slides[0]
    for s in s1.shapes:
        if s.name == 'Google Shape;110;p1':
            s.left = Inches(1.5)
            s.top = Inches(0.95)
            s.width = Inches(10.3)
            s.height = Inches(0.8)
            s.text_frame.paragraphs[0].text = 'QUALITY PLAN & UJI KOMPETENSI KEAHLIAN'
            s.text_frame.paragraphs[0].font.name = FONT_NAME
            s.text_frame.paragraphs[0].font.size = Pt(32)
            s.text_frame.paragraphs[0].font.bold = True
            s.text_frame.paragraphs[0].font.color.rgb = COLOR_TITLE
        elif s.name == 'Google Shape;111;p1':
            s.left = Inches(1.5)
            s.top = Inches(1.8)
            s.width = Inches(10.3)
            s.height = Inches(0.4)
            s.text_frame.paragraphs[0].text = 'Tahun Pembelajaran 2026-2027'
            s.text_frame.paragraphs[0].font.name = FONT_NAME
            s.text_frame.paragraphs[0].font.size = Pt(16)
        elif s.name == 'Google Shape;112;p1':
            s.left = Inches(1.8)
            s.top = Inches(2.35)
            s.width = Inches(9.5)
            s.height = Inches(3.3)
            tf = s.text_frame
            tf.clear()
            lines = [
                ('Nama\t\t: ', 'Jundi Faiq Mahbubillah'),
                ('NIS\t\t: ', '24183041'),
                ('Kelas\t\t: ', 'XII RPL (Rekayasa Perangkat Lunak)'),
                ('Paket Soal\t: ', 'Paket 2 (Pengembangan Aplikasi Parkir)'),
                ('Nama Produk\t: ', 'ParkSpot'),
                ('Judul Proyek\t: ', 'Pembuatan Aplikasi Parkir ParkSpot Berbasis Web dan Mobile sebagai Sistem Pengelolaan Parkir Terpadu'),
                ('Tema Proyek\t: ', 'Sistem Pengelolaan Parkir Cerdas dengan Penempatan Slot Terdekat Otomatis & Karcis Digital QR')
            ]
            for i, (label, val) in enumerate(lines):
                p = tf.add_paragraph() if i > 0 else tf.paragraphs[0]
                r1 = p.add_run()
                r1.text = label
                r1.font.name = FONT_NAME
                r1.font.size = Pt(13)
                r1.font.bold = True
                r1.font.color.rgb = COLOR_BODY

                r2 = p.add_run()
                r2.text = val
                r2.font.name = FONT_NAME
                r2.font.size = Pt(13)
                r2.font.bold = False
                r2.font.color.rgb = COLOR_BODY

    # =========================================================================
    # SLIDE 2: LATAR BELAKANG & RUMUSAN MASALAH
    # =========================================================================
    s2 = slides[1]
    clear_slide(s2)
    set_title(s2, "LATAR BELAKANG & MASALAH OPERASIONAL PARKIR")

    issues = [
        ("1. Pencarian Slot Manual", [
            ("Kendala di Lapangan:", "Pengendara harus berkeliling manual menyusuri area parkir untuk mencari tempat kosong."),
            ("Dampak Antrean:", "Memperlambat pergerakan kendaraan lain di belakang dan menimbulkan kemacetan di area pintu masuk."),
            ("Ketiadaan Informasi:", "Petugas gerbang tidak mengetahui secara pasti posisi slot kosong mana yang paling dekat.")
        ]),
        ("2. Keterbatasan Tiket Kertas Manual", [
            ("Karcis Konvensional:", "Karcis kertas biasa hanya mencatat jam masuk, rawan sobek, basah, atau hilang."),
            ("Tagihan Tidak Transparan:", "Pengemudi tidak dapat memantau argo biaya parkir berjalan sebelum tiba di loket kasir."),
            ("Penumpukan Kasir:", "Seluruh proses hitung durasi, tarif, dan pembayaran tunai terpusat di pintu keluar.")
        ]),
        ("3. Biaya Perangkat Keras Sensor IoT Mahal", [
            ("Sistem Komersial:", "Sistem parkir pintar komersial membutuhkan sensor ultrasonik & lampu LED pada setiap slot."),
            ("Biaya Investasi Tinggi:", "Pengadaan ratusan sensor fisik memerlukan anggaran puluhan juta rupiah dan perawatan rutin."),
            ("Kebutuhan ParkSpot:", "Dibutuhkan sistem alokasi slot cerdas berbasis software data denah tanpa sensor fisik.")
        ])
    ]

    col_w = Inches(3.55)
    gap = Inches(0.2)
    for i, (title, items) in enumerate(issues):
        c_left = Inches(1.2) + i * (col_w + gap)
        add_card(s2, c_left, Inches(1.65), col_w, Inches(4.9), title, items, title_color=COLOR_PRIMARY, body_size=11.5)

    # =========================================================================
    # SLIDE 3: TUJUAN & SASARAN PROYEK
    # =========================================================================
    s3 = slides[2]
    clear_slide(s3)
    set_title(s3, "A. TUJUAN & B. SASARAN PENGEMBANGAN")

    add_card(s3, Inches(1.2), Inches(1.65), Inches(5.35), Inches(4.9), 
             "A. TUJUAN PENGEMBANGAN SISTEM", [
                 ("Syarat Kelulusan UKK RPL:", "Memenuhi kriteria kompetensi Uji Kompetensi Keahlian Rekayasa Perangkat Lunak 2026-2027."),
                 ("Alokasi Slot Otomatis:", "Membangun sistem penempatan kendaraan otomatis ke slot terdekat berbasis data software."),
                 ("Karcis QR untuk Tamu (Guest):", "Menyediakan tiket ber-QR yang menghubungkan pengunjung ke web responsif tanpa perlu unduh aplikasi."),
                 ("Reservasi Slot Member:", "Mengembangkan fitur booking slot berbatas waktu kedatangan pada aplikasi mobile Flutter."),
                 ("Simulasi Pembayaran Non-Tunai:", "Mengintegrasikan simulasi QRIS Midtrans Sandbox untuk mempercepat alur transaksi keluar.")
             ], body_size=11.5)

    add_card(s3, Inches(6.85), Inches(1.65), Inches(5.35), Inches(4.9),
             "B. SASARAN PENGGUNA SISTEM", [
                 ("Petugas Pos Parkir:", "Mempermudah pelayanan input plat di gerbang masuk serta scanning tiket cepat di kasir keluar."),
                 ("Administrator:", "Menyediakan kontrol terpusat untuk data user, master tarif, pembagian area & titik slot, serta audit log."),
                 ("Owner / Pemilik Usaha:", "Memberikan transparansi laporan transaksi finansial, grafik pendapatan, dan persentase okupansi slot."),
                 ("Pelanggan Member:", "Kemudahan cek status slot aktif dan reservasi tempat parkir dari rumah."),
                 ("Pengunjung Tamu (Guest):", "Kemudahan memeriksa posisi denah blok kendaraan dan tagihan berjalan via browser smartphone.")
             ], body_size=11.5)

    # =========================================================================
    # SLIDE 4: PROSES BISNIS SISTEM YANG DIUSULKAN
    # =========================================================================
    s4 = slides[3]
    clear_slide(s4)
    set_title(s4, "PROSES BISNIS SISTEM YANG DIUSULKAN")

    biz_steps = [
        ("1. Pos Gerbang Masuk (Check-In)", [
            ("Input Plat & Jenis:", "Petugas memasukkan nomor plat dan memilih golongan kendaraan (Mobil/Motor)."),
            ("Penentuan Slot Terdekat:", "Sistem memeriksa slot kosong pada database dan mengalokasikan titik terdekat."),
            ("Cetak Tiket QR & Palang Buka:", "Karcis mini ber-QR dicetak otomatis, status slot menjadi Merah, palang masuk terbuka.")
        ]),
        ("2. Pengunjung Tamu (Guest Web)", [
            ("Scan QR Karcis Fisik:", "Pengunjung memindai kode QR pada tiket menggunakan kamera smartphone (tanpa instal aplikasi)."),
            ("Panduan Denah Lokasi:", "Halaman web menampilkan denah posisi blok parkir tempat kendaraan ditempatkan."),
            ("Cek Tagihan Berjalan:", "Menampilkan kalkulasi durasi parkir dan estimasi biaya berjalan secara transparan.")
        ]),
        ("3. Pelanggan Member (Mobile App)", [
            ("Cek Ketersediaan Slot:", "Member melihat denah visual ketersediaan slot sebelum menuju lokasi parkir."),
            ("Booking Berbatas Waktu:", "Melakukan reservasi slot; status slot berubah menjadi Oranye (Booking)."),
            ("Validasi Kedatangan:", "Sistem mencatat batas toleransi kedatangan agar slot tidak tertahan tanpa kepastian.")
        ]),
        ("4. Pos Kasir Keluar (Check-Out)", [
            ("Scan Tiket Parkir:", "Petugas memindai kode QR karcis fisik atau QR pada aplikasi mobile member."),
            ("Perhitungan Tarif:", "Sistem membaca selisih waktu dan menghitung tarif akhir secara otomatis."),
            ("Pilihan Pembayaran:", "Pembayaran dapat dilakukan tunai di kasir atau simulasi non-tunai QRIS Midtrans. Slot kembali Hijau.")
        ])
    ]

    coords_4 = [
        (Inches(1.2), Inches(1.65), Inches(5.35), Inches(2.4)),
        (Inches(6.85), Inches(1.65), Inches(5.35), Inches(2.4)),
        (Inches(1.2), Inches(4.18), Inches(5.35), Inches(2.4)),
        (Inches(6.85), Inches(4.18), Inches(5.35), Inches(2.4))
    ]

    for (title, items), (c_left, c_top, c_w, c_h) in zip(biz_steps, coords_4):
        add_card(s4, c_left, c_top, c_w, c_h, title, items, body_size=11)

    # =========================================================================
    # SLIDE 5: TEKNOLOGI & PERAN TEKNIS DALAM SISTEM
    # =========================================================================
    s5 = slides[4]
    clear_slide(s5)
    set_title(s5, "TEKNOLOGI & PERAN TEKNIS DALAM PENGEMBANGAN")

    # 5 cards with generated tech badges
    tech_data = [
        ('laravel.png', "Laravel 11 & PHP", [
            ("Backend & REST API:", "Menyediakan endpoint API untuk transaksi pos dan aplikasi mobile."),
            ("Web Management:", "Membangun dashboard operasional Admin, Petugas, dan Owner."),
            ("Keamanan Sistem:", "Penerapan autentikasi multi-role, hashing password, dan proteksi CSRF.")
        ]),
        ('flutter.png', "Flutter & Dart", [
            ("Aplikasi Mobile Member:", "Membangun antarmuka mobile untuk pelanggan tetap."),
            ("Performa Cross-Platform:", "Satu basis kode untuk dijalankan di sistem operasi Android & iOS."),
            ("Interaksi Reaktif:", "Pembaruan tampilan status parkir secara langsung tanpa lag antarmuka.")
        ]),
        ('firebase.png', "Firebase Realtime Database", [
            ("Penyimpanan NoSQL Cloud:", "Menyimpan data master slot, pengguna, tarif, dan log transaksi."),
            ("Sinkronisasi Realtime:", "Pembaruan status slot dari Web Pos seketika terbaca di aplikasi mobile."),
            ("Efisiensi Query:", "Format JSON tree yang ringan untuk akses data cepat pada gerbang parkir.")
        ]),
        ('tailwind.png', "Tailwind CSS", [
            ("Penataan UI Web:", "Menghasilkan tampilan dashboard web yang rapi, responsif, dan konsisten."),
            ("Format Cetak Karcis:", "Mengatur layout tiket termal gerbang masuk dan struk bukti bayar kasir.")
        ]),
        ('midtrans.png', "Midtrans Sandbox", [
            ("Simulasi Payment Gateway:", "Menguji coba alur pembayaran non-tunai menggunakan kode QRIS."),
            ("Validasi Transaksi:", "Memverifikasi status pelunasan transaksi sebelum palang keluar dibuka.")
        ])
    ]

    # Row 1: 3 cards
    w_3 = Inches(3.55)
    gap_3 = Inches(0.2)
    for i in range(3):
        b_img, b_title, b_items = tech_data[i]
        c_left = Inches(1.2) + i * (w_3 + gap_3)
        # Add badge image at top of card
        badge_path = os.path.join(badge_dir, b_img)
        if os.path.exists(badge_path):
            s5.shapes.add_picture(badge_path, c_left + Inches(0.18), Inches(1.68), width=Inches(3.18))
        # Add card below badge
        add_card(s5, c_left, Inches(2.7), w_3, Inches(3.85), "", b_items, body_size=11)

    # Row 2: 2 cards centered
    w_2 = Inches(5.35)
    gap_2 = Inches(0.3)
    for i in range(2):
        b_img, b_title, b_items = tech_data[3 + i]
        c_left = Inches(1.2) + i * (w_2 + gap_2)
        badge_path = os.path.join(badge_dir, b_img)
        # Position card on right
        # We can adjust: Let's do a clean 5-column or 3-top / 2-bottom
    # Wait, let's make a clean layout for the 5 technologies:
    # Card 1 (Laravel), Card 2 (Flutter), Card 3 (Firebase) at top (height=2.35)
    # Card 4 (Tailwind), Card 5 (Midtrans) at bottom (height=2.35)
    clear_slide(s5)
    set_title(s5, "TEKNOLOGI & PERAN TEKNIS DALAM PENGEMBANGAN")

    # 3 at top
    for i in range(3):
        b_img, b_title, b_items = tech_data[i]
        c_left = Inches(1.2) + i * (w_3 + gap_3)
        badge_path = os.path.join(badge_dir, b_img)
        if os.path.exists(badge_path):
            s5.shapes.add_picture(badge_path, c_left, Inches(1.65), width=Inches(3.55), height=Inches(0.85))
        add_card(s5, c_left, Inches(2.55), w_3, Inches(1.95), "", b_items, body_size=10.5)

    # 2 at bottom
    w_bottom = Inches(5.35)
    for i in range(2):
        b_img, b_title, b_items = tech_data[3 + i]
        c_left = Inches(1.2) + i * (w_bottom + Inches(0.3))
        badge_path = os.path.join(badge_dir, b_img)
        if os.path.exists(badge_path):
            s5.shapes.add_picture(badge_path, c_left, Inches(4.6), width=Inches(5.35), height=Inches(0.85))
        add_card(s5, c_left, Inches(5.5), w_bottom, Inches(1.25), "", b_items, body_size=11)

    # =========================================================================
    # SLIDE 6: ANALISIS PENDEKATAN SISTEM (KOMPETITOR)
    # =========================================================================
    s6 = slides[5]
    clear_slide(s6)
    set_title(s6, "ANALISIS PENDEKATAN SISTEM & KOMPETITOR")

    comp_approaches = [
        ("1. Sistem Parkir Konvensional", [
            ("Pencarian Slot:", "Pengemudi mencari tempat kosong secara manual; tidak ada panduan visual lokasi."),
            ("Pencatatan Masuk:", "Karcis kertas biasa tanpa informasi tagihan berjalan; rawan hilang atau terselip."),
            ("Pembayaran:", "Hanya menerima tunai di pintu keluar sehingga memicu antrean kendaraan saat jam sibuk."),
            ("Keterbatasan:", "Tidak ada sinkronisasi ketersediaan kapasitas parkir ke perangkat pengguna.")
        ]),
        ("2. Sistem Komersial (Smart Parking)", [
            ("Infrastruktur Fisik:", "Menggunakan sensor ultrasonik dan lampu indikator per-slot yang berbiaya tinggi."),
            ("Akses Pengguna:", "Mewajibkan pengendara mengunduh aplikasi penuh atau memiliki kartu elektronik khusus."),
            ("Tamu Harian:", "Pengunjung jarang datang enggan memasang aplikasi hanya untuk sekali kunjungan."),
            ("Ketergantungan Hardware:", "Biaya pemeliharaan tinggi jika sensor fisik mengalami kerusakan.")
        ]),
        ("3. Pendekatan Solusi ParkSpot", [
            ("Alokasi Berbasis Software:", "Sistem otomatis menugaskan slot kosong terdekat via data denah tanpa sensor IoT."),
            ("Karcis QR untuk Tamu:", "Pengunjung cukup memindai QR tiket fisik untuk membuka web tracking tanpa install aplikasi."),
            ("Fitur Reservasi Member:", "Pengguna aplikasi mobile dapat memesan slot parkir berbatas toleransi waktu."),
            ("Simulasi Bayar QRIS:", "Mendukung simulasi pembayaran non-tunai pre-exit untuk memangkas antrean loket keluar.")
        ])
    ]

    for i, (title, items) in enumerate(comp_approaches):
        c_left = Inches(1.2) + i * (col_w + gap)
        t_color = COLOR_TITLE if i == 2 else COLOR_PRIMARY
        add_card(s6, c_left, Inches(1.65), col_w, Inches(4.9), title, items, title_color=t_color, body_size=11.5)

    # =========================================================================
    # SLIDE 7: PERANCANGAN: USE CASE DIAGRAM
    # =========================================================================
    s7 = slides[6]
    clear_slide(s7)
    set_title(s7, "PERANCANGAN: USE CASE DIAGRAM")

    img_usecase = os.path.join(diagram_dir, 'Diagram ParkSpot UKK-Use Case.drawio.png')
    # Large diagram on left: width = 6.2 inches, height = 4.9 inches
    add_fitted_picture(s7, img_usecase, Inches(1.2), Inches(1.65), Inches(6.2), Inches(4.9))

    add_card(s7, Inches(7.6), Inches(1.65), Inches(4.5), Inches(4.9),
             "PEMBAGIAN HAK AKSES SISTEM", [
                 ("Administrator (Web):", "Mengelola master akun user, data kendaraan, tarif per jenis kendaraan, master area/slot, serta akses log aktivitas."),
                 ("Petugas Pos (Web):", "Melakukan check-in gerbang masuk (alokasi slot otomatis & cetak karcis) dan checkout kasir keluar (scan tiket & terima pembayaran)."),
                 ("Owner Gedung (Web):", "Memantau dashboard statistik okupansi slot, grafik pendapatan, dan mengunduh laporan rekapitulasi transaksi."),
                 ("Pelanggan Member (Mobile):", "Pendaftaran akun/kendaraan, booking slot parkir berbatas toleransi waktu, dan simulasi pembayaran QRIS."),
                 ("Pengunjung Tamu (Guest Web):", "Scan QR karcis fisik via browser untuk memantau letak posisi kendaraan dan cek estimasi tagihan berjalan.")
             ], body_size=11.5)

    # =========================================================================
    # SLIDE 8: PERANCANGAN: ACTIVITY DIAGRAM (CORE PROCESS)
    # =========================================================================
    s8 = slides[7]
    clear_slide(s8)
    set_title(s8, "ACTIVITY DIAGRAM: TRANSAKSI MASUK & KELUAR (CORE)")

    img_act_masuk = os.path.join(diagram_dir, 'Diagram ParkSpot UKK-activity kendaraan masuk.drawio.png')
    img_act_keluar = os.path.join(diagram_dir, 'Diagram ParkSpot UKK-Activity Keluar gerbang & Print Struk.drawio.png')

    # Left Box: Transaksi Masuk (Landscape diagram top, steps bottom)
    card_left = slide_shape = s8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(1.2), Inches(1.65), Inches(5.35), Inches(4.9))
    card_left.fill.solid()
    card_left.fill.fore_color.rgb = COLOR_CARD_BG
    card_left.line.color.rgb = COLOR_BORDER

    # Title inside left box
    tb_l = s8.shapes.add_textbox(Inches(1.4), Inches(1.75), Inches(4.95), Inches(0.4))
    tb_l.text_frame.word_wrap = True
    tb_l.text_frame.paragraphs[0].text = "1. Alur Transaksi Gerbang Masuk"
    tb_l.text_frame.paragraphs[0].font.name = FONT_NAME
    tb_l.text_frame.paragraphs[0].font.size = Pt(14)
    tb_l.text_frame.paragraphs[0].font.bold = True
    tb_l.text_frame.paragraphs[0].font.color.rgb = COLOR_TITLE

    # Diagram Masuk
    add_fitted_picture(s8, img_act_masuk, Inches(1.4), Inches(2.15), Inches(4.95), Inches(2.2), add_frame=False)

    # Bullets Masuk below
    tb_l_steps = s8.shapes.add_textbox(Inches(1.4), Inches(4.45), Inches(4.95), Inches(1.9))
    tf_l = tb_l_steps.text_frame
    tf_l.word_wrap = True
    steps_masuk = [
        ("Input Plat & Jenis:", "Petugas memasukkan nomor plat & tipe kendaraan di Web Pos."),
        ("Alokasi Terdekat:", "Sistem otomatis memilih slot kosong terdekat berbasis denah."),
        ("Kunci Slot & Palang Buka:", "Status slot jadi 'Terisi' (Merah), cetak karcis QR & palang buka.")
    ]
    for i, (b_lead, b_txt) in enumerate(steps_masuk):
        p = tf_l.add_paragraph() if i > 0 else tf_l.paragraphs[0]
        p.font.name = FONT_NAME
        p.font.size = Pt(10.5)
        p.space_after = Pt(2.5)
        r1 = p.add_run(); r1.text = "• " + b_lead + " "; r1.font.bold = True; r1.font.color.rgb = COLOR_BODY
        r2 = p.add_run(); r2.text = b_txt; r2.font.bold = False; r2.font.color.rgb = COLOR_BODY

    # Right Box: Transaksi Keluar (Portrait diagram + steps side-by-side)
    card_right = s8.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, Inches(6.85), Inches(1.65), Inches(5.35), Inches(4.9))
    card_right.fill.solid()
    card_right.fill.fore_color.rgb = COLOR_CARD_BG
    card_right.line.color.rgb = COLOR_BORDER

    tb_r = s8.shapes.add_textbox(Inches(7.05), Inches(1.75), Inches(4.95), Inches(0.4))
    tb_r.text_frame.word_wrap = True
    tb_r.text_frame.paragraphs[0].text = "2. Alur Transaksi Kasir Keluar"
    tb_r.text_frame.paragraphs[0].font.name = FONT_NAME
    tb_r.text_frame.paragraphs[0].font.size = Pt(14)
    tb_r.text_frame.paragraphs[0].font.bold = True
    tb_r.text_frame.paragraphs[0].font.color.rgb = COLOR_TITLE

    # Diagram Keluar (Portrait, placed on left half of right card)
    add_fitted_picture(s8, img_act_keluar, Inches(7.05), Inches(2.15), Inches(2.2), Inches(4.2), add_frame=False)

    # Bullets Keluar on right half of right card
    tb_r_steps = s8.shapes.add_textbox(Inches(9.35), Inches(2.15), Inches(2.7), Inches(4.2))
    tf_r = tb_r_steps.text_frame
    tf_r.word_wrap = True
    steps_keluar = [
        ("Scan Tiket QR:", "Petugas scan kode karcis fisik / QR aplikasi member."),
        ("Hitung Biaya Otomatis:", "Sistem membaca selisih jam masuk dan tarif golongan."),
        ("Opsi Pembayaran:", "Pengendara bayar via Tunai di pos atau simulasi QRIS Midtrans."),
        ("Penyelesaian Transaksi:", "Status slot dikembalikan jadi 'Kosong' (Hijau), cetak struk lunas & palang keluar terbuka.")
    ]
    for i, (b_lead, b_txt) in enumerate(steps_keluar):
        p = tf_r.add_paragraph() if i > 0 else tf_r.paragraphs[0]
        p.font.name = FONT_NAME
        p.font.size = Pt(10.5)
        p.space_after = Pt(3.5)
        r1 = p.add_run(); r1.text = "• " + b_lead + " "; r1.font.bold = True; r1.font.color.rgb = COLOR_BODY
        r2 = p.add_run(); r2.text = b_txt; r2.font.bold = False; r2.font.color.rgb = COLOR_BODY

    # =========================================================================
    # SLIDE 9: PERANCANGAN: STRUKTUR DATABASE (ERD)
    # =========================================================================
    s9 = slides[8]
    clear_slide(s9)
    set_title(s9, "PERANCANGAN: STRUKTUR DATABASE (ERD)")

    img_erd = os.path.join(diagram_dir, 'Diagram ParkSpot UKK-ERD.drawio.png')
    # Massive landscape display area for ERD: width = 6.8 inches
    add_fitted_picture(s9, img_erd, Inches(1.2), Inches(1.65), Inches(6.8), Inches(4.9))

    add_card(s9, Inches(8.2), Inches(1.65), Inches(3.9), Inches(4.9),
             "STRUKTUR DATA FIREBASE", [
                 ("Node `users`:", "Menyimpan data akun, username, password terenkripsi, role (Admin, Petugas, Owner, Member), dan nomor HP."),
                 ("Node `areas` & `slots`:", "Hierarki area (lantai/blok) serta status titik slot (available, booked, occupied)."),
                 ("Node `vehicles`:", "Data kendaraan terdaftar milik pelanggan member (plat, merk, tipe)."),
                 ("Node `rates`:", "Konfigurasi tarif dasar jam pertama dan jam berikutnya per jenis kendaraan."),
                 ("Node `parkings`:", "Tabel transaksi parkir aktif & riwayat (kode tiket QR, waktu masuk/keluar, biaya, status lunas)."),
                 ("Node `activity_logs`:", "Audit trail pencatatan aktivitas pengguna sistem untuk keamanan dan pemantauan.")
             ], body_size=11)

    # =========================================================================
    # SLIDE 10: IMPLEMENTASI ANTARMUKA: WEB PETUGAS & ADMIN
    # =========================================================================
    s10 = slides[9]
    clear_slide(s10)
    set_title(s10, "IMPLEMENTASI ANTARMUKA: WEB PETUGAS & ADMIN")

    img_pos_masuk = os.path.join(wireframe_dir, 'petugas_gate_in.png')
    img_denah_slot = os.path.join(wireframe_dir, 'admin_denah_slot.png')

    # Left: Pos Masuk UI (Width = 5.35 inches)
    add_fitted_picture(s10, img_pos_masuk, Inches(1.2), Inches(1.65), Inches(5.35), Inches(3.9))
    tb_cap1 = s10.shapes.add_textbox(Inches(1.2), Inches(5.65), Inches(5.35), Inches(0.9))
    tf_c1 = tb_cap1.text_frame
    tf_c1.word_wrap = True
    p1 = tf_c1.paragraphs[0]; p1.text = "Antarmuka Pos Gerbang Masuk & Kasir (Petugas)"
    p1.font.name = FONT_NAME; p1.font.size = Pt(13); p1.font.bold = True; p1.font.color.rgb = COLOR_TITLE
    p2 = tf_c1.add_paragraph()
    p2.text = "Form entri cepat nomor plat dengan tombol alokasi slot terdekat instan dan cetak tiket QR."
    p2.font.name = FONT_NAME; p2.font.size = Pt(11); p2.font.color.rgb = COLOR_BODY

    # Right: Denah 3 Warna UI (Width = 5.35 inches)
    add_fitted_picture(s10, img_denah_slot, Inches(6.85), Inches(1.65), Inches(5.35), Inches(3.9))
    tb_cap2 = s10.shapes.add_textbox(Inches(6.85), Inches(5.65), Inches(5.35), Inches(0.9))
    tf_c2 = tb_cap2.text_frame
    tf_c2.word_wrap = True
    p3 = tf_c2.paragraphs[0]; p3.text = "Visualisasi Denah Slot 3 Warna (Admin & Petugas)"
    p3.font.name = FONT_NAME; p3.font.size = Pt(13); p3.font.bold = True; p3.font.color.rgb = COLOR_TITLE
    p4 = tf_c2.add_paragraph()
    p4.text = "Pemetaan kondisi lapangan realtime: Hijau (Kosong), Kuning (Booking Member), Merah (Terisi)."
    p4.font.name = FONT_NAME; p4.font.size = Pt(11); p4.font.color.rgb = COLOR_BODY

    # =========================================================================
    # SLIDE 11: IMPLEMENTASI ANTARMUKA: MOBILE MEMBER & GUEST WEB
    # =========================================================================
    s11 = slides[10]
    clear_slide(s11)
    set_title(s11, "IMPLEMENTASI ANTARMUKA: MOBILE MEMBER & GUEST WEB")

    img_mob_home = os.path.join(wireframe_dir, 'member_beranda.png')
    img_mob_qris = os.path.join(wireframe_dir, 'member_qris.png')
    img_guest_web = os.path.join(wireframe_dir, 'guest_karcis_digital.png')

    # 3 Mobile screens side-by-side (portrait)
    w_phone = Inches(2.4)
    h_phone = Inches(4.15)
    gap_phone = Inches(0.2)

    screens = [
        (img_mob_home, "Beranda Member (Mobile)", "Kartu status parkir aktif dan durasi waktu berjalan."),
        (img_mob_qris, "Simulasi Bayar QRIS (Mobile)", "Pelunasan tarif non-tunai via Midtrans Sandbox."),
        (img_guest_web, "Karcis Digital Tamu (Web)", "Terbuka dari scan QR tiket fisik tanpa install app.")
    ]

    for i, (s_path, s_title, s_desc) in enumerate(screens):
        pos_left = Inches(1.2) + i * (w_phone + gap_phone)
        add_fitted_picture(s11, s_path, pos_left, Inches(1.65), w_phone, h_phone)

        tb_s = s11.shapes.add_textbox(pos_left, Inches(5.85), w_phone, Inches(0.9))
        tf_s = tb_s.text_frame
        tf_s.word_wrap = True
        ps1 = tf_s.paragraphs[0]; ps1.text = s_title
        ps1.font.name = FONT_NAME; ps1.font.size = Pt(11); ps1.font.bold = True; ps1.font.color.rgb = COLOR_TITLE
        ps2 = tf_s.add_paragraph(); ps2.text = s_desc
        ps2.font.name = FONT_NAME; ps2.font.size = Pt(9.5); ps2.font.color.rgb = COLOR_BODY

    # Right Card: Explanation of experience
    add_card(s11, Inches(8.9), Inches(1.65), Inches(3.2), Inches(4.9),
             "PENGALAMAN PENGGUNA", [
                 ("Aplikasi Member:", "Pelanggan tetap dapat mereservasi slot parkir sebelum tiba di lokasi dan melakukan pembayaran mandiri sebelum menuju kendaraan."),
                 ("Web Tamu Fleksibel:", "Pengunjung non-aplikasi tetap mendapatkan kemudahan digital; cukup scan kamera HP ke karcis fisik untuk memantau denah lantai dan argo tagihan."),
                 ("Tanpa Beban Unduhan:", "Pengunjung tidak dipaksa mengunduh aplikasi berukuran besar hanya untuk parkir beberapa jam.")
             ], body_size=11)

    # =========================================================================
    # SLIDE 12: HASIL PENGUJIAN SISTEM (BLACK BOX TESTING)
    # =========================================================================
    s12 = slides[11]
    clear_slide(s12)
    set_title(s12, "HASIL PENGUJIAN SISTEM (BLACK BOX TESTING)")

    # Create clean professional table
    rows, cols = 7, 4
    t_left = Inches(1.2)
    t_top = Inches(1.65)
    t_width = Inches(11.0)
    t_height = Inches(4.8)

    table_shape = s12.shapes.add_table(rows, cols, t_left, t_top, t_width, t_height)
    tbl = table_shape.table

    # Column widths
    tbl.columns[0].width = Inches(1.1)   # Kode Uji
    tbl.columns[1].width = Inches(3.2)   # Fitur & Skenario
    tbl.columns[2].width = Inches(5.3)   # Hasil yang Diharapkan & Diuji
    tbl.columns[3].width = Inches(1.4)   # Status

    test_cases = [
        ("KODE", "SKENARIO & FITUR YANG DIUJI", "HASIL PENGUJIAN YANG DIAMATI", "STATUS"),
        ("UJI-01", "Autentikasi Multi-Role (Admin, Petugas, Owner, Member)", "Pengguna berhasil login sesuai hak akses masing-masing; menu terfilter dan tercatat di audit log.", "BERHASIL"),
        ("UJI-02", "Input Plat Masuk & Alokasi Slot Terdekat di Pos", "Sistem berhasil mengalokasikan slot kosong terdekat, mengubah indikator jadi Merah, dan mencetak tiket QR.", "BERHASIL"),
        ("UJI-03", "Akses Halaman Web Tamu via QR Karcis Fisik", "Halaman web mobile terbuka menampilkan lokasi denah lantai kendaraan dan kalkulasi tagihan berjalan realtime.", "BERHASIL"),
        ("UJI-04", "Pemesanan (Booking) Tempat Parkir oleh Member", "Slot berubah status menjadi Oranye (Booking) di denah, dan batas toleransi kedatangan terhitung otomatis.", "BERHASIL"),
        ("UJI-05", "Checkout di Kasir Keluar & Simulasi Bayar QRIS", "Durasi parkir dan tarif terhitung akurat; simulasi QRIS Midtrans berhasil verifikasi status lunas, palang terbuka.", "BERHASIL"),
        ("UJI-06", "Rekapitulasi Laporan Finansial oleh Owner", "Seluruh transaksi masuk dan keluar tercatat otomatis pada grafik omzet dan dapat diekspor tanpa selisih data.", "BERHASIL")
    ]

    for r_idx, row_data in enumerate(test_cases):
        for c_idx, val in enumerate(row_data):
            cell = tbl.cell(r_idx, c_idx)
            cell.text = val
            p = cell.text_frame.paragraphs[0]
            p.font.name = FONT_NAME
            cell.vertical_anchor = MSO_ANCHOR.MIDDLE

            if r_idx == 0:
                p.font.size = Pt(11)
                p.font.bold = True
                p.font.color.rgb = RGBColor(0xFF, 0xFF, 0xFF)
                cell.fill.solid()
                cell.fill.fore_color.rgb = COLOR_PRIMARY
                p.alignment = PP_ALIGN.CENTER if c_idx in [0, 3] else PP_ALIGN.LEFT
            else:
                p.font.size = Pt(10)
                cell.fill.solid()
                cell.fill.fore_color.rgb = RGBColor(0xFF, 0xFF, 0xFF) if r_idx % 2 == 1 else RGBColor(0xF8, 0xFA, 0xFC)
                if c_idx == 3:
                    p.font.bold = True
                    p.font.color.rgb = COLOR_SUCCESS
                    p.alignment = PP_ALIGN.CENTER
                elif c_idx == 0:
                    p.font.bold = True
                    p.font.color.rgb = COLOR_PRIMARY
                    p.alignment = PP_ALIGN.CENTER
                else:
                    p.font.color.rgb = COLOR_BODY
                    p.alignment = PP_ALIGN.LEFT

    # =========================================================================
    # SLIDE 13: KESIMPULAN HASIL PROJEK
    # =========================================================================
    s13 = slides[12]
    clear_slide(s13)
    set_title(s13, "KESIMPULAN HASIL PENGEMBANGAN PROJEK")

    conclusions = [
        ("1. Keberhasilan Implementasi Sistem", [
            ("Integrasi Web & Mobile:", "Aplikasi ParkSpot berhasil dibangun mengintegrasikan backend Laravel 11, mobile Flutter, dan database realtime Firebase."),
            ("Alokasi Slot Otomatis:", "Algoritma penentuan slot terdekat terbukti dapat mengarahkan kendaraan secara otomatis melalui perangkat lunak tanpa perlu sensor fisik bernilai mahal.")
        ]),
        ("2. Pemecahan Masalah Antrean & Akses", [
            ("Akses Universal Tamu:", "Pengunjung non-aplikasi (guest) tetap mendapatkan kemudahan digital memantau letak parkir dan tarif berjalan cukup lewat scan QR karcis."),
            ("Efisiensi Check-Out:", "Tersedianya opsi simulasi pembayaran QRIS dan penghitungan tarif otomatis berhasil mempercepat proses transaksi di pos keluar.")
        ]),
        ("3. Transparansi & Akuntabilitas Pengelolaan", [
            ("Keamanan Multi-Role:", "Sistem hak akses memisahkan wewenang antara Admin, Petugas loket, dan Owner secara tertib."),
            ("Laporan Finansial Realtime:", "Pencatatan pendapatan terotomatisasi penuh, meminimalisir human-error, serta mempermudah pengawasan pemilik gedung.")
        ])
    ]

    for i, (title, items) in enumerate(conclusions):
        c_left = Inches(1.2) + i * (col_w + gap)
        add_card(s13, c_left, Inches(1.65), col_w, Inches(4.9), title, items, title_color=COLOR_TITLE, body_size=11.5)

    # =========================================================================
    # SLIDE 14: TERIMA KASIH & TANYA JAWAB
    # =========================================================================
    s14 = slides[13]
    for s in s14.shapes:
        if s.name == 'Google Shape;182;p14':
            s.text_frame.paragraphs[0].text = 'TERIMA KASIH'
            s.text_frame.paragraphs[0].font.name = FONT_NAME
            s.text_frame.paragraphs[0].font.size = Pt(40)
            s.text_frame.paragraphs[0].font.bold = True
            s.text_frame.paragraphs[0].font.color.rgb = COLOR_TITLE
        elif s.name == 'Google Shape;183;p14':
            s.text_frame.paragraphs[0].text = 'Sesi Tanya Jawab, Saran & Masukan Penguji'
            s.text_frame.paragraphs[0].font.name = FONT_NAME
            s.text_frame.paragraphs[0].font.size = Pt(18)
            s.text_frame.paragraphs[0].font.bold = True
            s.text_frame.paragraphs[0].font.color.rgb = COLOR_PRIMARY

    prs.save(output_path)
    print(f"Refined presentation successfully generated at: {output_path}")

    try:
        prs.save(output_path_alt)
        print(f"Also updated: {output_path_alt}")
    except PermissionError:
        print(f"Note: {output_path_alt} is currently open in PowerPoint, so changes are saved to: {output_path}")

if __name__ == '__main__':
    generate_deck()
