from pathlib import Path

from PIL import Image
from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_CONNECTOR, MSO_SHAPE
from pptx.enum.text import MSO_ANCHOR, PP_ALIGN
from pptx.util import Inches, Pt


ROOT = Path(__file__).resolve().parent
TEMPLATE = ROOT / "TEMPLATE PPT QP UKK 2026-2027.pptx"
OUTPUT = ROOT / "ParkSpot - Presentasi UKK 2026-2027 - Revisi Terstruktur.pptx"
DIAGRAMS = ROOT / "diagram"
WIREFRAMES = ROOT / "wireframes"

MAGENTA = RGBColor(0xB6, 0x0D, 0x79)
NAVY = RGBColor(0x34, 0x3E, 0x4D)
BODY = RGBColor(0x3F, 0x45, 0x50)
MUTED = RGBColor(0x68, 0x70, 0x7C)
PALE = RGBColor(0xF5, 0xF6, 0xF9)
PALE_PINK = RGBColor(0xFB, 0xF4, 0xF8)
BORDER = RGBColor(0xD9, 0xDD, 0xE3)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
GREEN = RGBColor(0x41, 0x8B, 0x67)
ORANGE = RGBColor(0xD4, 0x8A, 0x2F)
RED = RGBColor(0xB7, 0x4C, 0x59)
FONT = "Arial"


def set_text(shape, text, size, color=BODY, bold=False, align=PP_ALIGN.LEFT):
    frame = shape.text_frame
    frame.clear()
    frame.word_wrap = True
    frame.vertical_anchor = MSO_ANCHOR.MIDDLE
    paragraph = frame.paragraphs[0]
    paragraph.text = text
    paragraph.alignment = align
    paragraph.font.name = FONT
    paragraph.font.size = Pt(size)
    paragraph.font.bold = bold
    paragraph.font.color.rgb = color
    return shape


def add_text(
    slide, text, left, top, width, height, size=24, color=BODY,
    bold=False, align=PP_ALIGN.LEFT, margins=0.04
):
    shape = slide.shapes.add_textbox(left, top, width, height)
    shape.text_frame.margin_left = Inches(margins)
    shape.text_frame.margin_right = Inches(margins)
    shape.text_frame.margin_top = Inches(margins / 2)
    shape.text_frame.margin_bottom = Inches(margins / 2)
    return set_text(shape, text, size, color, bold, align)


def add_box(
    slide, left, top, width, height, title, detail=None, *,
    fill=PALE, accent=MAGENTA, title_size=28, detail_size=24,
    title_color=NAVY, detail_color=BODY, border=BORDER, align=PP_ALIGN.LEFT
):
    shape = slide.shapes.add_shape(
        MSO_SHAPE.RECTANGLE, left, top, width, height
    )
    shape.fill.solid()
    shape.fill.fore_color.rgb = fill
    shape.line.color.rgb = border
    shape.line.width = Pt(1.1)

    marker = slide.shapes.add_shape(
        MSO_SHAPE.RECTANGLE, left, top, Inches(0.09), height
    )
    marker.fill.solid()
    marker.fill.fore_color.rgb = accent
    marker.line.fill.background()

    inset = Inches(0.2)
    text_box = slide.shapes.add_textbox(
        left + inset, top + Inches(0.08),
        width - inset - Inches(0.08), height - Inches(0.16)
    )
    frame = text_box.text_frame
    frame.clear()
    frame.word_wrap = True
    frame.vertical_anchor = MSO_ANCHOR.MIDDLE
    frame.margin_left = Inches(0.02)
    frame.margin_right = Inches(0.02)
    frame.margin_top = Inches(0.01)
    frame.margin_bottom = Inches(0.01)

    heading = frame.paragraphs[0]
    heading.text = title
    heading.alignment = align
    heading.font.name = FONT
    heading.font.size = Pt(title_size)
    heading.font.bold = True
    heading.font.color.rgb = title_color
    if detail:
        paragraph = frame.add_paragraph()
        paragraph.text = detail
        paragraph.alignment = align
        paragraph.font.name = FONT
        paragraph.font.size = Pt(detail_size)
        paragraph.font.color.rgb = detail_color
        paragraph.space_before = Pt(5)
    return shape


def add_arrow(slide, left, top, width=0.42, height=0.36):
    return add_text(
        slide, "›", left, top, width, height, size=32,
        color=MAGENTA, bold=True, align=PP_ALIGN.CENTER
    )


