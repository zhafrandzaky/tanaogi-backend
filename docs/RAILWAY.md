# RAILWAY.md — Deploy TanaOgi ke Railway

## Arsitektur Production di Railway

```
Railway Project: tanaogi
├── Service: tanaogi-api     (Laravel app)
│   └── Domain: api.tanaogi.zyy.my.id
└── Service: tanaogi-db      (MySQL 8.0)

Cloudflare R2: tanaogi-storage
└── Domain: storage.tanaogi.zyy.my.id
```

---

## Setup Awal

### 1. Buat Project di Railway
- Buka https://railway.app
- New Project → Empty Project
- Beri nama: `tanaogi`

### 2. Tambah MySQL
- New Service → Database → MySQL
- Railway otomatis generate kredensial
- Salin connection details dari tab Variables

### 3. Deploy Laravel App
- New Service → GitHub Repo
- Pilih repo `tanaogi-backend`
- Railway otomatis detect Dockerfile (production stage) dan build

---

## Environment Variables di Railway

Set semua variabel ini di tab **Variables** service `tanaogi-api`:

```env
APP_NAME=TanaOgi
APP_ENV=production
APP_KEY=                        ← php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://api.tanaogi.zyy.my.id
FRONTEND_URL=https://tanaogi.zyy.my.id
MAINTENANCE_SECRET=rahasia_tanaogi_admin_production

LOG_CHANNEL=stderr

DB_CONNECTION=mysql
DB_HOST=${{tanaogi-db.MYSQLHOST}}
DB_PORT=${{tanaogi-db.MYSQLPORT}}
DB_DATABASE=${{tanaogi-db.MYSQLDATABASE}}
DB_USERNAME=${{tanaogi-db.MYSQLUSER}}
DB_PASSWORD=${{tanaogi-db.MYSQLPASSWORD}}

SANCTUM_STATEFUL_DOMAINS=tanaogi.zyy.my.id

FILESYSTEM_DISK=r2
CLOUDFLARE_R2_ACCESS_KEY=your_r2_access_key
CLOUDFLARE_R2_SECRET_KEY=your_r2_secret_key
CLOUDFLARE_R2_BUCKET=tanaogi-storage
CLOUDFLARE_R2_ENDPOINT=https://{account_id}.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://storage.tanaogi.zyy.my.id

WAAPI_URL=https://waapi.fyas.my.id
WAAPI_KEY=wapi_production_key
ADMIN_WHATSAPP=628xxxxxxxxxx

MAIL_MAILER=smtp
MAIL_HOST=smtp.resend.com
MAIL_PORT=465
MAIL_USERNAME=resend
MAIL_PASSWORD=your_resend_api_key
MAIL_FROM_ADDRESS=noreply@tanaogi.zyy.my.id
MAIL_FROM_NAME=TanaOgi
```

---

## Dockerfile (Multi-Stage)

Dockerfile menggunakan multi-stage build. Railway otomatis menggunakan stage `production` (stage terakhir).

```
Stage "base"       → PHP 8.4-fpm + pdo_mysql + opcache + composer
Stage "dev"        → semua composer deps (untuk docker-compose lokal)
Stage "production" → --no-dev + config:cache + route:cache + view:cache
                     nginx + supervisor, expose 8080
```

**Stage production** menjalankan:
- nginx (port 8080) dan php-fpm (port 9000 internal) via supervisord
- `php artisan migrate --force` sebelum start
- Nginx root: `/var/www/public`

---

## railway.json

```json
{
  "$schema": "https://railway.app/railway.schema.json",
  "build": {
    "builder": "DOCKERFILE",
    "dockerfilePath": "Dockerfile"
  },
  "deploy": {
    "healthcheckPath": "/api/v1/health",
    "healthcheckTimeout": 300,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 3
  }
}
```

Railway memanggil `GET /api/v1/health` setiap deploy. Endpoint ini **tanpa auth** dan mengembalikan:

```json
{ "status": "ok", "service": "TanaOgi API" }
```

---

## Pre-Deploy Checklist

**Wajib diselesaikan sebelum push ke main / merge PR:**

### Railway Variables
- [ ] `APP_KEY` — generate dengan `php artisan key:generate --show`
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `LOG_CHANNEL=stderr`
- [ ] `MAINTENANCE_SECRET` — password production yang kuat
- [ ] Semua DB vars ter-set via Railway reference:
  - `DB_HOST=${{tanaogi-db.MYSQLHOST}}`
  - `DB_PORT=${{tanaogi-db.MYSQLPORT}}`
  - `DB_DATABASE=${{tanaogi-db.MYSQLDATABASE}}`
  - `DB_USERNAME=${{tanaogi-db.MYSQLUSER}}`
  - `DB_PASSWORD=${{tanaogi-db.MYSQLPASSWORD}}`

### Cloudflare R2 (Production Bucket)
- [ ] Bucket `tanaogi-storage` sudah dibuat di Cloudflare R2
- [ ] Custom domain `storage.tanaogi.zyy.my.id` sudah di-setup
- [ ] `CLOUDFLARE_R2_ACCESS_KEY` — API token production
- [ ] `CLOUDFLARE_R2_SECRET_KEY` — API token production
- [ ] `CLOUDFLARE_R2_BUCKET=tanaogi-storage`
- [ ] `CLOUDFLARE_R2_ENDPOINT` — URL endpoint R2
- [ ] `CLOUDFLARE_R2_URL=https://storage.tanaogi.zyy.my.id`

### WhatsApp (WaAPI)
- [ ] `WAAPI_URL` — URL WaAPI production
- [ ] `WAAPI_KEY` — API key WaAPI production
- [ ] `ADMIN_WHATSAPP` — nomor admin penerima notifikasi

### Email (SMTP)
- [ ] `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` — SMTP production (e.g. Resend)

### Post-Deploy
- [ ] `php artisan migrate --force` — otomatis dijalankan saat container start
- [ ] Admin default password **wajib diganti** setelah deploy pertama
- [ ] Verifikasi `GET /api/v1/health` return `{ "status": "ok" }`
- [ ] Verifikasi login admin berfungsi
- [ ] Verifikasi upload foto ke R2 production berhasil

---

## Custom Domain

1. Service `tanaogi-api` → Settings → Networking → Custom Domain
2. Tambah: `api.tanaogi.zyy.my.id`
3. Set DNS di Cloudflare:
   ```
   CNAME api → your-app.railway.app
   ```

---

## Laravel Scheduler di Railway

Opsi 1 — Cron di Railway service:
```json
{
  "deploy": {
    "cronSchedule": "* * * * *",
    "startCommand": "php artisan schedule:run"
  }
}
```

Opsi 2 — Service terpisah khusus scheduler (recommended).

---

## Setup R2 Cloudflare untuk Production

1. Buka https://dash.cloudflare.com → R2 Object Storage
2. Buat bucket: `tanaogi-storage`
3. Buat API Token dengan permission Object Read & Write
4. Setup custom domain `storage.tanaogi.zyy.my.id` di bucket settings
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
