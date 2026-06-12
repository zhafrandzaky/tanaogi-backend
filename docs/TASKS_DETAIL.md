# TASKS_DETAIL.md — Spesifikasi Teknis Per Task

Dokumen ini mencatat detail teknis setiap task: file yang dibuat, key logic, dan konfigurasi yang diperlukan.
Untuk prompt Qoder lengkap lihat `.agents/PROMPTS.md`.

---

## PHASE 1 — Project Setup

### TASK-001 — Laravel Init + Docker

**Branch:** `feat/task-001-laravel-init`

#### Files

- `docker-compose.yml` — services: app (PHP-FPM), nginx, mysql:8.0
- `Dockerfile` — non-root www-data user, PHP-FPM
- `docker/nginx/default.conf` — client_max_body_size 20M, PHP-FPM upstream
- `.env.example` — semua env variable dari AGENTS.md (tanpa nilai asli)
- `routes/api.php` — load api_v1.php dengan prefix /api/v1/
- `routes/api_v1.php` — semua route stubs
- `app/Http/Controllers/Api/V1/HealthController.php`
  - `index()` — return `{ "status": "ok", "service": "TanaOgi API" }`

#### Config

- CORS: allowed_origins dari `FRONTEND_URL` env
- `config/cors.php` — paths: `api/*`

---

### TASK-002 — Environment & Packages

**Branch:** `feat/task-002-env-packages`

#### Files

- `app/Traits/ApiResponse.php`
  - `success(data, message, code)` — envelope `{ success: true, message, data }`
  - `error(message, code, errors)` — envelope `{ success: false, message, errors }`
  - `paginated(paginator, message)` — envelope dengan meta pagination
- `config/filesystems.php` — disk `r2`: driver s3, use_path_style_endpoint true
- `config/sanctum.php` — stateful domains dari `SANCTUM_STATEFUL_DOMAINS` env

#### Packages

- `laravel/sanctum` — via `php artisan install:api`
- `spatie/laravel-permission` — migrations di-publish
- `league/flysystem-aws-s3-v3` — untuk Cloudflare R2

---

### TASK-003 — Folder Structure & Base Classes

**Branch:** `feat/task-003-folder-structure`

#### Folders

```
app/Http/Controllers/Api/V1/
app/Http/Controllers/Api/V1/Auth/
app/Http/Controllers/Api/V1/Admin/
app/Http/Controllers/Api/V1/Admin/Blacklist/
app/Services/
app/Repositories/
app/Repositories/Contracts/
```

#### Files

- `app/Enums/VehicleType.php` — cases: `car`, `bus`
- `app/Enums/DriverOrderStatus.php` — cases: `pending`, `confirmed`, `completed`, `cancelled`
- `app/Enums/AccommodationType.php` — cases: `hotel`, `resort`, `homestay`
- `app/Exceptions/DriverNotAvailableException.php`
- `app/Exceptions/PhoneBlacklistedException.php`
- `app/Providers/AppServiceProvider.php` — bind semua RepositoryInterface ke implementasi

---

## PHASE 2 — Database & Models

### TASK-004 — Core Migrations & Seeders

**Branch:** `feat/task-004-core-migrations`

#### Migrations (urutan)

1. `create_regencies_table` — CHAR(36) UUID PK, name, slug unique, is_active, timestamps
2. `create_destinations_table` — FK regency_id, name, slug unique, description, ticket_price, facilities JSON, route_text, latitude DECIMAL(10,8), longitude DECIMAL(11,8), is_active
3. `create_destination_images_table` — FK destination_id, path, url, order INT
4. `create_accommodations_table` — FK destination_id, name, type, price_per_night, address, latitude, longitude, is_active
5. `create_vehicles_table` — type, name, price_per_day, description, is_active
6. `create_drivers_table` — name, phone, vehicle_type, is_active
7. `create_driver_orders_table` — FK destination_id/vehicle_id/driver_id(nullable)/accommodation_id(nullable), user_name, user_phone, departure_date DATE, return_date DATE, is_overnight BOOL, pickup_location TEXT, status, departure_reminded, return_reminded, notes
8. `create_settings_table` — key unique, value TEXT