def add_dot(slide, left, top, color=MAGENTA):
    dot = slide.shapes.add_shape(
        MSO_SHAPE.OVAL, left, top, Inches(0.16), Inches(0.16)
    )
    dot.fill.solid()
    dot.fill.fore_color.rgb = color
    dot.line.fill.background()
    return dot


def add_line(slide, x1, y1, x2, y2, color=MAGENTA, width=2):
    connector = slide.shapes.add_connector(
        MSO_CONNECTOR.STRAIGHT, x1, y1, x2, y2
    )
    connector.line.color.rgb = color
    connector.line.width = Pt(width)
    return connector


def add_image_fit(slide, image_path, left, top, width, height):
    image_path = Path(image_path)
    if not image_path.is_file():
        raise FileNotFoundError(f"Presentation asset not found: {image_path}")
    with Image.open(image_path) as image:
        aspect = image.width / image.height
    box_aspect = width / height
    if aspect > box_aspect:
        fitted_width = width
        fitted_height = int(width / aspect)
    else:
        fitted_height = height
        fitted_width = int(height * aspect)
    x = int(left + (width - fitted_width) / 2)
    y = int(top + (height - fitted_height) / 2)
    return slide.shapes.add_picture(
        str(image_path), x, y, width=fitted_width, height=fitted_height
    )


def clear_slide(slide):
    for shape in list(slide.shapes):
        shape._element.getparent().remove(shape._element)


def add_heading(slide, title, section="PARKSPOT  /  UKK PAKET 2"):
    add_text(
        slide, section, Inches(1.15), Inches(0.28), Inches(11.3),
        Inches(0.28), size=14, color=MUTED, bold=True
    )
    add_text(
        slide, title, Inches(1.15), Inches(0.67), Inches(11.3),
        Inches(0.65), size=32, color=MAGENTA, bold=True
    )
    bar = slide.shapes.add_shape(
        MSO_SHAPE.RECTANGLE, Inches(1.15), Inches(1.42),
        Inches(0.78), Inches(0.065)
    )
    bar.fill.solid()
    bar.fill.fore_color.rgb = MAGENTA
    bar.line.fill.background()


def prepare_slide(slide, title, section="PARKSPOT  /  UKK PAKET 2"):
    clear_slide(slide)
    add_heading(slide, title, section)


def add_step(slide, number, title, detail, x, y, width, height, accent=MAGENTA):
    add_box(
        slide, x, y, width, height, title, detail,
        fill=WHITE, accent=accent, title_size=26, detail_size=22
    )
    add_text(
        slide, number, x + Inches(0.12), y - Inches(0.38),
        Inches(0.48), Inches(0.32), size=18, color=accent, bold=True
    )


def add_footer_note(slide, text, y=6.7):
    add_text(
        slide, text, Inches(1.15), Inches(y), Inches(11.1),
        Inches(0.42), size=24, color=MUTED, align=PP_ALIGN.CENTER
    )


def add_full_diagram(slide, title, filename, caption):
    prepare_slide(slide, title, section="LAMPIRAN  /  DIAGRAM PROYEK")
    add_image_fit(
        slide, DIAGRAMS / filename,
        Inches(1.15), Inches(1.62), Inches(11.25), Inches(5.2)
    )
    add_footer_note(slide, caption, y=6.88)


def add_source(slide, label, url, y):
    shape = add_text(
        slide, label, Inches(1.15), Inches(y), Inches(11.0),
        Inches(0.58), size=24, color=NAVY
    )
    paragraph = shape.text_frame.paragraphs[0]
    if url and paragraph.runs:
        paragraph.runs[0].hyperlink.address = url
    return shape


def create_slides(presentation, template_slides, count):
    slides = []
    for _ in range(count):
        slide = presentation.slides.add_slide(template_slides[5].slide_layout)
        clear_slide(slide)
        slides.append(slide)
    return slides


