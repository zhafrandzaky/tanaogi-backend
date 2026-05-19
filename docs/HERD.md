# HERD.md — Setup TanaOgi untuk Developer tanpa Docker (Laravel Herd)

Panduan ini untuk developer Windows atau Mac yang tidak menggunakan Docker.
Developer dengan Docker tetap ikuti `docs/DOCKER.md`.

---

## Prasyarat

- [Laravel Herd](https://herd.laravel.com) (download dan install)
- Git

---

## Instalasi Laravel Herd

- Download dan install Herd dari https://herd.laravel.com
- Herd sudah include: PHP 8.3+, Nginx
- MySQL tersedia via Herd Pro atau install terpisah (lihat bagian Setup MySQL)

---

## Setup MySQL

### Opsi A — MySQL via Herd Pro (berbayar, paling simpel)

- Buka Herd → Services → MySQL → Start
- Host: `127.0.0.1`, Port: `3306`

### Opsi B — MySQL Standalone (gratis)

- Download MySQL 8.0 dari https://dev.mysql.com/downloads/mysql/
- Install dan jalankan MySQL service
- Buat database dan user via MySQL shell:

```sql
CREATE DATABASE tanaogi;
CREATE USER 'tanaogi_user'@'localhost' IDENTIFIED BY 'secret';
GRANT ALL PRIVILEGES ON tanaogi.* TO 'tanaogi_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## Setup Project

Clone repo ke folder Herd Sites (default: `~/Herd/`):

```bash
cd ~/Herd
git clone https://github.com/username/tanaogi-backend.git
cd tanaogi-backend
```

Copy environment file:

```bash
cp .env.example .env
```

Edit `.env` untuk Herd (tanpa Docker):

```env
APP_URL=http://tanaogi-backend.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tanaogi
DB_USERNAME=tanaogi_user
DB_PASSWORD=secret

FILESYSTEM_DISK=local

# Kosongkan semua CLOUDFLARE_R2_* — tidak dibutuhkan untuk dev lokal
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=
CLOUDFLARE_R2_ENDPOINT=
CLOUDFLARE_R2_URL=
```

---

## Install Dependencies & Setup

```bash
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

---

## File Storage (tanpa MinIO)

Karena tidak pakai Docker dan MinIO, gunakan disk `local`:

- `FILESYSTEM_DISK=local` di `.env`
- Foto tersimpan di `storage/app/public/`
- Diakses via `http://tanaogi-backend.test/storage/`

**Penting:** `storage/app/public/` sudah ada di `.gitignore` — foto lokal tidak akan ikut ke repo.

---

## PHP Version

Pastikan Herd menggunakan PHP 8.3+:

- Klik kanan icon Herd di taskbar
- PHP → pilih 8.3 atau 8.4
- Atau konfigurasi per project di Herd settings

---

## Verifikasi Setup

Buka browser:

```
http://tanaogi-backend.test/api/v1/health
```

Response yang diharapkan:

```json
{ "status": "ok", "service": "TanaOgi API" }
```

---

## Workflow Harian

```bash
# Setelah pull branch baru yang ada migration
php artisan migrate

# Reset database
php artisan migrate:fresh --seed

# Lihat semua route
php artisan route:list

# Jalankan tests
php artisan test
```

---

## Perbedaan dengan Setup Docker

| Fitur | Docker (Linux) | Herd (Windows/Mac) |
|---|---|---|
| PHP | 8.3 via container | 8.3+ via Herd |
| MySQL | Container `mysql:8.0` | Herd Pro atau standalone |
| Storage | MinIO (R2-compatible) | Local disk |
| URL | `http://localhost:8000` | `http://tanaogi-backend.test` |
| Jalankan | `docker compose up -d` | Herd otomatis |

---

## Yang TIDAK Boleh Di-push ke Repo

- `.env` — sudah di `.gitignore`
- `storage/app/public/*` — sudah di `.gitignore`
- `vendor/` — sudah di `.gitignore`

---

## Jika Ada Error

### Migration error: Specified key was too long

Tambahkan di `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Schema;

public function boot(): void
{
    Schema::defaultStringLength(191);
}
```

### Storage permission error (Windows)

Jalankan di terminal:

```bash
php artisan storage:link --force
```

### MySQL tidak bisa connect

- Pastikan MySQL service sudah running
- Cek `DB_HOST=127.0.0.1` (bukan `localhost` atau `mysql`)
- Cek port `3306` tidak dipakai aplikasi lain