#### Seeders

- `RegencySeeder` — 24 kabupaten/kota Sulawesi Selatan
- `VehicleSeeder` — Mobil (car, 500000/hari), Bus Pariwisata (bus, 1500000/hari)
- `SettingSeeder` — admin_whatsapp, wa_template_driver_order, reminder_hours_before_pickup=3, max_requests_per_minute=60, max_orders_per_phone=3, orders_window_hours=24, auto_ban_enabled=true, auto_ban_duration_minutes=60, is_maintenance=false
- `RoleSeeder` — role admin via Spatie Permission
- `AdminUserSeeder` — akun admin dari env atau default admin@tanaogi.zyy.my.id

---

### TASK-005 — Blacklist Migrations

**Branch:** `feat/task-005-blacklist-migrations`

#### Migrations

1. `create_blacklist_ips_table` — ip_address VARCHAR(45), reason TEXT, is_auto BOOL, banned_at TIMESTAMP, banned_until TIMESTAMP nullable, is_active BOOL. Unique index pada (ip_address, is_active)
2. `create_whitelist_ips_table` — ip_address VARCHAR(45) unique, note TEXT, is_active BOOL
3. `create_blacklist_phones_table` — phone VARCHAR(20), reason TEXT, is_auto BOOL, banned_at, banned_until nullable, is_active. Unique index pada (phone, is_active)
4. `create_whitelist_phones_table` — phone VARCHAR(20) unique, note TEXT, is_active BOOL
5. `create_request_logs_table` — ip_address VARCHAR(45), phone VARCHAR(20) nullable, endpoint VARCHAR(255), created_at only (no updated_at). Composite index pada (ip_address, created_at) dan (phone, created_at)

---

### TASK-006 — Eloquent Models

**Branch:** `feat/task-006-eloquent-models`

#### Models (semua gunakan HasUuids)

- `Regency` — hasMany destinations, scopeActive, casts: is_active bool
- `Destination` — belongsTo regency, hasMany images/accommodations/driverOrders, scopeActive, casts: ticket_price int, facilities array, latitude/longitude float, is_active bool
- `DestinationImage` — belongsTo destination
- `Accommodation` — belongsTo destination, hasMany driverOrders, scopeActive, casts: price_per_night int, is_active bool
- `Vehicle` — hasMany driverOrders, scopeActive, casts: price_per_day int, type → VehicleType enum, is_active bool
- `Driver` — hasMany driverOrders, scopeActive, casts: is_active bool
- `DriverOrder` — belongsTo destination/vehicle/driver(nullable)/accommodation(nullable), casts: departure_date/return_date date, is_overnight/departure_reminded/return_reminded bool, status → DriverOrderStatus enum
- `Setting` — fillable: key, value
- `BlacklistIp` / `BlacklistPhone` — casts: is_auto bool, is_active bool, banned_at datetime, banned_until datetime nullable, scopeActive
- `WhitelistIp` / `WhitelistPhone` — casts: is_active bool, scopeActive
- `RequestLog` — timestamps false, hanya created_at
- `User` — tambah HasApiTokens, HasRoles (Spatie)

---

## PHASE 3 — Auth & Middleware

### TASK-007 — Authentication

**Branch:** `feat/task-007-authentication`

#### Files

- `app/Repositories/Contracts/UserRepositoryInterface.php` — findByEmail, findById, create
- `app/Repositories/UserRepository.php`
- `app/Services/AuthService.php`
  - `login(array credentials)` — validasi credentials, cek role admin, buat Sanctum token, return token + user
  - `logout(User user)` — delete currentAccessToken
- `app/Http/Requests/Auth/LoginRequest.php` — email required, password required (pesan error bahasa Indonesia)
- `app/Http/Controllers/Api/V1/Auth/AuthController.php` — login, logout (max 20 baris per method)
- `app/Http/Resources/V1/UserResource.php` — id, name, email, role, created_at (tanpa password)

#### Routes

```
POST /api/v1/auth/login     — public
POST /api/v1/auth/logout    — auth:sanctum
```

---

### TASK-008 — Security Middleware Stack

**Branch:** `feat/task-008-security-middleware`

#### Services