def build():
    if not TEMPLATE.is_file():
        raise FileNotFoundError(f"School template not found: {TEMPLATE}")
    presentation = Presentation(str(TEMPLATE))
    if len(presentation.slides) != 14:
        raise ValueError(f"Expected 14 template slides, got {len(presentation.slides)}")
    template_slides = list(presentation.slides)

    # Cover: retain the school's title-slide composition and identity.
    cover = template_slides[0]
    cover_shapes = {shape.name: shape for shape in cover.shapes}
    title = cover_shapes["Google Shape;110;p1"]
    title.left, title.top = Inches(1.58), Inches(1.28)
    title.width, title.height = Inches(11.16), Inches(1.0)
    set_text(title, "PARKSPOT", 32, MAGENTA, True, PP_ALIGN.CENTER)
    year = cover_shapes["Google Shape;111;p1"]
    year.left, year.top = Inches(3.2), Inches(2.42)
    year.width, year.height = Inches(6.95), Inches(0.55)
    set_text(year, "UKK PAKET 2  |  2026–2027", 28, NAVY, True, PP_ALIGN.CENTER)
    details = cover_shapes["Google Shape;112;p1"]
    details.left, details.top = Inches(2.4), Inches(3.17)
    details.width, details.height = Inches(8.7), Inches(2.7)
    details.text_frame.clear()
    details.text_frame.word_wrap = True
    for index, line in enumerate([
        "Aplikasi parkir terpadu berbasis web dan mobile",
        "Jundi Faiq Mahbubillah  |  NIS 24183041",
        "XII RPL  |  SMK Bina Informatika Bintaro",
    ]):
        paragraph = (
            details.text_frame.paragraphs[0] if index == 0
            else details.text_frame.add_paragraph()
        )
        paragraph.text = line
        paragraph.alignment = PP_ALIGN.CENTER
        paragraph.font.name = FONT
        paragraph.font.size = Pt(24)
        paragraph.font.color.rgb = NAVY
        paragraph.space_after = Pt(9)

    slides = create_slides(presentation, template_slides, 19)

    # 1. Open with a human problem, not a feature list.
    slide = slides[0]
    prepare_slide(slide, "Mencari slot seharusnya dimulai dari informasi.")
    add_box(
        slide, Inches(0.95), Inches(2.82), Inches(3.1), Inches(1.5),
        "Tiba di gerbang", "Slot kosong belum terlihat",
        fill=PALE, accent=MAGENTA, title_size=28, detail_size=24,
        align=PP_ALIGN.CENTER
    )
    add_arrow(slide, Inches(4.23), Inches(3.35), Inches(0.6))
    add_box(
        slide, Inches(4.92), Inches(2.82), Inches(3.1), Inches(1.5),
        "Mencari sendiri", "Pengemudi berkeliling area",
        fill=PALE, accent=ORANGE, title_size=28, detail_size=24,
        align=PP_ALIGN.CENTER
    )
    add_arrow(slide, Inches(8.22), Inches(3.35), Inches(0.6))
    add_box(
        slide, Inches(8.9), Inches(2.82), Inches(3.4), Inches(1.5),
        "Antrean keluar", "Pengecekan dan bayar bertemu di gerbang",
        fill=PALE, accent=RED, title_size=28, detail_size=24,
        align=PP_ALIGN.CENTER
    )

    # 2. State the real needs before introducing the product.
    slide = slides[1]
    prepare_slide(slide, "Empat friksi di balik proses parkir.")
    problem_rows = [
        "Slot kosong tidak terlihat dari gerbang.",
        "Sensor per slot menambah biaya pemasangan dan perawatan.",
        "Tamu enggan memasang aplikasi hanya untuk kunjungan sesekali.",
        "Bayar di gerbang berpotensi menambah antrean keluar.",
    ]
    for index, text in enumerate(problem_rows):
        y = Inches(2.0 + index * 0.95)
        add_dot(slide, Inches(1.17), y + Inches(0.16))
        add_text(
            slide, text, Inches(1.78), y, Inches(10.6), Inches(0.58),
            size=24, color=NAVY
        )

    # 3. Tie the project purpose directly to those needs.
    slide = slides[2]
    prepare_slide(slide, "ParkSpot dirancang untuk menjawab kebutuhan itu.")
    goals = [
        ("TEMUKAN", "Pilih slot kosong terdekat dari data denah, tanpa sensor per slot."),
        ("AKSES", "Berikan Guest akses tiket, lokasi, dan tagihan lewat QR."),
        ("KELOLA", "Satukan booking, transaksi, tarif, dan rekap di satu alur data."),
    ]
    for index, (label, detail) in enumerate(goals):
        x = Inches(1.1 + index * 4.12)
        add_dot(slide, x + Inches(0.06), Inches(2.2))
        add_text(
            slide, label, x, Inches(2.72), Inches(3.6), Inches(0.55),
            size=28, color=NAVY, bold=True
        )
        add_text(
            slide, detail, x, Inches(3.48), Inches(3.65), Inches(1.55),
            size=24, color=BODY
        )

    # 4. Make the proposed business process visible.
    slide = slides[3]
    prepare_slide(slide, "Satu perjalanan, dari input pelat sampai keluar.")
    steps = [
        ("Input pelat", "1.8"),
        ("Tentukan slot", "2.0"),
        ("Pantau tiket", "1.75"),
        ("Bayar QRIS / kasir", "2.0"),
        ("Scan & tutup", "1.8"),
    ]
    x = 1.05
    for index, (label, width_text) in enumerate(steps):
        width = float(width_text)
        add_box(
            slide, Inches(x), Inches(2.92), Inches(width), Inches(1.06),
            label, fill=PALE, accent=MAGENTA if index in (0, 4) else NAVY,
            title_size=24, align=PP_ALIGN.CENTER
        )
        x += width
        if index < len(steps) - 1:
            add_arrow(slide, Inches(x), Inches(3.25), Inches(0.42))
            x += 0.48
    add_footer_note(
        slide, "Palang fisik masih dibuka manual oleh petugas."
    )

    # 5. Explain the tools through the needs they serve.
    slide = slides[4]
    prepare_slide(slide, "Teknologi dipilih sesuai peran sistem.")
    stack = [
        "Laravel + Tailwind  |  modul web dan format struk.",
        "Flutter + Dart  |  aplikasi Member dan booking.",
        "Firebase Realtime DB  |  status slot dan transaksi tersinkron.",
        "Midtrans Sandbox  |  simulasi pembayaran QRIS.",
    ]
    for index, text in enumerate(stack):
        y = Inches(1.95 + index * 0.94)
        add_dot(slide, Inches(1.17), y + Inches(0.17))
        add_text(
            slide, text, Inches(1.78), y, Inches(10.5), Inches(0.68),
            size=24, color=NAVY
        )
    add_footer_note(
        slide, "XAMPP digunakan sebagai server lokal pada proses pengembangan web."
    )

    # 6. Use verified public positioning; do not repeat unverified feature claims.
    slide = slides[5]
    prepare_slide(slide, "Kompetitor: bandingkan fokus, bukan asumsi fitur.")
    competitor_items = [
        "Konvensional: pencarian manual; bayar di gerbang (proposal).",
        "Parkee: solusi terintegrasi; 700+ lokasi (situs resmi).",
        "Secure Parking: layanan operasional dan produk parkir (situs resmi).",
        "ParkSpot: slot dari denah, booking, QR Guest, QRIS Sandbox (rancangan).",
    ]
    for index, text in enumerate(competitor_items):
        y = Inches(1.95 + index * 0.96)
        add_dot(slide, Inches(1.17), y + Inches(0.17))
        add_text(
            slide, text, Inches(1.78), y, Inches(10.55), Inches(0.68),
            size=24, color=NAVY
        )

    # 7. Cover every main actor without shrinking the full source diagram.
    slide = slides[6]
    prepare_slide(slide, "Use Case: siapa memakai fitur yang mana?")
    roles = [
        ("ADMIN", "Akun, tarif, area, slot, log"),
        ("PETUGAS", "Kendaraan masuk/keluar, booking"),
        ("OWNER", "Rekap transaksi sesuai waktu"),
        ("MEMBER", "Kendaraan, booking, lokasi, tagihan"),
        ("GUEST", "Lokasi dan tagihan lewat QR karcis"),
    ]
    for index, (role, actions) in enumerate(roles):
        y = Inches(1.75 + index * 0.91)
        add_text(
            slide, f"{role}  —  {actions}", Inches(1.15), y,
            Inches(11.1), Inches(0.62), size=24, color=NAVY
        )
    add_footer_note(
        slide, "Ringkasan aktor dan hak akses; diagram lengkap tersedia di lampiran."
    )

    # 8. Activity summary: check-in and nearest-slot assignment.
    slide = slides[7]
    prepare_slide(slide, "Activity  |  kendaraan masuk")
    entry_steps = [
        "Input pelat dan jenis kendaraan",
        "Cek apakah ada booking aktif",
        "Gunakan slot booking atau slot hijau terdekat",
        "Tandai merah dan mulai transaksi",
    ]
    for index, text in enumerate(entry_steps):
        y = Inches(2.05 + index * 0.82)
        add_dot(
            slide, Inches(1.27), y + Inches(0.18),
            color=GREEN if index == 2 else MAGENTA
        )
        add_text(
            slide, text, Inches(1.95), y, Inches(10.3), Inches(0.62),
            size=24, color=NAVY
        )
    add_footer_note(
        slide, "Urutan alokasi diambil dari logika ParkingService pada repositori."
    )

    # 9. Activity summary: booking and its arrival window.
    slide = slides[8]
    prepare_slide(slide, "Activity  |  booking dan toleransi")
    booking_lines = [
        "Member memilih slot dan waktu; status slot berubah menjadi oranye.",
        "Tiba ≤ 15 menit: petugas memproses check-in dan slot menjadi merah.",
        "Lewat 15 menit: booking dibatalkan dan slot dilepas.",
    ]
    for index, text in enumerate(booking_lines):
        y = Inches(2.18 + index * 1.05)
        add_dot(
            slide, Inches(1.35), y + Inches(0.18),
            color=ORANGE if index == 0 else MAGENTA
        )
        add_text(
            slide, text, Inches(2.05), y, Inches(10.1), Inches(0.72),
            size=24, color=NAVY
        )
    add_footer_note(
        slide, "Toleransi 15 menit berlaku sebelum kendaraan masuk, bukan durasi parkir."
    )

    # 10. Activity summary: QR ticket and running bill.
    slide = slides[9]
    prepare_slide(slide, "Activity  |  cek lokasi dan tagihan")
    monitor_steps = [
        "Pindai QR karcis → buka halaman Guest.",
        "Sistem membaca transaksi parkir.",
        "Tampilkan lokasi slot, durasi, dan tagihan berjalan.",
    ]
    for index, text in enumerate(monitor_steps):
        y = Inches(2.15 + index * 1.05)
        add_dot(slide, Inches(1.35), y + Inches(0.18))
        add_text(
            slide, text, Inches(2.05), y, Inches(10.1), Inches(0.72),
            size=24, color=NAVY
        )
    add_footer_note(
        slide, "Member melihat informasi serupa melalui aplikasi; Guest tidak perlu akun."
    )

    # 11. Activity summary: sandbox QRIS.
    slide = slides[10]
    prepare_slide(slide, "Activity  |  pembayaran QRIS")
    payment_steps = [
        "Pilih transaksi → API meminta QRIS.",
        "QR dibuat di lingkungan Midtrans Sandbox.",
        "Status transaksi mengikuti respons pembayaran.",
        "Sandbox bukan bukti transaksi produksi atau hasil uji lapangan.",
    ]
    for index, text in enumerate(payment_steps):
        y = Inches(2.0 + index * 0.88)
        add_dot(
            slide, Inches(1.35), y + Inches(0.18),
            color=ORANGE if index == 3 else MAGENTA
        )
        add_text(
            slide, text, Inches(2.05), y, Inches(10.1), Inches(0.65),
            size=24, color=NAVY if index < 3 else BODY
        )

    # 12. Activity summary: manual exit, settlement, and receipt.
    slide = slides[11]
    prepare_slide(slide, "Activity  |  keluar dan cetak struk")
    exit_steps = [
        "Petugas scan tiket → transaksi dibuka.",
        "Sistem menghitung durasi dan biaya akhir.",
        "Pembayaran tunai di kasir atau verifikasi status QRIS.",
        "Transaksi selesai → slot hijau → struk dicetak.",
        "Palang fisik dibuka manual oleh petugas.",
    ]
    for index, text in enumerate(exit_steps):
        y = Inches(1.9 + index * 0.82)
        add_dot(
            slide, Inches(1.3), y + Inches(0.18),
            color=GREEN if index == 3 else MAGENTA
        )
        add_text(
            slide, text, Inches(2.0), y, Inches(10.2), Inches(0.62),
            size=24, color=NAVY
        )

    # 13. Explain the Firebase data model without presenting it as SQL tables.
    slide = slides[12]
    prepare_slide(slide, "Database  |  transaksi menjadi pusat alur.")
    add_box(
        slide, Inches(4.05), Inches(1.82), Inches(5.2), Inches(1.0),
        "Firebase RTDB", "Node JSON",
        fill=PALE_PINK, accent=MAGENTA, title_size=28, detail_size=24,
        align=PP_ALIGN.CENTER
    )
    add_box(
        slide, Inches(4.05), Inches(3.12), Inches(5.2), Inches(1.02),
        "tb_transaksi", "status, waktu, biaya, tiket",
        fill=WHITE, accent=MAGENTA, title_size=28, detail_size=24,
        align=PP_ALIGN.CENTER
    )
    data_nodes = [
        ("tb_user", 1.1, 4.45),
        ("tb_kendaraan", 1.1, 5.25),
        ("tb_area_parkir", 1.1, 6.05),
        ("tb_slot_parkir", 8.4, 4.45),
        ("tb_tarif", 8.4, 5.25),
        ("tb_log_aktivitas", 8.4, 6.05),
    ]
    for node, x, y in data_nodes:
        add_box(
            slide, Inches(x), Inches(y), Inches(3.8), Inches(0.62),
            node, fill=PALE, accent=NAVY, title_size=24,
            align=PP_ALIGN.CENTER
        )
    add_line(slide, Inches(6.65), Inches(2.82), Inches(6.65), Inches(3.08))
    for x, y in [
        (4.9, 4.4), (4.9, 5.18), (4.9, 5.98),
        (8.15, 4.4), (8.15, 5.18),
    ]:
        add_line(
            slide, Inches(6.65), Inches(4.14), Inches(x), Inches(y),
            color=BORDER, width=1.3
        )
    add_footer_note(
        slide, "Transaksi merujuk user, slot, area, tarif; log terpisah.", y=6.88
    )

    # 14. Show the real project UI artifacts as wireframes, not test evidence.
    slide = slides[13]
    prepare_slide(slide, "Rancangan antarmuka  |  Member dan Guest")
    add_image_fit(
        slide, WIREFRAMES / "member_booking.png",
        Inches(1.1), Inches(1.72), Inches(2.85), Inches(4.72)
    )
    add_image_fit(
        slide, WIREFRAMES / "guest_karcis_digital.png",
        Inches(4.05), Inches(1.72), Inches(2.45), Inches(4.72)
    )
    add_text(
        slide, "Member", Inches(1.1), Inches(6.46), Inches(2.85),
        Inches(0.38), size=24, color=NAVY, bold=True, align=PP_ALIGN.CENTER
    )
    add_text(
        slide, "Guest", Inches(4.05), Inches(6.46), Inches(2.45),
        Inches(0.38), size=24, color=NAVY, bold=True, align=PP_ALIGN.CENTER
    )
    add_text(
        slide,
        "Booking slot dan estimasi kedatangan",
        Inches(7.1), Inches(2.05), Inches(5.0), Inches(0.82),
        size=28, color=NAVY, bold=True
    )
    add_text(
        slide,
        "Guest: QR membuka lokasi dan tagihan tanpa akun.",
        Inches(7.1), Inches(3.08), Inches(5.0), Inches(1.0),
        size=24, color=BODY
    )
    add_text(
        slide,
        "Wireframe proposal; bukan data produksi.",
        Inches(7.1), Inches(4.72), Inches(5.0), Inches(1.05),
        size=24, color=MAGENTA, bold=True
    )

    # 15. Separate source-code evidence from field-test claims.
    slide = slides[14]
    prepare_slide(slide, "Batas implementasi  |  apa yang dapat diklaim?")
    add_text(
        slide, "TERLIHAT DI REPOSITORI", Inches(1.0), Inches(1.9),
        Inches(5.3), Inches(0.5), size=24, color=GREEN, bold=True
    )
    add_text(
        slide,
        "Laravel: alur parkir. Flutter: status slot dari Firebase.",
        Inches(1.0), Inches(2.62), Inches(5.35), Inches(1.05),
        size=24, color=BODY
    )
    add_text(
        slide, "QRIS menggunakan Midtrans Sandbox.",
        Inches(1.0), Inches(4.12), Inches(5.35), Inches(0.72),
        size=24, color=BODY
    )
    divider = slide.shapes.add_shape(
        MSO_SHAPE.RECTANGLE, Inches(6.55), Inches(1.9),
        Inches(0.035), Inches(3.65)
    )
    divider.fill.solid()
    divider.fill.fore_color.rgb = BORDER
    divider.line.fill.background()
    add_text(
        slide, "BELUM DIKLAIM", Inches(7.0), Inches(1.9),
        Inches(4.7), Inches(0.5), size=24, color=RED, bold=True
    )
    add_text(
        slide, "Semua skenario lulus / siap produksi.",
        Inches(7.0), Inches(2.62), Inches(4.7), Inches(1.05),
        size=24, color=BODY
    )
    add_text(
        slide, "Palang otomatis atau kinerja lapangan tervalidasi.",
        Inches(7.0), Inches(4.12), Inches(4.7), Inches(0.82),
        size=24, color=BODY
    )
    add_footer_note(
        slide, "Ada kode bukan berarti alur telah diuji end-to-end."
    )

    # 16. Keep the proposal's test plan explicitly in the future tense.
    slide = slides[15]
    prepare_slide(slide, "Rencana pengujian  |  hasil belum dicantumkan")
    test_items = [
        "Login dan pembatasan akses tiap peran.",
        "Check-in, alokasi slot, tiket QR, dan status slot.",
        "Booking dan pembatalan setelah toleransi.",
        "Akses Guest ke lokasi dan tagihan berjalan.",
        "QRIS Sandbox, check-out, dan cetak struk.",
        "Rekap transaksi untuk Owner.",
    ]
    for index, text in enumerate(test_items):
        y = Inches(1.78 + index * 0.73)
        add_dot(slide, Inches(1.17), y + Inches(0.16))
        add_text(
            slide, text, Inches(1.85), y, Inches(10.5), Inches(0.55),
            size=24, color=NAVY
        )

    # 17. Close with a scoped, defensible conclusion.
    slide = slides[16]
    prepare_slide(slide, "Kesimpulan  |  ParkSpot menyatukan alur parkir.")
    conclusions = [
        ("ARAH", "Slot terdekat ditentukan dari data, tanpa sensor per slot."),
        ("AKSES", "Member memesan; Guest membuka informasi lewat QR."),
        ("BATAS", "Manfaatnya perlu dibuktikan melalui uji skenario dan lapangan."),
    ]
    for index, (tag, detail) in enumerate(conclusions):
        y = Inches(2.0 + index * 1.22)
        add_text(
            slide, tag, Inches(1.15), y, Inches(1.8), Inches(0.55),
            size=28, color=MAGENTA, bold=True
        )
        add_text(
            slide, detail, Inches(3.05), y, Inches(8.9), Inches(0.75),
            size=24, color=NAVY
        )
    # 18. Appendix divider.
    slide = slides[17]
    prepare_slide(slide, "Lampiran  |  diagram sumber proyek")
    add_text(
        slide, "Diagram di halaman berikut adalah aset proposal ParkSpot.",
        Inches(1.3), Inches(2.42), Inches(10.7), Inches(0.75),
        size=28, color=NAVY, bold=True, align=PP_ALIGN.CENTER
    )
    add_text(
        slide, "Perbesar tampilan saat meninjau detail node dan alur.",
        Inches(1.3), Inches(3.42), Inches(10.7), Inches(0.7),
        size=24, color=BODY, align=PP_ALIGN.CENTER
    )

    # 19. Sources and traceability.
    slide = slides[18]
    prepare_slide(slide, "Referensi dan batas sumber")
    add_source(
        slide, "Proposal ParkSpot UKK — masalah, tujuan, proses, diagram, wireframe.",
        None, 1.78
    )
    add_source(
        slide, "Parkee — situs resmi, pernyataan solusi parkir terintegrasi.",
        "https://parkee.app/", 2.6
    )
    add_source(
        slide, "Secure Parking — situs resmi, layanan dan produk parkir.",
        "https://secureparking.co.id/produk-layanan/", 3.42
    )
    add_source(
        slide, "Ketentuan presentasi UKK SMK Bina Informatika — Arial, 32/28/24 pt.",
        None, 4.24
    )
    add_source(
        slide, "Microsoft Support — tips presentasi efektif.",
        "https://support.microsoft.com/en-us/powerpoint/tips-for-creating-and-delivering-an-effective-presentation",
        5.06
    )
    add_footer_note(
        slide, "Klaim kompetitor dibatasi pada keterangan situs resmi; fitur rinci tidak diasumsikan.",
        y=6.15
    )

    # Full source diagrams: keep originals available for zoom and review.
    appendix_specs = [
        (
            "Use Case ParkSpot",
            "Diagram ParkSpot UKK-Use Case.drawio.png",
            "Diagram lengkap dari proposal."
        ),
        (
            "Flowchart proses ParkSpot",
            "Diagram ParkSpot UKK-FlowChart.drawio.png",
            "Diagram lengkap dari proposal."
        ),
        (
            "DFD Level 0",
            "Diagram ParkSpot UKK-DFD LEVEL 0.drawio.png",
            "Diagram lengkap dari proposal."
        ),
        (
            "Activity  |  input pelat dan slot terdekat",
            "Diagram ParkSpot UKK-Activity Input Pelat & Pencarian Slot Terdekat.drawio.png",
            "Diagram lengkap dari proposal; detail dapat diperbesar."
        ),
        (
            "Activity  |  booking dan toleransi",
            "Diagram ParkSpot UKK-Activity Pengecekan & Booking.drawio.png",
            "Diagram lengkap dari proposal; detail dapat diperbesar."
        ),
        (
            "Activity  |  lokasi slot dan tagihan",
            "Diagram ParkSpot UKK-Activity Lihat slot & Tagihan.drawio.png",
            "Diagram lengkap dari proposal; detail dapat diperbesar."
        ),
        (
            "Activity  |  pembayaran QRIS",
            "Diagram ParkSpot UKK-Activity Pembayaran (QRIS).drawio.png",
            "Diagram lengkap dari proposal; detail dapat diperbesar."
        ),
        (
            "Activity  |  keluar dan cetak struk",
            "Diagram ParkSpot UKK-Activity Keluar gerbang & Print Struk.drawio.png",
            "Diagram lengkap dari proposal; detail dapat diperbesar."
        ),
        (
            "ERD Firebase Realtime Database",
            "Diagram ParkSpot UKK-ERD.drawio.png",
            "Diagram lengkap dari proposal; key menghubungkan node NoSQL."
        ),
    ]
    for title_text, filename, caption in appendix_specs:
        slide = presentation.slides.add_slide(template_slides[5].slide_layout)
        add_full_diagram(slide, title_text, filename, caption)
        slides.append(slide)

    # Preserve the school's closing-slide artwork.
    thank_slide, qna_slide = template_slides[12], template_slides[13]
    thank_title = next(
        shape for shape in thank_slide.shapes
        if shape.name == "Google Shape;174;p13"
    )
    set_text(thank_title, "TERIMA KASIH", 32, WHITE, True, PP_ALIGN.CENTER)
    thank_subtitle = next(
        shape for shape in thank_slide.shapes
        if shape.name == "Google Shape;175;p13"
    )
    set_text(
        thank_subtitle, "Jundi Faiq Mahbubillah",
        24, WHITE, False, PP_ALIGN.CENTER
    )
    qna_title = next(
        shape for shape in qna_slide.shapes
        if shape.name == "Google Shape;182;p14"
    )
    set_text(qna_title, "TANYA JAWAB", 32, WHITE, True, PP_ALIGN.CENTER)
    qna_subtitle = next(
        shape for shape in qna_slide.shapes
        if shape.name == "Google Shape;183;p14"
    )
    set_text(
        qna_subtitle, "ParkSpot  |  UKK 2026–2027",
        24, WHITE, False, PP_ALIGN.CENTER
    )

    # Keep authored slides together, then the official closing pair.
    ordered = [cover, *slides, thank_slide, qna_slide]
    slide_ids = presentation.slides._sldIdLst
    id_by_slide = {
        id(slide): slide_ids[index]
        for index, slide in enumerate(presentation.slides)
    }
    ordered_ids = [id_by_slide[id(slide)] for slide in ordered]
    for slide_id in list(slide_ids):
        slide_ids.remove(slide_id)
    for slide_id in ordered_ids:
        slide_ids.append(slide_id)

    if len(presentation.slides) != len(ordered):
        raise ValueError(
            f"Expected {len(ordered)} slides, got {len(presentation.slides)}"
        )
    presentation.save(str(OUTPUT))
    return OUTPUT


if __name__ == "__main__":
    print(f"Presentasi berhasil dibuat: {build()}")
