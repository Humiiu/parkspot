import os
from PIL import Image, ImageDraw, ImageFont

def create_badges():
    out_dir = r'c:\xampp\htdocs\parkspot\ppt_assets\badges'
    os.makedirs(out_dir, exist_ok=True)

    badges = [
        ('laravel.png', 'LARAVEL 11', 'Backend & REST API', (235, 68, 54)),      # Laravel red
        ('flutter.png', 'FLUTTER', 'Aplikasi Mobile Member', (2, 86, 155)),      # Flutter blue
        ('firebase.png', 'FIREBASE', 'Realtime Database', (245, 124, 0)),        # Firebase amber
        ('tailwind.png', 'TAILWIND CSS', 'Antarmuka Web', (6, 182, 212)),        # Tailwind cyan
        ('midtrans.png', 'MIDTRANS', 'Simulasi Bayar QRIS', (15, 76, 129))       # Midtrans navy
    ]

    width = 380
    height = 120
    radius = 18

    for filename, title, subtitle, bg_color in badges:
        img = Image.new('RGBA', (width, height), (0, 0, 0, 0))
        draw = ImageDraw.Draw(img)

        # Draw rounded rectangle background
        draw.rounded_rectangle([(0, 0), (width, height)], radius=radius, fill=bg_color)

        # Draw a subtle left accent pill
        draw.rounded_rectangle([(14, 16), (22, height - 16)], radius=4, fill=(255, 255, 255, 220))

        # We can use default font or truetype font if available
        font_title = None
        font_sub = None
        for font_candidate in ['arialbd.ttf', 'seguiemj.ttf', 'arial.ttf']:
            try:
                font_title = ImageFont.truetype(font_candidate, 28)
                font_sub = ImageFont.truetype(font_candidate, 18)
                break
            except Exception:
                continue

        if font_title is None:
            font_title = ImageFont.load_default()
            font_sub = ImageFont.load_default()

        # Text draw
        draw.text((38, 22), title, font=font_title, fill=(255, 255, 255))
        draw.text((38, 64), subtitle, font=font_sub, fill=(240, 245, 255))

        out_path = os.path.join(out_dir, filename)
        img.save(out_path, 'PNG')
        print(f'Generated badge: {out_path}')

if __name__ == '__main__':
    create_badges()