- `app/Services/SettingService.php`
  - `get(key)`, `getAll()`, `update(array)`
  - Shortcut: getAdminWhatsapp, getWaTemplate, getReminderHours, isAutoBanEnabled, getMaxRequestsPerMinute, getMaxOrdersPerPhone, getOrdersWindowHours, getAutoBanDuration
- `app/Services/MaintenanceService.php` — enable(), disable(), isActive()
- `app/Services/BlacklistService.php` — banIp/unbanIp/whitelistIp/removeIpFromWhitelist/isIpBlacklisted/isIpWhitelisted + ekuivalen untuk phone
- `app/Services/RateLimitService.php` — stub: checkAndLogIp, checkAndLogPhone, getIpRequestCount, getPhoneOrderCount, cleanOldLogs

#### Middleware

- `app/Http/Middleware/CheckMaintenance.php` — return 503 jika maintenance aktif; skip jika admin dengan token valid
- `app/Http/Middleware/CheckBlacklistIp.php` — whitelist IP lolos; blacklist IP return 403
- `app/Http/Middleware/RateLimitAndLog.php` — log request + cek auto-ban threshold; return 429 jika baru di-ban

#### bootstrap/app.php

- CheckMaintenance → prepend (jalan pertama)
- CheckBlacklistIp, RateLimitAndLog → appended ke api group

---

## PHASE 4 — Core Features

### TASK-009 — Regencies CRUD

**Branch:** `feat/task-009-regencies-crud`

#### Files

- `app/Repositories/Contracts/RegencyRepositoryInterface.php`
- `app/Repositories/RegencyRepository.php` — findAll, findById, create, update, delete
- `app/Services/RegencyService.php` — getAll, findById, create, update, delete, toggleActive (slug auto-generated dari name)
- `app/Http/Requests/Admin/StoreRegencyRequest.php` / `UpdateRegencyRequest.php`
- `app/Http/Resources/V1/RegencyResource.php` — id, name, slug, is_active, created_at
- `app/Http/Controllers/Api/V1/RegencyController.php` — public: index only
- `app/Http/Controllers/Api/V1/Admin/RegencyController.php` — index, store, show, update, destroy, toggleActive

#### Routes

```
GET    /api/v1/regencies                            — public
GET    /api/v1/admin/regencies                      — admin
POST   /api/v1/admin/regencies                      — admin
GET    /api/v1/admin/regencies/{id}                 — admin
PUT    /api/v1/admin/regencies/{id}                 — admin
DELETE /api/v1/admin/regencies/{id}                 — admin
PATCH  /api/v1/admin/regencies/{id}/toggle-active   — admin
```

---

### TASK-010 — Destinations CRUD + R2 Upload

**Branch:** `feat/task-010-destinations-crud`

#### Files

- `app/Repositories/Contracts/DestinationRepositoryInterface.php`
- `app/Repositories/DestinationRepository.php` — findAll, findByRegency, findBySlug, findById, create, update, delete
- `app/Services/DestinationService.php`
  - `uploadImages(Destination, files)` — path: `destinations/{id}/{uuid}.{ext}`, Storage::disk('r2')->put(..., 'public'), simpan path+url ke destination_images
  - `deleteImage(imageId)` — Storage::disk('r2')->delete(image->path) + hapus DB record
- `app/Http/Requests/Admin/UploadDestinationImageRequest.php` — images[] max 10, tiap max 2MB, mimes: jpg/jpeg/png/webp
- `app/Http/Resources/V1/DestinationResource.php` — semua field + images array URL
- `app/Http/Controllers/Api/V1/DestinationController.php` — public: index (filter ?regency_id), show
- `app/Http/Controllers/Api/V1/Admin/DestinationController.php` — full CRUD + toggleActive + uploadImages + deleteImage

#### Routes

```
GET    /api/v1/destinations                                   — public
GET    /api/v1/destinations/{slug}                            — public
GET    /api/v1/admin/destinations                             — admin
POST   /api/v1/admin/destinations                             — admin
GET    /api/v1/admin/destinations/{id}                        — admin
PUT    /api/v1/admin/destinations/{id}                        — admin
DELETE /api/v1/admin/destinations/{id}                        — admin
PATCH  /api/v1/admin/destinations/{id}/toggle-active          — admin
POST   /api/v1/admin/destinations/{id}/images                 — admin (multipart/form-data)
DELETE /api/v1/admin/destinations/{id}/images/{imageId}       — admin
```

