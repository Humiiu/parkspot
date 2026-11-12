from pathlib import Path

from PIL import Image
from pptx import Presentation
from pptx.dml.color import RGBColor
from pptx.enum.shapes import MSO_SHAPE, MSO_CONNECTOR
from pptx.enum.text import PP_ALIGN, MSO_ANCHOR
from pptx.util import Inches, Pt


ROOT = Path(__file__).resolve().parent
TEMPLATE = ROOT / "TEMPLATE PPT QP UKK 2026-2027.pptx"
OUTPUT = ROOT / "ParkSpot - Presentasi UKK 2026-2027 - Revisi.pptx"
DIAGRAMS = ROOT / "diagram"
WIREFRAMES = ROOT / "wireframes"
BADGES = ROOT / "ppt_assets" / "badges"

MAGENTA = RGBColor(0xB6, 0x0D, 0x79)
NAVY = RGBColor(0x34, 0x3E, 0x4D)
BODY = RGBColor(0x3F, 0x45, 0x50)
PALE = RGBColor(0xF5, 0xF6, 0xF9)
PALE_PINK = RGBColor(0xFB, 0xF4, 0xF8)
BORDER = RGBColor(0xD9, 0xDD, 0xE3)
WHITE = RGBColor(0xFF, 0xFF, 0xFF)
MUTED = RGBColor(0x68, 0x70, 0x7C)
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


def add_text(slide, text, left, top, width, height, size=24, color=BODY,
             bold=False, align=PP_ALIGN.LEFT):
    shape = slide.shapes.add_textbox(left, top, width, height)
    shape.text_frame.margin_left = Inches(0.03)
    shape.text_frame.margin_right = Inches(0.03)
    shape.text_frame.margin_top = Inches(0.01)
    shape.text_frame.margin_bottom = Inches(0.01)
    return set_text(shape, text, size, color, bold, align)


def add_card(slide, left, top, width, height, text, fill=PALE,
             color=NAVY, size=24, bold=False, align=PP_ALIGN.CENTER):
    card = slide.shapes.add_shape(
        MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height
    )
    card.fill.solid()
    card.fill.fore_color.rgb = fill
    card.line.color.rgb = BORDER
    card.line.width = Pt(1.2)
    textbox = slide.shapes.add_textbox(
        left + Inches(0.12), top + Inches(0.08),
        width - Inches(0.24), height - Inches(0.16)
    )
    textbox.text_frame.margin_left = Inches(0.02)
    textbox.text_frame.margin_right = Inches(0.02)
    textbox.text_frame.margin_top = Inches(0.01)
    textbox.text_frame.margin_bottom = Inches(0.01)
    set_text(textbox, text, size, color, bold, align)
    return card


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
        element = shape._element
        element.getparent().remove(element)


def add_heading(slide, title):
    add_text(
        slide, title, Inches(1.5), Inches(0.63), Inches(10.3), Inches(0.63),
        size=32, color=MAGENTA, bold=True
    )
    accent = slide.shapes.add_shape(
        MSO_SHAPE.RECTANGLE, Inches(1.5), Inches(1.39), Inches(0.9), Inches(0.07)
    )
    accent.fill.solid()
    accent.fill.fore_color.rgb = MAGENTA
    accent.line.fill.background()


def prepare_slide(slide, title):
    clear_slide(slide)
    add_heading(slide, title)


def add_bullet_row(slide, text, y, width=10.4, x=1.8):
    marker = slide.shapes.add_shape(
        MSO_SHAPE.ROUNDED_RECTANGLE, Inches(x), Inches(y + 0.13),
        Inches(0.1), Inches(0.34)
    )
    marker.fill.solid()
    marker.fill.fore_color.rgb = MAGENTA
    marker.line.fill.background()
    add_text(
        slide, text, Inches(x + 0.28), Inches(y), Inches(width), Inches(0.62),
        size=24, color=NAVY
    )


def add_flow_arrow(slide, start_x, end_x, y):
    connector = slide.shapes.add_connector(
        MSO_CONNECTOR.STRAIGHT, Inches(start_x), Inches(y),
        Inches(end_x), Inches(y)
    )
    connector.line.color.rgb = MAGENTA
    connector.line.width = Pt(2.5)
    connector.line.end_arrowhead = True


def add_caption(slide, text, left, top, width, align=PP_ALIGN.CENTER):
    add_text(
        slide, text, left, top, width, Inches(0.48),
        size=24, color=NAVY, bold=True, align=align
    )


