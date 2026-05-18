# RAILWAY.md — Deploy TanaOgi ke Railway

## Arsitektur Production di Railway

```
Railway Project: tanaogi
├── Service: tanaogi-api     (Laravel app)
│   └── Domain: api.tanaogi.com
└── Service: tanaogi-db      (PostgreSQL)

Cloudflare R2: tanaogi-storage
└── Domain: storage.tanaogi.com
```

---

## Setup Awal

### 1. Buat Project di Railway
- Buka https://railway.app
- New Project → Empty Project
- Beri nama: `tanaogi`

### 2. Tambah PostgreSQL
- New Service → Database → PostgreSQL
- Railway otomatis generate kredensial
- Salin connection details dari tab Variables

### 3. Deploy Laravel App
- New Service → GitHub Repo
- Pilih repo `tanaogi-backend`
- Railway otomatis detect Dockerfile dan build

---

## Environment Variables di Railway

Set semua variabel ini di tab **Variables** service `tanaogi-api`:

```env
APP_NAME=TanaOgi
APP_ENV=production
APP_KEY=                        ← php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://api.tanaogi.com
FRONTEND_URL=https://tanaogi.com
MAINTENANCE_SECRET=rahasia_tanaogi_admin_production

LOG_CHANNEL=stderr

DB_CONNECTION=pgsql
DB_HOST=${{tanaogi-db.PGHOST}}
DB_PORT=${{tanaogi-db.PGPORT}}
DB_DATABASE=${{tanaogi-db.PGDATABASE}}
DB_USERNAME=${{tanaogi-db.PGUSER}}
DB_PASSWORD=${{tanaogi-db.PGPASSWORD}}

SANCTUM_STATEFUL_DOMAINS=tanaogi.com

FILESYSTEM_DISK=r2
CLOUDFLARE_R2_ACCESS_KEY=your_r2_access_key
CLOUDFLARE_R2_SECRET_KEY=your_r2_secret_key
CLOUDFLARE_R2_BUCKET=tanaogi-storage
CLOUDFLARE_R2_ENDPOINT=https://{account_id}.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://storage.tanaogi.com

FONNTE_TOKEN=your_fonnte_token_production
ADMIN_WHATSAPP=628xxxxxxxxxx

MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=your_resend_api_key
MAIL_FROM_ADDRESS=noreply@tanaogi.com
MAIL_FROM_NAME=TanaOgi
```

---

## Dockerfile untuk Production

```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip nginx supervisor \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && php-fpm -D && nginx -g 'daemon off;'"]
```

---

## railway.json

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "DOCKERFILE"
  },
  "deploy": {
    "healthcheckPath": "/api/v1/health",
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 3
  }
}
```

---

## Health Check Endpoint

```php
Route::get('/health', fn() => response()->json([
    'status'  => 'ok',
    'service' => 'TanaOgi API',
]));
```

---

## Custom Domain

1. Service `tanaogi-api` → Settings → Networking → Custom Domain
2. Tambah: `api.tanaogi.com`
3. Set DNS di Cloudflare:
   ```
   CNAME api → your-app.railway.app
   ```

---

## Laravel Scheduler di Railway

```json
// railway.json — tambahkan scheduler sebagai cron job terpisah
{
  "deploy": {
    "cronSchedule": "* * * * *",
    "startCommand": "php artisan schedule:run"
  }
}
```

Atau buat service terpisah di Railway khusus scheduler.

---

## Setup R2 Cloudflare untuk Production

1. Buka https://dash.cloudflare.com → R2 Object Storage
2. Buat bucket: `tanaogi-storage`
3. Buat API Token dengan permission Object Read & Write
4. Setup custom domain `storage.tanaogi.com` di bucket settings
5. Isi env vars R2 di Railway

Detail lengkap di `docs/STORAGE.md`.

---

## CD/CD Otomatis

Railway otomatis deploy setiap push ke branch `main`.

Untuk staging:
- New Service → repo yang sama → branch `develop`
- Nama: `tanaogi-api-staging`

---

## Monitoring & Logs

```bash
npm install -g @railway/cli
railway login
railway link
railway logs
```