---

### TASK-011 — Vehicles CRUD

**Branch:** `feat/task-011-vehicles-crud`

#### Files

- `app/Repositories/VehicleRepository.php` — standard CRUD
- `app/Services/VehicleService.php` — getAll, findById, create, update, delete, toggleActive
- `app/Http/Requests/Admin/StoreVehicleRequest.php` / `UpdateVehicleRequest.php`
- `app/Http/Resources/V1/VehicleResource.php` — id, type, name, price_per_day, description, is_active
- `app/Http/Controllers/Api/V1/VehicleController.php` — public: index
- `app/Http/Controllers/Api/V1/Admin/VehicleController.php` — full CRUD + toggleActive

#### Routes

```
GET    /api/v1/vehicles                             — public
GET    /api/v1/admin/vehicles                       — admin
POST   /api/v1/admin/vehicles                       — admin
GET    /api/v1/admin/vehicles/{id}                  — admin
PUT    /api/v1/admin/vehicles/{id}                  — admin
DELETE /api/v1/admin/vehicles/{id}                  — admin
PATCH  /api/v1/admin/vehicles/{id}/toggle-active    — admin
```

---

### TASK-012 — Accommodations CRUD

**Branch:** `feat/task-012-accommodations-crud`

#### Files

- `app/Repositories/Contracts/AccommodationRepositoryInterface.php`
- `app/Repositories/AccommodationRepository.php`
- `app/Services/AccommodationService.php` — getByDestination (by slug), findById, create, update, delete, toggleActive
- `app/Http/Requests/Admin/StoreAccommodationRequest.php` — name, type enum, price_per_night, address, destination_id, optional lat/lng, is_active
- `app/Http/Resources/V1/AccommodationResource.php` — id, name, type, price_per_night, address, is_active (address wajib ada — dipakai pickup location)
- `app/Http/Controllers/Api/V1/AccommodationController.php` — public: byDestination (resolve by slug)
- `app/Http/Controllers/Api/V1/Admin/AccommodationController.php` — full CRUD + toggleActive

#### Routes

```
GET    /api/v1/destinations/{slug}/accommodations       — public
GET    /api/v1/admin/accommodations                     — admin
POST   /api/v1/admin/accommodations                     — admin
GET    /api/v1/admin/accommodations/{id}                — admin
PUT    /api/v1/admin/accommodations/{id}                — admin
DELETE /api/v1/admin/accommodations/{id}                — admin
PATCH  /api/v1/admin/accommodations/{id}/toggle-active  — admin
```

---

### TASK-013 — Drivers CRUD + Schedule

**Branch:** `feat/task-013-drivers-crud`

#### Files

- `app/Repositories/Contracts/DriverRepositoryInterface.php`
- `app/Repositories/DriverRepository.php`
  - `findAvailable(departureDate, returnDate, vehicleType)` — query driver NOT IN driver_orders di kedua tanggal dengan status NOT IN (completed, cancelled)
- `app/Services/DriverService.php`
  - `getAvailable(departureDate, returnDate, vehicleType)` — delegate ke repository
  - `getSchedule(driverId, month, year)` — return blocked_dates array + orders array untuk bulan tersebut
- `app/Http/Requests/Admin/StoreDriverRequest.php` — name, phone regex `^62[0-9]{9,12}$`, vehicle_type enum car/bus
- `app/Http/Resources/V1/DriverResource.php`
- `app/Http/Resources/V1/DriverScheduleResource.php` — driver_id, driver_name, month, year, blocked_dates, orders
- `app/Http/Controllers/Api/V1/Admin/DriverController.php` — full CRUD + toggleActive + schedule

#### Routes

