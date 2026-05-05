# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Running the app

```bash
# Development server (document root = public/)
php -S localhost:8000 -t public

# Set development mode in .env
APP_ENV=development
```

Copy `.env.example` to `.env` and fill in DB credentials before starting.

## Database setup

Initialize schema on a fresh database:
```bash
mysql -u root -p kna_site < app/schema.sql
```

To migrate an existing database (idempotent `ALTER TABLE IF NOT EXISTS`):
```bash
mysql -u root -p kna_site < app/schema_migration.sql
```

Required env vars: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

## Seed Data (ข้อมูลตั้งต้น)

ข้อมูลได้ถูก seed ลง DB แล้ว (production-ready):

| ตาราง | จำนวน | หมายเหตุ |
|---|---|---|
| `doctors` | 22 | ชื่อไทย/อังกฤษ, เฉพาะทาง, คลินิก — ดึงจากต้นฉบับ knainterpharma.co.th |
| `clinics` | 428 | ดึงจาก `รายชื่อคลินิค.xlsx` พร้อม province, district, phone, social URLs |
| `clinic_products` | 463 | link คลินิก ↔ สินค้า (Hyabell/METEORA/Variofill/NeoFilera) |

**Products ใน DB:**
| id | ชื่อ |
|---|---|
| 1 | NeoFilera |
| 2 | METEORA |
| 3 | Variofill |
| 4 | Hyabell |

## Deployment

**Stack**: nixpacks (PHP 8.2 + PDO MySQL, GD, mbstring, curl, openssl, fileinfo). Start command: `php -S 0.0.0.0:$PORT -t public`.

**GitHub remote**: `https://github.com/Iiyas12j/kna-home-php.git`

### Recommended platforms

| Platform | PHP | MySQL | ฟรี | หมายเหตุ |
|---|---|---|---|---|
| **Railway** | ✅ nixpacks | ✅ built-in | ❌ ~$5/เดือน | แนะนำสำหรับ production |
| **Render** | ✅ nixpacks | ✅ add-on | ✅ (sleep 15 นาที) | ทดสอบก่อน deploy จริง |
| **Fly.io** | ✅ Docker | ❌ ต้องใช้ PlanetScale | ✅ tier | ซับซ้อนกว่า |

### Railway deploy

1. railway.app → New Project → Deploy from GitHub → เลือก `kna-home-php`
2. Add MySQL service → Railway inject `DATABASE_URL` ให้อัตโนมัติ
3. ตั้ง env vars:
   ```
   APP_NAME=KNA Interpharma
   APP_ENV=production
   BASE_URL=https://your-domain.up.railway.app
   DB_HOST=  # จาก Railway MySQL variables
   DB_NAME=
   DB_USER=
   DB_PASS=
   ```
4. หลัง deploy ครั้งแรก รัน schema: `mysql ... < app/schema.sql`

### หมายเหตุ Uploads

`public/uploads/` ไม่ persistent บน Railway/Render (ephemeral filesystem) — ถ้าต้องการเก็บไฟล์ข้าม deploy ให้ใช้ S3-compatible storage (Cloudflare R2, Backblaze B2) แล้วแก้ `save_uploaded_image()` ใน `app/helpers.php`

## หน้าเว็บทั้งหมด (Public Pages)

| หน้า | ไฟล์ | หมายเหตุ |
|---|---|---|
| Home | `public/index.php` | Hero slider, สินค้า, ข่าว, วิดีโอ TikTok, ค้นหาคลินิก |
| เกี่ยวกับเรา | `public/about-us.php` | ข้อมูลบริษัท |
| ติดต่อ | `public/contact.php` | **2 สาขา** (BKK + นครสวรรค์) + Google Maps + ฟอร์มส่งข้อความ |
| สินค้า | `public/product.php` | รายการสินค้าทั้งหมด |
| สินค้าชิ้นเดียว | `public/single-product.php` | Image + highlights + description + related + CTA banner |
| ข่าว/อีเวนต์ | `public/news-event.php` | รายการข่าว |
| รายละเอียดข่าว | `public/news-detail.php` | ข่าวชิ้นเดียว |
| วิดีโอ (TikTok) | `public/video-tiktok.php` | TikTok embeds |
| วิดีโอ (เรียนรู้) | `public/video-learning.php` | สำหรับ member/doctor |
| วิดีโอ detail | `public/video-detail.php` | ดูวิดีโอ |
| ทำเนียบแพทย์ | `public/doctors_directory.php` | Grid 2 คอล, รูป 230×250px, ค้นหาได้ |
| ค้นหาคลินิก | `public/searchpage.php` | ค้นหา 428 คลินิก + filter จังหวัด |
| Login | `public/login.php` | สีขาว/KNA purple, มี "ยินดีต้อนรับกลับมา!", จดจำฉัน, ลืมรหัสผ่าน |
| Register | `public/register.php` | ครบ 10 fields: ชื่อ, นามสกุล, อีเมล, เลขใบฯ, โรงพยาบาล, จังหวัด, โทร, Line, รหัสผ่าน, terms |

