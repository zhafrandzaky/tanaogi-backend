# TanaOgi Backend

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" />
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/Docker-Compose-2496ED?style=for-the-badge&logo=docker&logoColor=white" />
  <img src="https://img.shields.io/badge/Deployed%20on-Railway-0B0D0E?style=for-the-badge&logo=railway&logoColor=white" />
</p>

REST API backend untuk **TanaOgi** — platform wisata Sulawesi Selatan.
Repo ini adalah backend only. Frontend berada di repo terpisah.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.3+ |
| Database | MySQL 8.0 |
| Auth | Laravel Sanctum |
| Role & Permission | Spatie Laravel Permission |
| File Storage | Cloudflare R2 |
| WA Notification | Fonnte API |
| Scheduler | Laravel Scheduler |
| Container | Docker + Docker Compose |
| Deployment | Railway |

---

## Prerequisites

Pastikan sudah terinstall sebelum setup:

- [Docker](https://www.docker.com/) & Docker Compose
- [Git](https://git-scm.com/)

Tidak perlu install PHP atau MySQL secara lokal — semuanya berjalan via Docker.

> **Developer Windows (Laragon/XAMPP):** lihat [`docs/LOCAL_SETUP.md`](docs/LOCAL_SETUP.md)

---

## Quick Start

```bash
# 1. Clone repo
git clone https://github.com/username/tanaogi-backend.git
cd tanaogi-backend

# 2. Copy environment file
cp .env.example .env

# 3. Isi environment variables yang dibutuhkan di .env
# Minimal: DB_*, CLOUDFLARE_R2_* (minta credentials R2 dari senior), FONNTE_TOKEN, ADMIN_WHATSAPP

# 4. Jalankan Docker
docker compose up -d

# 5. Masuk ke container
docker compose exec app bash

# 6. Install dependencies
composer install

# 7. Generate app key
php artisan key:generate

# 8. Jalankan migration & seeder
php artisan migrate --seed

# 9. Keluar dari container
exit

# API berjalan di http://localhost:8000
```

---

## Verifikasi Setup

```bash
# Health check
curl http://localhost:8000/api/v1/health

# Response
{ "status": "ok", "service": "TanaOgi API" }
```

---

## Struktur Folder

```
tanaogi-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/    ← semua controller
│   │   ├── Requests/              ← validasi input
│   │   ├── Resources/V1/          ← transformasi response
│   │   └── Middleware/            ← security middleware
│   ├── Services/                  ← business logic
│   ├── Repositories/              ← database queries
│   │   └── Contracts/             ← interfaces
│   ├── Models/                    ← Eloquent models
│   ├── Enums/                     ← enumerations
│   ├── Exceptions/                ← custom exceptions
│   └── Traits/                    ← reusable traits
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php                    ← entry point, load versi
│   ├── api_v1.php                 ← semua endpoint v1
│   └── console.php                ← scheduler jobs
├── docs/                          ← dokumentasi teknis
├── docker/
│   └── nginx/default.conf
├── docker-compose.yml
├── Dockerfile
└── .env.example
```

---

## API

### Base URL

```
Local      : http://localhost:8000/api/v1
Production : https://api.tanaogi.zyy.my.id/api/v1
```

### Endpoint Utama

| Method | Endpoint | Akses | Deskripsi |
|---|---|---|---|
| GET | `/health` | Public | Health check |
| GET | `/regencies` | Public | Daftar kabupaten/kota |
| GET | `/destinations` | Public | Daftar destinasi |
| GET | `/destinations/{slug}` | Public | Detail destinasi |
| GET | `/destinations/{slug}/accommodations` | Public | Rekomendasi penginapan |
| GET | `/vehicles` | Public | Daftar kendaraan & harga |
| GET | `/settings/whatsapp` | Public | Nomor & template WA admin |
| GET | `/maintenance/status` | Public | Status maintenance |
| POST | `/auth/login` | Public | Login admin |
| POST | `/auth/logout` | Auth | Logout |
| * | `/admin/*` | Admin | Full admin control |

Dokumentasi endpoint lengkap ada di [`docs/INTEGRATION.md`](docs/INTEGRATION.md).

---

## Development Workflow

### Branch

Setiap fitur/task menggunakan branch baru:

```bash
git checkout main
git pull origin main
git checkout -b feat/task-XXX-nama-singkat
```

### Commit Convention

Mengikuti [Conventional Commits](https://www.conventionalcommits.org/):

```
feat: add destination CRUD endpoints
fix: resolve driver availability query bug
chore: setup Docker and docker-compose
docs: update API endpoint documentation
migration: create destinations table
refactor: extract logic to DestinationService
test: add authentication unit tests
```

### Pull Request

1. Push branch ke GitHub
2. Buat Pull Request ke `main`
3. Review kode
4. Merge setelah approved

---

## Perintah Harian

```bash
# Masuk ke container
docker compose exec app bash

# Migration
php artisan migrate
php artisan migrate:fresh --seed

# Routes
php artisan route:list

# Scheduler (test manual)
php artisan schedule:run
php artisan reminders:send-driver

# Testing
php artisan test
php artisan test --filter NamaTest

# Cache
php artisan cache:clear
php artisan config:clear
```

---

## Environment Variables

Salin `.env.example` ke `.env` dan isi variabel berikut:

| Variable | Deskripsi |
|---|---|
| `APP_NAME` | Nama aplikasi |
| `APP_KEY` | Generate via `php artisan key:generate` |
| `APP_ENV` | Environment (`local` atau `production`) |
| `APP_DEBUG` | Mode debug (`true` untuk dev, `false` untuk production) |
| `APP_URL` | URL aplikasi |
| `FRONTEND_URL` | URL frontend untuk CORS |
| `MAINTENANCE_SECRET` | Secret key untuk bypass maintenance |
| `DB_CONNECTION` | Driver database (gunakan `mysql`) |
| `DB_HOST` | MySQL host (`mysql` untuk Docker, `127.0.0.1` untuk Laragon/XAMPP) |
| `DB_PORT` | Port MySQL (default: `3306`) |
| `DB_DATABASE` | Nama database |
| `DB_USERNAME` | Username database |
| `DB_PASSWORD` | Password database |
| `FILESYSTEM_DISK` | Selalu `r2` — dev dan production langsung ke R2 Cloudflare |
| `CLOUDFLARE_R2_ACCESS_KEY` | R2 access key — minta dari senior via chat |
| `CLOUDFLARE_R2_SECRET_KEY` | R2 secret key — minta dari senior via chat |
| `CLOUDFLARE_R2_BUCKET` | Dev: `tanaogi-storage-dev`, production: `tanaogi-storage` |
| `CLOUDFLARE_R2_ENDPOINT` | Endpoint R2 Cloudflare |
| `CLOUDFLARE_R2_URL` | URL publik R2 / custom domain |
| `FONNTE_TOKEN` | Token Fonnte API untuk WA reminder |
| `ADMIN_WHATSAPP` | Nomor WA admin (format: 628xxx) |
| `SANCTUM_STATEFUL_DOMAINS` | Domain frontend untuk Sanctum |
| `MAIL_MAILER` | Mail driver — opsional, hanya jika email aktif |
| `MAIL_HOST` | SMTP host |
| `MAIL_PORT` | SMTP port |
| `MAIL_USERNAME` | SMTP username |
| `MAIL_PASSWORD` | SMTP password |
| `MAIL_FROM_ADDRESS` | Alamat pengirim email |
| `MAIL_FROM_NAME` | Nama pengirim email |

---

## Dokumentasi

| File | Deskripsi |
|---|---|
| [`docs/FEATURES.md`](docs/FEATURES.md) | Alur bisnis lengkap 5 tahap TanaOgi |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Clean architecture & middleware stack |
| [`docs/DATABASE.md`](docs/DATABASE.md) | Skema database & relasi |
| [`docs/API_VERSIONING.md`](docs/API_VERSIONING.md) | Format response standar |
| [`docs/AUTHENTICATION.md`](docs/AUTHENTICATION.md) | Auth & role management |
| [`docs/SERVICES.md`](docs/SERVICES.md) | Daftar semua service class |
| [`docs/CODING_CONVENTIONS.md`](docs/CODING_CONVENTIONS.md) | Konvensi kode |
| [`docs/STORAGE.md`](docs/STORAGE.md) | Setup R2 Cloudflare |
| [`docs/INTEGRATION.md`](docs/INTEGRATION.md) | Semua endpoint API |
| [`docs/EXTERNAL.md`](docs/EXTERNAL.md) | Integrasi Google Maps, WA, Fonnte |
| [`docs/SCHEDULER.md`](docs/SCHEDULER.md) | Sistem reminder driver |
| [`docs/DOCKER.md`](docs/DOCKER.md) | Setup Docker (developer Linux/senior) |
| [`docs/LOCAL_SETUP.md`](docs/LOCAL_SETUP.md) | Setup Laragon atau XAMPP (developer Windows) |
| [`docs/TEAM.md`](docs/TEAM.md) | Panduan tim: git workflow, onboarding, komunikasi |
| [`docs/RAILWAY.md`](docs/RAILWAY.md) | Deployment ke Railway |
| [`docs/TASKS.md`](docs/TASKS.md) | Progress tracker development |

---

## License

Private — All rights reserved.