```
GET    /api/v1/admin/drivers                        — admin
POST   /api/v1/admin/drivers                        — admin
GET    /api/v1/admin/drivers/{id}                   — admin
PUT    /api/v1/admin/drivers/{id}                   — admin
DELETE /api/v1/admin/drivers/{id}                   — admin
PATCH  /api/v1/admin/drivers/{id}/toggle-active     — admin
GET    /api/v1/admin/drivers/{id}/schedule          — admin (?month=6&year=2025)
```

---

### TASK-014 — Driver Orders CRUD + Assign Driver

**Branch:** `feat/task-014-driver-orders`

#### Files

- `app/Repositories/Contracts/DriverOrderRepositoryInterface.php`
- `app/Repositories/DriverOrderRepository.php` — findAll(?status), findById, create, assignDriver, updateStatus, markReturnReminded
- `app/Services/DriverOrderService.php`
  - `create(array data)` — cek whitelist phone → cek blacklist phone (throw PhoneBlacklistedException) → log via RateLimitService → cek lagi → create order
  - `assignDriver(order, driverId)` — cek driver active → getAvailable → cek driverId ada di result (throw DriverNotAvailableException jika tidak) → assign driver_id, status = confirmed
  - `updateStatus`, `markReturnReminded`, `getAll(?status)`, `findById`
- `app/Http/Requests/Admin/StoreDriverOrderRequest.php`
- `app/Http/Requests/Admin/UpdateDriverOrderRequest.php` — driver_id, status
- `app/Http/Resources/V1/DriverOrderResource.php` — semua field + nested destination/vehicle/driver/accommodation

#### Routes

```
GET    /api/v1/admin/driver-orders              — admin (filter ?status)
POST   /api/v1/admin/driver-orders              — admin
GET    /api/v1/admin/driver-orders/{id}         — admin
PUT    /api/v1/admin/driver-orders/{id}         — admin
DELETE /api/v1/admin/driver-orders/{id}         — admin
```

---

## PHASE 5 — Admin Full Control

### TASK-015 — Settings Endpoint

**Branch:** `feat/task-015-settings-endpoint`

#### Files

- `app/Http/Controllers/Api/V1/SettingController.php` — public: GET /settings/whatsapp (return admin_whatsapp + wa_template)
- `app/Http/Controllers/Api/V1/Admin/SettingController.php` — GET semua setting, PUT update multiple
- `app/Http/Requests/Admin/UpdateSettingRequest.php` — validasi semua setting key

#### Routes

```
GET /api/v1/settings/whatsapp       — public
GET /api/v1/admin/settings          — admin
PUT /api/v1/admin/settings          — admin
```

---

### TASK-016 — Admin Users CRUD

**Branch:** `feat/task-016-admin-users`

#### Files

- `app/Http/Controllers/Api/V1/Admin/UserController.php` — index, store, show, update, destroy
  - `store` — auto-assign role admin
  - `destroy` — tolak jika menghapus akun sendiri (`$request->user()->id !== $user->id`)
- `app/Http/Requests/Admin/StoreUserRequest.php` — name, email unique, password, password_confirmation
- `app/Http/Requests/Admin/UpdateUserRequest.php` — name, email

#### Routes

```
GET    /api/v1/admin/users          — admin
POST   /api/v1/admin/users          — admin
GET    /api/v1/admin/users/{id}     — admin
PUT    /api/v1/admin/users/{id}     — admin
DELETE /api/v1/admin/users/{id}     — admin
```

---

### TASK-017 — Maintenance Mode

**Branch:** `feat/task-017-maintenance-mode`

#### Files

- `app/Http/Controllers/Api/V1/MaintenanceController.php` — public: GET /maintenance/status
- `app/Http/Controllers/Api/V1/Admin/MaintenanceController.php`
  - `enable()` — `Artisan::call('down', ['--secret' => config('app.maintenance_secret')])` + update setting is_maintenance=true
  - `disable()` — `Artisan::call('up')` + update setting is_maintenance=false

#### Routes

```
GET  /api/v1/maintenance/status         — public
GET  /api/v1/admin/maintenance/status   — admin
POST /api/v1/admin/maintenance/enable   — admin
POST /api/v1/admin/maintenance/disable  — admin
```

#### Config / Env

- `.env.example`: `MAINTENANCE_SECRET=`

---

## PHASE 6 — Blacklist & Security