## Design System

- **สีหลัก**: `#4B4899` (KNA purple) — ใช้ทุกหน้า ห้ามใช้ indigo/violet แทน
- **Font**: Kanit (Google Fonts) — ทุกหน้า
- **Icons**: Font Awesome 6 CDN
- **CSS Framework**: Tailwind CSS CDN (`cdn.tailwindcss.com`)
- **Logo**: `public/uploads/logo-kna.png` — header height **52px** desktop / **44px** mobile
- **Background**: `bg-gray-50` เป็น default ของทุกหน้า

## Architecture

### Request flow

Every page in `public/` is a self-contained PHP file. Pages pull data from the DB then render HTML inline — no framework, no router.

```
public/index.php           → requires app/db.php → gets $pdo
                           → requires app/helpers.php
                           → queries DB, assigns variables
                           → requires public/partials/site-header.php
                           → renders HTML
                           → requires public/partials/site-footer.php
```

Admin pages additionally call `require_admin()` from `app/auth.php` at the top, which redirects to `/admin/login.php` if `$_SESSION['admin_id']` is empty.

### Core layer (`app/`)

| File | Purpose |
|---|---|
| `config.php` | Loads `.env`, defines constants (`APP_NAME`, `BASE_URL`, `APP_ENV`, `DB_*`), sets security headers and session config |
| `db.php` | Creates `$pdo` (PDO, MySQL, utf8mb4). Sets `$pdo = null` on connection failure so static pages still render |
| `auth.php` | Auth functions for both admin and member sessions — login, logout, register, role checks |
| `helpers.php` | `h()` (XSS escape), `asset_url()`, `save_uploaded_image()`, CSRF helpers, file-based rate limiter, lazy DB column migrations (`ensure_*` functions) |

### Auth & roles

There are **two independent auth systems** sharing the same `admin_users` table:

- **Admin session**: `$_SESSION['admin_id']` — checked by `is_admin_logged_in()` / `require_admin()`
- **Member session**: `$_SESSION['member_id']` — checked by `is_member_logged_in()` / `current_member()`

Roles (stored in `admin_users.role`): `admin`, `doctor`, `member`. Normalised via `normalize_member_role()` which accepts Thai aliases (e.g. `แพทย์` → `doctor`).

Video access levels: `public` (anyone), `member` (logged-in members), `doctor` (doctor role only). Checked by `member_can_access_video()`.

### Register — fields เพิ่มเติม (lazy migration)

`ensure_admin_user_registration_columns()` ใน `helpers.php` เพิ่ม column อัตโนมัติ:

| Column | Type | หมายเหตุ |
|---|---|---|
| `requested_role` | VARCHAR(40) | member / doctor |
| `doctor_license_no` | VARCHAR(120) | เลข อว. |
| `last_name` | VARCHAR(120) | นามสกุล |
| `hospital_clinic` | VARCHAR(220) | โรงพยาบาล/คลินิก |
| `province` | VARCHAR(120) | จังหวัด |
| `phone` | VARCHAR(50) | เบอร์โทร |
| `line_id` | VARCHAR(120) | Line ID |

### Lazy schema migrations

Instead of migration files, `helpers.php` has `ensure_*()` functions that use `information_schema.COLUMNS` to add missing columns on first use — safe to run on every request due to a `static $checked` guard.

### Partials

- `public/partials/site-header.php` — shared site nav, expects `$siteHeaderActive` string
- `public/partials/site-footer.php` — shared site footer
- `public/partials/clinic-search-panel.php` — reusable clinic search form
- `public/admin/partials/header.php` / `footer.php` — admin panel chrome

### Uploaded files

All uploads go to `public/uploads/` in subfolders by type (`products/`, `news/`, `videos/`, `website/`, `doctors/`). The `media_url($value, $folder, $fallback)` helper in `public/index.php` resolves stored filenames to web paths.

### Security conventions

- Always use `h()` to escape output — never echo raw user data
- All POST forms must include `<?= csrf_field() ?>` and verify with `csrf_verify()` at the top of the handler
- Rate limiting via `rate_limit_check()` / `rate_limit_hit()` / `rate_limit_clear()` (file-based, IP-keyed, stored in `sys_get_temp_dir()`)
- `save_uploaded_image()` validates extension (jpg/jpeg/png/webp only) and uses `bin2hex(random_bytes(8))` for filenames
