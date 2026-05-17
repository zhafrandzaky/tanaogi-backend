# TanaOgi Backend

REST API untuk platform wisata Sulawesi Selatan — **TanaOgi**.

## Requirements

- Docker & Docker Compose
- PHP 8.4+ (via Docker)
- Laravel 13
- PostgreSQL 16 (via Docker)

## Quick Start

```bash
# 1. Clone repo
git clone https://github.com/your-org/tanaogi-backend.git
cd tanaogi-backend

# 2. Copy environment
cp .env.example .env

# 3. Jalankan Docker
docker compose up -d

# 4. Masuk ke container
docker compose exec app bash

# 5. Install dependencies
composer install

# 6. Generate key
php artisan key:generate

# 7. Migrate & seed
php artisan migrate --seed

# 8. Selesai — API berjalan di http://localhost:8000
```

## Struktur Repo

```
tanaogi-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/V1/
│   │   ├── Requests/
│   │   ├── Resources/V1/
│   │   └── Middleware/
│   ├── Services/
│   ├── Repositories/
│   │   └── Contracts/
│   ├── Models/
│   ├── Enums/
│   ├── Exceptions/
│   └── Traits/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── api_v1.php
├── docs/
│   ├── FEATURES.md
│   ├── ARCHITECTURE.md
│   ├── API_VERSIONING.md
│   ├── CODING_CONVENTIONS.md
│   ├── DATABASE.md
│   ├── AUTHENTICATION.md
│   ├── SERVICES.md
│   ├── DOCKER.md
│   ├── RAILWAY.md
│   ├── INTEGRATION.md
│   ├── EXTERNAL.md
│   └── SCHEDULER.md
│   └── TASKS.md
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Dokumentasi

Baca `docs` terlebih dahulu sebelum mulai development.

## API Base URL

```
Local      : http://localhost:8000/api/v1
Production : https://api.tanaogi.com/api/v1
```

## Tim

- Backend: TanaOgi Dev Team
- Frontend: Repo terpisah (tanaogi-frontend)