### TASK-018 — Blacklist Whitelist Full Control

**Branch:** `feat/task-018-blacklist-whitelist`

#### Files

- `app/Http/Controllers/Api/V1/Admin/Blacklist/BlacklistIpController.php` — index, store, show, destroy, unban
- `app/Http/Controllers/Api/V1/Admin/Blacklist/WhitelistIpController.php` — index, store, destroy
- `app/Http/Controllers/Api/V1/Admin/Blacklist/BlacklistPhoneController.php` — index, store, show, destroy, unban
- `app/Http/Controllers/Api/V1/Admin/Blacklist/WhitelistPhoneController.php` — index, store, destroy
- `app/Http/Requests/Admin/StoreBlacklistIpRequest.php` — ip_address, reason, duration_minutes optional
- `app/Http/Requests/Admin/StoreWhitelistIpRequest.php` — ip_address, note
- `app/Http/Requests/Admin/StoreBlacklistPhoneRequest.php` — phone regex `^62[0-9]{9,12}$`, reason, duration_minutes optional
- `app/Http/Requests/Admin/StoreWhitelistPhoneRequest.php` — phone, note
- `app/Http/Resources/V1/BlacklistIpResource.php`
- `app/Http/Resources/V1/BlacklistPhoneResource.php`

#### Routes

```
GET    /api/v1/admin/blacklist/ips              — admin
POST   /api/v1/admin/blacklist/ips              — admin
GET    /api/v1/admin/blacklist/ips/{id}         — admin
DELETE /api/v1/admin/blacklist/ips/{id}         — admin
POST   /api/v1/admin/blacklist/ips/{id}/unban   — admin
(pola sama untuk /whitelist/ips, /blacklist/phones, /whitelist/phones)
```

---

### TASK-019 — Auto-Ban System

**Branch:** `feat/task-019-auto-ban`

#### Files

- `app/Services/RateLimitService.php` — implementasi lengkap (sebelumnya stub dari TASK-008)
  - `checkAndLogIp(ip, endpoint)` — log ke request_logs, hitung vs max_requests_per_minute, auto-ban jika melebihi
  - `checkAndLogPhone(phone, endpoint)` — log ke request_logs, hitung vs max_orders_per_phone dalam orders_window_hours, auto-ban jika melebihi
  - `getIpRequestCount(ip, minutes)` — count request_logs dalam X menit terakhir
  - `getPhoneOrderCount(phone, hours)` — count request_logs dalam X jam terakhir
  - `cleanOldLogs()` — delete request_logs lebih dari 24 jam
  - Skip jika IP/nomor di whitelist; skip jika auto_ban_enabled=false; duration_minutes=0 berarti permanent (banned_until=null)
- `routes/console.php` — `Schedule::call(fn() => app(RateLimitService::class)->cleanOldLogs())->daily()`

---

## PHASE 7 — Scheduler & Notifications

### TASK-020 — WhatsApp Service via WaAPI

**Branch:** `feat/task-020-whatsapp-service`

**Tujuan:** Service notifikasi WhatsApp via WaAPI self-hosted.

**Yang harus dibuat:**

- `app/Services/WhatsappService.php`
  - `send(string phone, string message): bool`
    - POST ke `config('services.waapi.url') . '/api/whatsapp/send-message'`
    - Header: `X-API-Key` dari `config('services.waapi.key')`
    - Body: `{ "number": $phone, "message": $message }`
    - Rate limit: 1 pesan/30 detik — tambah `sleep(1)` jika kirim banyak pesan sekaligus
    - Return `true` jika berhasil, `false` jika error
    - Log error on failure — tidak throw exception
  - `sendReminderToDriver(DriverOrder order): bool`
    - Build message berdasarkan `is_overnight` flag
    - One-day trip format (plain text):
      Reminder TanaOgi
      Kamu perlu menjemput penumpang hari ini:
      Nama    : {user_name}
      Lokasi  : {pickup_location}
      No HP   : {user_phone}
      Pastikan hadir tepat waktu.
    - PP/Menginap H-1 format (plain text):
      Reminder TanaOgi
      Besok kamu perlu menjemput penumpang:
      Nama    : {user_name}
      Lokasi  : {pickup_location}
      Tanggal : {return_date}
      No HP   : {user_phone}
      Pastikan hadir tepat waktu.
    - Panggil `send()` dengan `driver->phone`
