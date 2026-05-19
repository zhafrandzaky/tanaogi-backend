# LOCAL_SETUP.md — Setup TanaOgi di Windows (Tanpa Docker)

## Untuk Siapa Panduan Ini

Panduan ini untuk developer Windows yang tidak pakai Docker.
Developer Linux tetap ikuti `docs/DOCKER.md`.

---

## Pilih Tools Kamu

| | Laragon | XAMPP |
|---|---|---|
| Rekomendasi | **Ya** (khusus Laravel) | Jika sudah terinstall |
| PHP version | Mudah ganti lewat UI | Manual via PATH |
| MySQL | Ada + HeidiSQL | Ada + phpMyAdmin |
| URL project | Otomatis `.test` | `localhost:8000` |
| Jalankan server | Otomatis | `php artisan serve` |

---

## OPSI A — Laragon (Direkomendasikan)

### 1. Install Laragon

- Download dari https://laragon.org/download/
- Pilih **Laragon Full** (sudah include PHP, MySQL, HeidiSQL)
- Pastikan pilih PHP 8.3+ saat install (atau ganti setelah install)

### 2. Setup MySQL di Laragon

- Buka Laragon → klik **Start All**
- Klik **Database** → HeidiSQL terbuka otomatis
- Buat database baru:
  - Klik kanan di sidebar kiri → **Create new** → **Database**
  - Nama: `tanaogi`
  - Collation: `utf8mb4_unicode_ci`
  - Klik OK
- Buat user (opsional, bisa pakai root):
  - Host: `127.0.0.1`, User: `tanaogi_user`, Password: `secret`
  - Beri akses ke database `tanaogi`

### 3. Clone Project

- Buka folder `C:\laragon\www\`
- Klik kanan → **Git Bash Here** (atau buka terminal di folder ini)

```bash
git clone https://github.com/username/tanaogi-backend.git
cd tanaogi-backend
```

### 4. Setup .env

```bash
cp .env.example .env
```

Edit `.env`:

```env
APP_URL=http://tanaogi-backend.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tanaogi
DB_USERNAME=tanaogi_user
DB_PASSWORD=secret

FILESYSTEM_DISK=r2

# R2 dev credentials — minta dari senior via chat
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=tanaogi-storage-dev
CLOUDFLARE_R2_ENDPOINT=
CLOUDFLARE_R2_URL=https://dev-storage.tanaogi.zyy.my.id
```

> **Catatan:** kosongkan dulu `CLOUDFLARE_R2_ACCESS_KEY`, `SECRET_KEY`, dan `ENDPOINT` — minta dari senior sebelum test fitur upload foto.

### 5. Install dan Jalankan

```bash
composer install
php artisan key:generate
php artisan migrate --seed
```

### 6. Verifikasi

Laragon otomatis detect project — tidak perlu `php artisan serve`.

Buka browser: `http://tanaogi-backend.test/api/v1/health`

Response:
```json
{ "status": "ok", "service": "TanaOgi API" }
```

---

## OPSI B — XAMPP (Jika Sudah Terinstall)

### 1. Pastikan XAMPP Sudah Install

- Download dari https://www.apachefriends.org/ jika belum ada
- Buka **XAMPP Control Panel**
- Klik **Start** untuk Apache dan MySQL

### 2. Setup Database via phpMyAdmin

- Buka http://localhost/phpmyadmin
- Klik **New** di sidebar kiri
- Database name: `tanaogi`
- Collation: `utf8mb4_unicode_ci`
- Klik **Create**

### 3. Clone Project

Buka folder `C:\xampp\htdocs\` atau folder lain, buka terminal di sana:

```bash
git clone https://github.com/username/tanaogi-backend.git
cd tanaogi-backend
```

### 4. Setup .env

```bash
cp .env.example .env
```

Edit `.env`:

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tanaogi
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=r2

# R2 dev credentials — minta dari senior via chat
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=tanaogi-storage-dev
CLOUDFLARE_R2_ENDPOINT=
CLOUDFLARE_R2_URL=https://dev-storage.tanaogi.zyy.my.id
```

> **Catatan:** default XAMPP username `root` dengan password kosong.

### 5. Install dan Jalankan

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 6. Verifikasi

Buka browser: `http://localhost:8000/api/v1/health`

Response:
```json
{ "status": "ok", "service": "TanaOgi API" }
```

---

## Storage R2 Cloudflare

Tidak perlu setup apapun selain isi `.env`.
Semua foto langsung tersimpan di R2 bucket `tanaogi-storage-dev` — tidak ada di komputer kamu.

Sebelum test fitur upload foto, minta credentials R2 dari senior:
- `CLOUDFLARE_R2_ACCESS_KEY`
- `CLOUDFLARE_R2_SECRET_KEY`
- `CLOUDFLARE_R2_ENDPOINT`

Isi di `.env` tapi **jangan commit ke repo**.

---

## PHP Version

Pastikan PHP 8.3+:

- **Laragon:** klik kanan icon Laragon di taskbar → **PHP** → pilih 8.3 atau 8.4
- **XAMPP:** cek di http://localhost/dashboard → lihat PHP version

---

## Perintah Harian

```bash
# Setelah pull branch baru yang ada migration
php artisan migrate

# Reset database
php artisan migrate:fresh --seed

# Lihat semua route
php artisan route:list

# Jalankan tests
php artisan test

# Bersihkan cache jika ada masalah konfigurasi
php artisan cache:clear
php artisan config:clear
```

---

## Troubleshooting

### MySQL tidak bisa connect

- Pastikan MySQL sudah Start di Laragon/XAMPP
- `DB_HOST` harus `127.0.0.1` — bukan `localhost` atau `mysql`
- Cek port `3306` tidak dipakai aplikasi lain

### Upload foto error

- Pastikan `CLOUDFLARE_R2_ACCESS_KEY`, `SECRET_KEY`, dan `ENDPOINT` sudah diisi
- Minta dari senior via chat
- Cek koneksi internet

### Key too long error saat migrate

Tambahkan di `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Schema;

public function boot(): void
{
    Schema::defaultStringLength(191);
}
```

### composer not found

Install Composer dari https://getcomposer.org/download/

### php not found di terminal

- **Laragon:** buka terminal dari Laragon (bukan CMD biasa) — klik **Terminal** di UI Laragon
- **XAMPP:** tambah `C:\xampp\php` ke PATH environment variable Windows

### Laragon tidak detect project otomatis

Pastikan folder project ada langsung di `C:\laragon\www\tanaogi-backend\` (bukan subfolder tambahan).
Restart Laragon jika URL `.test` belum muncul.