def reorder_slides(presentation, desired_slides):
    slide_ids = presentation.slides._sldIdLst
    id_by_slide = {
        id(slide): slide_ids[index]
        for index, slide in enumerate(presentation.slides)
    }
    ordered_ids = [id_by_slide[id(slide)] for slide in desired_slides]
    for slide_id in list(slide_ids):
        slide_ids.remove(slide_id)
    for slide_id in ordered_ids:
        slide_ids.append(slide_id)


def build():
    if not TEMPLATE.is_file():
        raise FileNotFoundError(f"School template not found: {TEMPLATE}")
    presentation = Presentation(str(TEMPLATE))
    if len(presentation.slides) != 14:
        raise ValueError(f"Expected 14 template slides, got {len(presentation.slides)}")
    base_slides = list(presentation.slides)

    # 1. Cover - preserve the school's title-slide composition.
    cover = base_slides[0]
    cover_shapes = {shape.name: shape for shape in cover.shapes}
    title = cover_shapes["Google Shape;110;p1"]
    title.left, title.top = Inches(1.55), Inches(1.05)
    title.width, title.height = Inches(10.3), Inches(0.82)
    set_text(
        title, "QUALITY PLAN & UJI KOMPETENSI KEAHLIAN",
        32, MAGENTA, True, PP_ALIGN.CENTER
    )
    year = cover_shapes["Google Shape;111;p1"]
    year.left, year.top = Inches(4.0), Inches(2.05)
    year.width, year.height = Inches(5.3), Inches(0.42)
    set_text(year, "Tahun Pembelajaran 2026–2027", 28, NAVY, False, PP_ALIGN.CENTER)
    details = cover_shapes["Google Shape;112;p1"]
    details.left, details.top = Inches(2.25), Inches(2.85)
    details.width, details.height = Inches(9.0), Inches(3.0)
    details.text_frame.clear()
    details.text_frame.word_wrap = True
    for index, line in enumerate([
        "Nama: Jundi Faiq Mahbubillah",
        "NIS: 24183041  |  Kelas: XII RPL",
        "Paket 2  |  Nama produk: ParkSpot",
        "Aplikasi parkir terpadu berbasis web dan mobile",
    ]):
        paragraph = (
            details.text_frame.paragraphs[0] if index == 0
            else details.text_frame.add_paragraph()
        )
        paragraph.text = line
        paragraph.font.name = FONT
        paragraph.font.size = Pt(24)
        paragraph.font.color.rgb = NAVY
        paragraph.space_after = Pt(8)

    # 2. Background: explain the concrete parking problems.
    prepare_slide(base_slides[1], "LATAR BELAKANG | MASALAH PARKIR")
    for index, problem in enumerate([
        "Pengemudi sering berputar untuk mencari slot kosong.",
        "Tanpa petunjuk, lokasi slot kosong sulit diketahui sejak gerbang.",
        "Sensor di tiap slot memerlukan biaya pemasangan dan perawatan.",
        "Tiket kertas tak menampilkan lokasi atau tagihan berjalan.",
    ]):
        add_bullet_row(base_slides[1], problem, 1.8 + index * 0.94)

    # 3. Turn the problems into a clear case for building the application.
    prepare_slide(base_slides[2], "MENGAPA PARKSPOT DIBUAT?")
    for index, statement in enumerate([
        "Slot tak terlihat  →  perlu alokasi terdekat berbasis denah.",
        "Sensor mahal  →  kelola status slot melalui perangkat lunak.",
        "Tamu tanpa aplikasi  →  sediakan akses informasi melalui QR.",
        "Antrean keluar  →  tampilkan tagihan dan dukung bayar QRIS.",
    ]):
        add_bullet_row(base_slides[2], statement, 1.8 + index * 0.94)

    # 4. Objectives and who benefits from the project.
    prepare_slide(base_slides[3], "TUJUAN & SASARAN PENGEMBANGAN")
    for index, statement in enumerate([
        "Mengatur parkir melalui web dan aplikasi mobile yang terhubung.",
        "Memberi slot kosong terdekat berdasarkan data lokasi.",
        "Melayani Admin, Petugas, Owner, Member, dan pengunjung tamu.",
        "Menyediakan booking member serta akses tiket tamu via QR.",
    ]):
        add_bullet_row(base_slides[3], statement, 1.8 + index * 0.94)

    # 5. Main solution and product advantages.
    prepare_slide(base_slides[4], "SOLUSI PARKSPOT | FITUR UTAMA")
    features = [
        ("HIJAU", "Kosong", RGBColor(0x41, 0x8B, 0x67)),
        ("ORANYE", "Dipesan", RGBColor(0xD4, 0x8A, 0x2F)),
        ("MERAH", "Terisi", RGBColor(0xB7, 0x4C, 0x59)),
    ]
    for index, (label, meaning, color) in enumerate(features):
        x = Inches(1.65 + index * 3.62)
        circle = base_slides[4].shapes.add_shape(
            MSO_SHAPE.OVAL, x + Inches(0.83), Inches(1.92),
            Inches(0.74), Inches(0.74)
        )
        circle.fill.solid()
        circle.fill.fore_color.rgb = color
        circle.line.fill.background()
        add_caption(
            base_slides[4], label, x, Inches(2.78), Inches(2.4)
        )
        add_text(
            base_slides[4], meaning, x, Inches(3.28), Inches(2.4),
            Inches(0.48), size=24, color=BODY, align=PP_ALIGN.CENTER
        )
    add_bullet_row(
        base_slides[4], "Karcis QR untuk tamu; booking slot untuk member.",
        4.35, width=9.9
    )
    add_bullet_row(
        base_slides[4], "Pembayaran QRIS dirancang melalui Midtrans Sandbox.",
        5.2, width=9.9
    )

    # 6. Proposed business process, inspired by the example's process slides.
    prepare_slide(base_slides[5], "PROSES BISNIS YANG DIUSULKAN")
    flow_items = [
        ("01", "MASUK", "Input pelat"),
        ("02", "ALOKASI", "Slot terdekat"),
        ("03", "PANTAU", "App / QR"),
        ("04", "KELUAR", "Bayar tiket"),
    ]
    box_y, box_w, box_h = Inches(2.45), Inches(2.25), Inches(1.6)
    positions = [Inches(1.55 + index * 2.8) for index in range(4)]
    for index, (number, heading, detail) in enumerate(flow_items):
        add_card(
            base_slides[5], positions[index], box_y, box_w, box_h,
            f"{number}\n{heading}\n{detail}", fill=PALE_PINK,
            color=NAVY, size=24, bold=True
        )
        if index < len(flow_items) - 1:
            add_flow_arrow(
                base_slides[5], 3.92 + index * 2.8, 4.12 + index * 2.8,
                3.25
            )
    add_text(
        base_slides[5], "Satu alur data untuk operasional dan pengguna.",
        Inches(2.0), Inches(4.75), Inches(9.3), Inches(0.55),
        size=24, color=BODY, align=PP_ALIGN.CENTER
    )

    # 7. Actors and responsibilities.
    prepare_slide(base_slides[6], "SASARAN PENGGUNA & HAK AKSES")
    roles = [
        "Admin — mengelola akun, tarif, area, slot, dan log.",
        "Petugas — mencatat kendaraan masuk dan transaksi keluar.",
        "Owner — memantau okupansi serta rekap pendapatan.",
        "Member — memesan slot dan memantau kendaraan.",
        "Guest — membuka lokasi serta tagihan lewat QR.",
    ]
    for index, role in enumerate(roles):
        add_bullet_row(base_slides[6], role, 1.68 + index * 0.82, width=9.9)

    # 8. Pre-production: project flowchart.
    prepare_slide(base_slides[7], "PRA-PRODUKSI | ALUR SISTEM")
    add_image_fit(
        base_slides[7], DIAGRAMS / "Diagram ParkSpot UKK-FlowChart.drawio.png",
        Inches(1.65), Inches(1.72), Inches(9.95), Inches(4.45)
    )
    add_caption(
        base_slides[7],
        "Rancangan alur sebelum implementasi web dan mobile.",
        Inches(1.65), Inches(6.2), Inches(9.95)
    )

    # 9. Pre-production: data design and realtime status.
    prepare_slide(base_slides[8], "PRA-PRODUKSI | RANCANGAN DATA")
    add_image_fit(
        base_slides[8], DIAGRAMS / "Diagram ParkSpot UKK-ERD.drawio.png",
        Inches(1.65), Inches(1.68), Inches(9.95), Inches(4.85)
    )
    add_caption(
        base_slides[8],
        "Struktur Firebase: user, area, slot, tarif, kendaraan, transaksi, log.",
        Inches(1.65), Inches(6.5), Inches(9.95)
    )

    # 10. Technology, matching the template and example's brand colors.
    prepare_slide(base_slides[9], "PROSES PRODUKSI | TEKNOLOGI")
    technologies = [
        ("laravel.png", "Laravel"),
        ("flutter.png", "Flutter"),
        ("firebase.png", "Firebase"),
        ("tailwind.png", "Tailwind"),
        ("midtrans.png", "Midtrans"),
    ]
    for index, (filename, name) in enumerate(technologies):
        x = Inches(1.55 + index * 2.15)
        add_card(
            base_slides[9], x, Inches(2.0), Inches(1.85), Inches(2.2),
            "", fill=WHITE
        )
        add_image_fit(
            base_slides[9], BADGES / filename,
            x + Inches(0.2), Inches(2.42), Inches(1.45), Inches(0.95)
        )
        add_text(
            base_slides[9], name, x - Inches(0.04), Inches(3.48),
            Inches(1.95), Inches(0.52), size=24, color=NAVY,
            bold=True, align=PP_ALIGN.CENTER
        )
    add_text(
        base_slides[9],
        "Web / REST API  ↔  Firebase  ↔  Aplikasi Flutter",
        Inches(1.85), Inches(4.75), Inches(9.8), Inches(0.6),
        size=24, color=MAGENTA, bold=True, align=PP_ALIGN.CENTER
    )
    add_text(
        base_slides[9], "QRIS menggunakan lingkungan uji Midtrans Sandbox.",
        Inches(1.85), Inches(5.48), Inches(9.8), Inches(0.6),
        size=24, color=BODY, align=PP_ALIGN.CENTER
    )

    # 11. Web interface concepts.
    prepare_slide(base_slides[10], "RANCANGAN ANTARMUKA | WEB")
    add_image_fit(
        base_slides[10], WIREFRAMES / "petugas_gate_in.png",
        Inches(1.7), Inches(1.68), Inches(4.65), Inches(4.38)
    )
    add_image_fit(
        base_slides[10], WIREFRAMES / "admin_denah_slot.png",
        Inches(6.7), Inches(1.68), Inches(4.65), Inches(4.38)
    )
    add_caption(
        base_slides[10], "Pos gerbang petugas",
        Inches(1.7), Inches(6.15), Inches(4.65)
    )
    add_caption(
        base_slides[10], "Denah slot Admin",
        Inches(6.7), Inches(6.15), Inches(4.65)
    )

    # 12. Mobile member and guest wireframes.
    prepare_slide(base_slides[11], "RANCANGAN ANTARMUKA | MOBILE & TAMU")
    add_image_fit(
        base_slides[11], WIREFRAMES / "member_beranda.png",
        Inches(2.25), Inches(1.7), Inches(3.5), Inches(4.55)
    )
    add_image_fit(
        base_slides[11], WIREFRAMES / "guest_karcis_digital.png",
        Inches(7.05), Inches(1.7), Inches(3.5), Inches(4.55)
    )
    add_caption(
        base_slides[11], "Beranda Member",
        Inches(2.25), Inches(6.28), Inches(3.5)
    )
    add_caption(
        base_slides[11], "Tiket digital Guest",
        Inches(7.05), Inches(6.28), Inches(3.5)
    )

    # 13. Testing plans; avoid stating unverified test outcomes.
    testing = presentation.slides.add_slide(base_slides[5].slide_layout)
    prepare_slide(testing, "PASKA-PRODUKSI | PENGUJIAN")
    for index, scenario in enumerate([
        "Login dan akses menu berdasarkan peran pengguna.",
        "Check-in, alokasi slot, QR tiket, dan status parkir.",
        "Booking, batas waktu, billing, dan check-out.",
        "Pembayaran, pencetakan struk, dan laporan transaksi.",
    ]):
        add_bullet_row(testing, scenario, 1.8 + index * 0.94)
    add_text(
        testing,
        "Status berhasil dicatat berdasarkan hasil eksekusi pengujian.",
        Inches(1.8), Inches(5.75), Inches(9.9), Inches(0.58),
        size=24, color=MUTED
    )

    # 14. Conclusion and a realistic next step.
    conclusion = presentation.slides.add_slide(base_slides[5].slide_layout)
    prepare_slide(conclusion, "KESIMPULAN & PENGEMBANGAN")
    for index, statement in enumerate([
        "ParkSpot menghubungkan alur masuk, pemantauan, dan keluar.",
        "Alokasi berbasis data mengurangi kebutuhan sensor per slot.",
        "QR memberi akses bagi tamu; aplikasi melayani member.",
        "Lanjutkan dengan uji lapangan dan validasi alur pembayaran.",
    ]):
        add_bullet_row(conclusion, statement, 1.8 + index * 0.94)

    # Keep the original school closing artwork and update its supplied text.
    thank_slide, qna_slide = base_slides[12], base_slides[13]
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

    reorder_slides(
        presentation,
        [*base_slides[:12], testing, conclusion, thank_slide, qna_slide]
    )
    if len(presentation.slides) != 16:
        raise ValueError(f"Expected 16 slides, got {len(presentation.slides)}")
    presentation.save(str(OUTPUT))
    return OUTPUT


if __name__ == "__main__":
    print(f"Presentasi berhasil dibuat: {build()}")