- `config/services.php` — tambah:
  'waapi' => [
      'url' => env('WAAPI_URL'),
      'key' => env('WAAPI_KEY'),
  ],
- `.env.example` — tambah:
  WAAPI_URL=https://waapi.fyas.my.id
  WAAPI_KEY=wapi_your_key_here

**Referensi:** `docs/EXTERNAL.md`, `docs/SERVICES.md`

**Commit:** `feat: add WhatsApp notification service via WaAPI`

---

### TASK-021 — Scheduler Reminder Driver

**Branch:** `feat/task-021-scheduler-reminder`

#### Files

- `app/Services/ReminderService.php`
  - `processReminders()` — panggil kedua method di bawah
  - `processOneDayTripReminders()` — cek setiap menit; kirim X jam sebelum pickup (jam 17.00 default); untuk is_overnight=false; cek departure_reminded=false
  - `processOvernightReminders()` — hanya jalan jika `now()->format('H:i') === '07:00'`; untuk is_overnight=true di mana return_date=besok AND return_reminded=false
  - `sendReturnReminder(DriverOrder order)` — panggil WhatsappService::sendReminderToDriver(); jika sukses set return_reminded=true; jika gagal log error dan biarkan false (untuk retry)
- `app/Console/Commands/SendDriverReminders.php`
  - Signature: `reminders:send-driver`
  - Handle: `app(ReminderService::class)->processReminders()`
- `routes/console.php`:
```php
Schedule::command('reminders:send-driver')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::call(fn() => app(RateLimitService::class)->cleanOldLogs())
    ->daily()
    ->at('02:00');
```

---

## PHASE 8 — Polish & Deploy

### TASK-022 — Error Handling

**Branch:** `feat/task-022-error-handling`

#### Files

- `app/Exceptions/Handler.php` — global exception handler
  - `ModelNotFoundException` → 404, "Data tidak ditemukan"
  - `AuthenticationException` → 401, "Silakan login terlebih dahulu"
  - `AuthorizationException` → 403, "Anda tidak memiliki akses"
  - `ValidationException` → 422, "Data tidak valid", errors berisi field errors
  - `DriverNotAvailableException` → 422, "Driver tidak tersedia di tanggal tersebut"
  - `PhoneBlacklistedException` → 403, message dari exception atau "Nomor tidak dapat melakukan pemesanan"
  - `ThrottleRequestsException` → 429, "Terlalu banyak request. Coba lagi nanti"
  - Generic Exception → 500 production (tanpa detail), development tampilkan message
  - Semua pakai `ApiResponse::error()` — tidak ada emoji, tidak ada stack trace di production

---

### TASK-023 — Deploy Railway + R2 Production

**Branch:** `feat/task-023-production-deploy`

#### Files

- `Dockerfile` — multi-stage: dev stage (dengan composer dev deps), production stage (`--no-dev`, config/route/view:cache, expose 8080). Driver: pdo_mysql (bukan pdo_pgsql)
- `railway.json` — builder: DOCKERFILE, healthcheckPath: `/api/v1/health`, restartPolicyType: ON_FAILURE
- `docs/RAILWAY.md` — update pre-deploy checklist:
  - Semua Railway env vars di-set termasuk MAINTENANCE_SECRET
  - CLOUDFLARE_R2_* vars untuk production bucket
  - WAAPI_URL dan WAAPI_KEY di-set untuk production
  - APP_DEBUG=false di production
  - Jalankan `php artisan migrate --force` saat deploy
  - Ganti password admin default setelah deploy pertama

#### Config / Env (production)

```env
DB_CONNECTION=mysql
DB_HOST=${{tanaogi-db.MYSQLHOST}}
DB_PORT=${{tanaogi-db.MYSQLPORT}}
DB_DATABASE=${{tanaogi-db.MYSQLDATABASE}}
DB_USERNAME=${{tanaogi-db.MYSQLUSER}}
DB_PASSWORD=${{tanaogi-db.MYSQLPASSWORD}}
```
