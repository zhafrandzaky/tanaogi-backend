# TASKS_DETAIL.md — TanaOgi Backend Task Specifications

Dokumentasi teknis per task. Berisi detail apa yang harus dibuat, referensi docs, dan commit message.
Untuk prompt Claude Code lengkap lihat `~/tanaogi-workspace/PROMPTS.md`.
Untuk progress tracker lihat `docs/TASKS.md`.

---

## PHASE 1 — Project Setup

### TASK-001 — Laravel Init + Docker

**Branch:** `feat/task-001-laravel-init`

**Tujuan:** Inisialisasi project Laravel 13 dengan full Docker development environment.

**Yang harus dibuat:**

- `Dockerfile` — PHP 8.3-fpm, non-root www-data user
- `docker-compose.yml` — 3 services: app (PHP-FPM), nginx, mysql:8.0 (tidak ada MinIO — dev langsung ke R2)
- `docker/nginx/default.conf` — client_max_body_size 20M, PHP-FPM upstream ke app:9000
- `.env.example` — semua variable dari CLAUDE.md tanpa nilai sensitif
- `routes/api.php` — load api_v1.php dengan prefix v1
- `routes/api_v1.php` — stub kosong, siap diisi endpoint
- `GET /api/v1/health` — health check endpoint, return `{ "status": "ok", "service": "TanaOgi API" }`
- CORS dikonfigurasi dari `FRONTEND_URL` env

**Referensi:** `docs/DOCKER.md`, `~/tanaogi-workspace/CLAUDE.md`

**Commit:** `chore: initialize Laravel 13 with Docker environment`

---

### TASK-002 — Environment & Packages

**Branch:** `feat/task-002-env-packages`

**Tujuan:** Install semua package yang dibutuhkan dan konfigurasi environment.

**Yang harus dibuat/dikonfigurasi:**

- Install Laravel Sanctum via `php artisan install:api`
- Install Spatie Laravel Permission
- Install Flysystem S3 untuk R2 (`league/flysystem-aws-s3-v3`)
- `config/filesystems.php` — disk `r2` dengan driver S3, `use_path_style_endpoint: true`
- `config/cors.php` — `allowed_origins` dari `FRONTEND_URL` env
- `config/sanctum.php` — stateful domains dari env
- `app/Traits/ApiResponse.php` — method `success()`, `error()`, `paginated()`
- Publish Spatie migrations

**Referensi:** `docs/AUTHENTICATION.md`, `docs/STORAGE.md`, `~/tanaogi-workspace/CLAUDE.md`

**Commit:** `chore: install and configure packages`

---

### TASK-003 — Folder Structure & Base Classes

**Branch:** `feat/task-003-folder-structure`

**Tujuan:** Buat struktur folder clean architecture dan semua base/enum class.

**Yang harus dibuat:**

Folder structure sesuai `docs/ARCHITECTURE.md`:
- `app/Http/Controllers/Api/V1/` dan subfolder `Auth/`, `Admin/`, `Admin/Blacklist/`
- `app/Services/`
- `app/Repositories/` dan `app/Repositories/Contracts/`

Enum classes:
- `app/Enums/VehicleType.php` — `car`, `bus`
- `app/Enums/DriverOrderStatus.php` — `pending`, `confirmed`, `completed`, `cancelled`
- `app/Enums/AccommodationType.php` — `hotel`, `resort`, `homestay`

Exception classes:
- `app/Exceptions/DriverNotAvailableException.php`
- `app/Exceptions/PhoneBlacklistedException.php`

Routes:
- `routes/api.php` — load `api_v1.php` dengan prefix `v1`
- `routes/api_v1.php` — semua route stub

AppServiceProvider:
- Bind semua repository interface ke implementasinya

**Referensi:** `docs/ARCHITECTURE.md`, `docs/CODING_CONVENTIONS.md`

**Commit:** `chore: setup clean architecture folder structure and base classes`

---

## PHASE 2 — Database & Models

### TASK-004 — Core Migrations & Seeders

**Branch:** `feat/task-004-core-migrations`

**Tujuan:** Buat semua migrasi tabel utama dan seeder data awal.

**Migrations (urutan):**

1. `create_regencies_table` — CHAR(36) PK via HasUuids, name, slug unique, is_active, timestamps, indexes
2. `create_destinations_table` — CHAR(36) PK via HasUuids, FK regency_id, name, slug unique, description, ticket_price INT, facilities JSON, route_text, latitude DECIMAL(10,8), longitude DECIMAL(11,8), is_active, timestamps, semua index
3. `create_destination_images_table` — CHAR(36) PK via HasUuids, FK destination_id, path, url, order INT, timestamps, index destination_id
4. `create_accommodations_table` — CHAR(36) PK via HasUuids, FK destination_id, name, type, price_per_night INT, address TEXT, latitude, longitude, is_active, timestamps, index
5. `create_vehicles_table` — CHAR(36) PK via HasUuids, type, name, price_per_day INT, description, is_active, timestamps
6. `create_drivers_table` — CHAR(36) PK via HasUuids, name, phone VARCHAR(20), vehicle_type, is_active, timestamps
7. `create_driver_orders_table` — CHAR(36) PK via HasUuids, FK destination_id, vehicle_id, driver_id nullable, accommodation_id nullable, user_name, user_phone, departure_date DATE, return_date DATE, is_overnight BOOLEAN, pickup_location TEXT, status, departure_reminded BOOLEAN, return_reminded BOOLEAN, notes, timestamps, semua index
8. `create_settings_table` — CHAR(36) PK via HasUuids, key unique, value TEXT, timestamps

**Seeders:**

- `RegencySeeder` — 24 kabupaten/kota Sulawesi Selatan
- `VehicleSeeder` — Mobil (Avanza/Innova) type=car Rp500.000/hari, Bus Pariwisata type=bus Rp1.500.000/hari
- `SettingSeeder` — semua default settings dari `docs/DATABASE.md`
- `RoleSeeder` — role `admin` via Spatie Permission
- `AdminUserSeeder` — 1 akun admin default

**Referensi:** `docs/DATABASE.md`

**Commit:** `migration: create core tables with seeders`

---

### TASK-005 — Blacklist Migrations

**Branch:** `feat/task-005-blacklist-migrations`

**Tujuan:** Buat migrasi tabel blacklist, whitelist, dan request logs.

**Migrations:**

1. `create_blacklist_ips_table` — CHAR(36) PK via HasUuids, ip_address VARCHAR(45), reason TEXT, is_auto BOOLEAN, banned_at TIMESTAMP, banned_until TIMESTAMP nullable, is_active BOOLEAN, timestamps. Unique index pada (ip_address, is_active) — MySQL tidak support partial unique index. Index pada is_active.
2. `create_whitelist_ips_table` — CHAR(36) PK via HasUuids, ip_address VARCHAR(45) unique, note TEXT, is_active BOOLEAN, timestamps. Index pada ip_address.
3. `create_blacklist_phones_table` — CHAR(36) PK via HasUuids, phone VARCHAR(20), reason TEXT, is_auto BOOLEAN, banned_at TIMESTAMP, banned_until TIMESTAMP nullable, is_active BOOLEAN, timestamps. Unique index pada (phone, is_active) — MySQL tidak support partial unique index. Index pada is_active.
4. `create_whitelist_phones_table` — CHAR(36) PK via HasUuids, phone VARCHAR(20) unique, note TEXT, is_active BOOLEAN, timestamps.
5. `create_request_logs_table` — CHAR(36) PK via HasUuids, ip_address VARCHAR(45), phone VARCHAR(20) nullable, endpoint VARCHAR(255), created_at TIMESTAMP only (tidak ada updated_at). Composite index pada (ip_address, created_at) dan (phone, created_at).

**Referensi:** `docs/DATABASE.md`

**Commit:** `migration: create blacklist whitelist and request_logs tables`

---

### TASK-006 — Eloquent Models

**Branch:** `feat/task-006-eloquent-models`

**Tujuan:** Buat semua Eloquent model dengan relasi, cast, fillable yang benar.

**Models (semua pakai HasUuids trait):**

- `Regency` — fillable, casts is_active bool, hasMany destinations, scopeActive
- `Destination` — fillable, casts ticket_price int / latitude float / longitude float / is_active bool / facilities array, belongsTo regency, hasMany images/accommodations/driverOrders, scopeActive
- `DestinationImage` — fillable, belongsTo destination
- `Accommodation` — fillable, casts price_per_night int / is_active bool, belongsTo destination, scopeActive
- `Vehicle` — fillable, casts price_per_day int / is_active bool / type ke VehicleType enum, hasMany driverOrders, scopeActive
- `Driver` — fillable, casts is_active bool, hasMany driverOrders, scopeActive
- `DriverOrder` — fillable, casts departure_date / return_date / is_overnight / departure_reminded / return_reminded / status ke DriverOrderStatus enum, semua relasi belongsTo (driver dan accommodation nullable)
- `Setting` — fillable
- `BlacklistIp` — fillable, casts is_auto / is_active bool / banned_at datetime / banned_until datetime nullable, scopeActive
- `WhitelistIp` — fillable, casts is_active bool, scopeActive
- `BlacklistPhone` — fillable, casts sama dengan BlacklistIp, scopeActive
- `WhitelistPhone` — fillable, casts is_active bool, scopeActive
- `RequestLog` — fillable, timestamps false (hanya created_at)
- `User` — update: tambah HasApiTokens, HasRoles dari Spatie

**Aturan:** tidak ada business logic di model — hanya relasi, cast, scope.

**Referensi:** `docs/DATABASE.md`, `docs/CODING_CONVENTIONS.md`

**Commit:** `feat: create all Eloquent models with relationships and casts`

---

## PHASE 3 — Auth & Middleware

### TASK-007 — Authentication

**Branch:** `feat/task-007-authentication`

**Tujuan:** Implementasi autentikasi lengkap dengan Sanctum dan Spatie.

**Yang harus dibuat:**

- `app/Repositories/Contracts/UserRepositoryInterface.php`
- `app/Repositories/UserRepository.php` — findByEmail, findById, create
- `app/Services/AuthService.php`
  - `login(credentials)` — validasi credentials, cek role admin, generate Sanctum token, return token + user
  - `logout(User)` — hapus currentAccessToken
- `app/Http/Requests/Auth/LoginRequest.php` — email required, password required, pesan error Bahasa Indonesia
- `app/Http/Controllers/Api/V1/Auth/AuthController.php` — login, logout (maks 20 baris per method)
- `app/Http/Resources/V1/UserResource.php` — id, name, email, role, created_at (tanpa password)
- Bind UserRepositoryInterface di AppServiceProvider
- Routes: `POST /auth/login` public, `POST /auth/logout` auth:sanctum

**Referensi:** `docs/AUTHENTICATION.md`, `docs/API_VERSIONING.md`

**Commit:** `feat: add authentication with Sanctum and Spatie roles`

---

### TASK-008 — Security Middleware Stack

**Branch:** `feat/task-008-security-middleware`

**Tujuan:** Buat semua middleware security dan service pendukungnya.

**Services yang harus dibuat:**

- `app/Services/SettingService.php` — get, getAll, update, semua shortcut method
- `app/Services/MaintenanceService.php` — enable, disable, isActive
- `app/Services/BlacklistService.php` — semua method IP dan phone (ban, unban, whitelist, check)
- `app/Services/RateLimitService.php` — checkAndLogIp, checkAndLogPhone, getIpRequestCount, getPhoneOrderCount, cleanOldLogs

**Middleware yang harus dibuat:**

- `app/Http/Middleware/CheckMaintenance.php` — return 503 jika maintenance aktif, skip jika admin dengan token valid
- `app/Http/Middleware/CheckBlacklistIp.php` — whitelist IP lolos, blacklist IP return 403
- `app/Http/Middleware/RateLimitAndLog.php` — log request + cek threshold auto-ban, return 429 jika baru di-ban

**Registrasi middleware di** `bootstrap/app.php`:
- CheckMaintenance sebagai prepend (jalan pertama)
- CheckBlacklistIp dan RateLimitAndLog di-append ke group api

**Urutan:** CheckMaintenance → CheckBlacklistIp → RateLimitAndLog → throttle → auth:sanctum → role:admin

**Referensi:** `docs/ARCHITECTURE.md`, `docs/SERVICES.md`

**Commit:** `feat: add security middleware stack with blacklist and rate limiting`

---

## PHASE 4 — Core Features

### TASK-009 — Regencies CRUD

**Branch:** `feat/task-009-regencies-crud`

**Tujuan:** CRUD kabupaten/kota dengan endpoint public dan admin.

**Yang harus dibuat:**

- `app/Repositories/Contracts/RegencyRepositoryInterface.php`
- `app/Repositories/RegencyRepository.php` — findAll, findById, create, update, delete
- `app/Services/RegencyService.php` — getAll, findById, create, update, delete, toggleActive, generateSlug
- `app/Http/Requests/Admin/StoreRegencyRequest.php` — name required, is_active boolean
- `app/Http/Requests/Admin/UpdateRegencyRequest.php`
- `app/Http/Resources/V1/RegencyResource.php` — id, name, slug, is_active, created_at
- `app/Http/Controllers/Api/V1/RegencyController.php` — public: index
- `app/Http/Controllers/Api/V1/Admin/RegencyController.php` — index, store, show, update, destroy, toggleActive

**Routes:**
- `GET /regencies` — public
- `GET/POST /admin/regencies` — admin
- `GET/PUT/DELETE /admin/regencies/{id}` — admin
- `PATCH /admin/regencies/{id}/toggle-active` — admin

**Referensi:** `docs/INTEGRATION.md`, `docs/CODING_CONVENTIONS.md`

**Commit:** `feat: add regencies CRUD with public and admin endpoints`

---

### TASK-010 — Destinations CRUD + R2 Upload

**Branch:** `feat/task-010-destinations-crud`

**Tujuan:** CRUD destinasi wisata dengan upload foto langsung ke R2 Cloudflare (dev bucket: `tanaogi-storage-dev`).

**Yang harus dibuat:**

- `app/Repositories/Contracts/DestinationRepositoryInterface.php`
- `app/Repositories/DestinationRepository.php` — findAll, findByRegency, findBySlug, findById, create, update, delete
- `app/Services/DestinationService.php` — getAll, getByRegency, findBySlug, create, update, delete, toggleActive, uploadImages, deleteImage, generateSlug
- `app/Http/Requests/Admin/StoreDestinationRequest.php`
- `app/Http/Requests/Admin/UpdateDestinationRequest.php`
- `app/Http/Requests/Admin/UploadDestinationImageRequest.php` — images[] max 10, masing-masing max 2MB, format jpg/jpeg/png/webp
- `app/Http/Resources/V1/DestinationResource.php` — semua field termasuk images array of URL strings
- `app/Http/Controllers/Api/V1/DestinationController.php` — public: index (filter ?regency_id), show
- `app/Http/Controllers/Api/V1/Admin/DestinationController.php` — full CRUD + toggleActive + uploadImages + deleteImage

**Logic upload foto:**
- Path di R2: `destinations/{destination_id}/{uuid}.{ext}`
- Upload via `Storage::disk('r2')->put(path, contents, 'public')`
- URL publik via `Storage::disk('r2')->url(path)`
- Simpan path + url ke tabel `destination_images`
- Saat hapus: `Storage::disk('r2')->delete(image->path)` lalu hapus record DB
- Dev: foto masuk ke `tanaogi-storage-dev` bucket
- Tidak ada MinIO — semua developer langsung ke R2

**Routes:**
- `GET /destinations` — public (filter ?regency_id)
- `GET /destinations/{slug}` — public
- `GET/POST /admin/destinations` — admin
- `GET/PUT/DELETE /admin/destinations/{id}` — admin
- `PATCH /admin/destinations/{id}/toggle-active` — admin
- `POST /admin/destinations/{id}/images` — admin, multipart/form-data
- `DELETE /admin/destinations/{id}/images/{imageId}` — admin

**Referensi:** `docs/STORAGE.md`, `docs/INTEGRATION.md`

**Commit:** `feat: add destinations CRUD with R2 image upload`

---

### TASK-011 — Vehicles CRUD

**Branch:** `feat/task-011-vehicles-crud`

**Tujuan:** CRUD jenis kendaraan dengan endpoint public dan admin.

**Yang harus dibuat:**

- `app/Repositories/VehicleRepository.php` — standard CRUD
- `app/Services/VehicleService.php` — getAll, findById, create, update, delete, toggleActive
- `app/Http/Requests/Admin/StoreVehicleRequest.php`
- `app/Http/Requests/Admin/UpdateVehicleRequest.php`
- `app/Http/Resources/V1/VehicleResource.php` — id, type, name, price_per_day, description, is_active
- `app/Http/Controllers/Api/V1/VehicleController.php` — public: index
- `app/Http/Controllers/Api/V1/Admin/VehicleController.php` — full CRUD + toggleActive

**Routes:**
- `GET /vehicles` — public
- `GET/POST /admin/vehicles` — admin
- `GET/PUT/DELETE /admin/vehicles/{id}` — admin
- `PATCH /admin/vehicles/{id}/toggle-active` — admin

**Referensi:** `docs/INTEGRATION.md`

**Commit:** `feat: add vehicles CRUD with public and admin endpoints`

---

### TASK-012 — Accommodations CRUD

**Branch:** `feat/task-012-accommodations-crud`

**Tujuan:** CRUD penginapan rekomendasi dengan endpoint public per destinasi dan admin full control.

**Yang harus dibuat:**

- `app/Repositories/Contracts/AccommodationRepositoryInterface.php`
- `app/Repositories/AccommodationRepository.php`
- `app/Services/AccommodationService.php` — getByDestination (by slug), findById, create, update, delete, toggleActive
- `app/Http/Requests/Admin/StoreAccommodationRequest.php` — name, type enum, price_per_night, address, destination_id, optional lat/lng
- `app/Http/Requests/Admin/UpdateAccommodationRequest.php`
- `app/Http/Resources/V1/AccommodationResource.php` — id, name, type, price_per_night, address, is_active
- `app/Http/Controllers/Api/V1/AccommodationController.php` — public: byDestination
- `app/Http/Controllers/Api/V1/Admin/AccommodationController.php` — full CRUD + toggleActive

**Catatan penting:** Response WAJIB include field `address` karena dipakai untuk otomatis isi lokasi penjemputan driver.

**Routes:**
- `GET /destinations/{slug}/accommodations` — public
- `GET/POST /admin/accommodations` — admin
- `GET/PUT/DELETE /admin/accommodations/{id}` — admin
- `PATCH /admin/accommodations/{id}/toggle-active` — admin

**Referensi:** `docs/FEATURES.md`, `docs/INTEGRATION.md`

**Commit:** `feat: add accommodations CRUD with public endpoint by destination`

---

### TASK-013 — Drivers CRUD + Schedule

**Branch:** `feat/task-013-drivers-crud`

**Tujuan:** CRUD driver dengan cek availability dan kalender jadwal.

**Yang harus dibuat:**

- `app/Repositories/Contracts/DriverRepositoryInterface.php`
- `app/Repositories/DriverRepository.php` — findAll, findById, create, update, findAvailable(departureDate, returnDate, vehicleType)
  - `findAvailable`: query driver NOT IN driver_orders dimana driver_id terisi DAN status NOT IN (completed, cancelled) DAN (departure_date = date OR return_date = date)
- `app/Services/DriverService.php` — getAll, findById, create, update, delete, toggleActive, getAvailable, getSchedule
  - `getSchedule(driverId, month, year)` — query driver_orders bulan tersebut, return blocked_dates array dan orders array
- `app/Http/Requests/Admin/StoreDriverRequest.php` — name, phone regex `^62[0-9]{9,12}$`, vehicle_type enum car/bus
- `app/Http/Requests/Admin/UpdateDriverRequest.php`
- `app/Http/Resources/V1/DriverResource.php`
- `app/Http/Resources/V1/DriverScheduleResource.php` — driver_id, driver_name, month, year, blocked_dates, orders
- `app/Http/Controllers/Api/V1/Admin/DriverController.php` — full CRUD + toggleActive + schedule

**Routes:**
- `GET/POST /admin/drivers` — admin
- `GET/PUT/DELETE /admin/drivers/{id}` — admin
- `PATCH /admin/drivers/{id}/toggle-active` — admin
- `GET /admin/drivers/{id}/schedule?month=6&year=2025` — admin

**Referensi:** `docs/SERVICES.md`, `docs/INTEGRATION.md`, `docs/DATABASE.md`

**Commit:** `feat: add drivers CRUD with availability check and schedule`

---

### TASK-014 — Driver Orders CRUD + Assign Driver

**Branch:** `feat/task-014-driver-orders`

**Tujuan:** CRUD pemesanan driver dengan logic assign driver dan cek blacklist nomor WA.

**Yang harus dibuat:**

- `app/Repositories/Contracts/DriverOrderRepositoryInterface.php`
- `app/Repositories/DriverOrderRepository.php` — findAll(?status), findById, create, assignDriver, updateStatus, markReturnReminded
- `app/Services/DriverOrderService.php`
  - `create(array data)`:
    1. Cek whitelist phone → jika tidak ada di whitelist, cek blacklist → throw PhoneBlacklistedException jika di-ban
    2. Log phone via RateLimitService::checkAndLogPhone()
    3. Cek lagi setelah log (mungkin baru di-ban) → throw PhoneBlacklistedException
    4. Buat order di DB
  - `assignDriver(DriverOrder, driverId)`:
    1. Cari driver, cek aktif
    2. getAvailable(departure_date, return_date, vehicle_type) → cek driverId ada di result
    3. Throw DriverNotAvailableException jika tidak available
    4. Set driver_id, update status ke confirmed
  - `updateStatus`, `markReturnReminded`, `getAll(?status)`, `findById`
- `app/Http/Requests/Admin/StoreDriverOrderRequest.php`
- `app/Http/Requests/Admin/UpdateDriverOrderRequest.php` — driver_id, status
- `app/Http/Resources/V1/DriverOrderResource.php` — semua field, nested destination/vehicle/driver/accommodation
- `app/Http/Controllers/Api/V1/Admin/DriverOrderController.php` — index (filter ?status), store, show, update, destroy

**Routes:**
- `GET/POST /admin/driver-orders` — admin
- `GET/PUT/DELETE /admin/driver-orders/{id}` — admin

**Referensi:** `docs/FEATURES.md`, `docs/SERVICES.md`, `docs/INTEGRATION.md`

**Commit:** `feat: add driver orders CRUD with assign driver and phone blacklist check`

---

## PHASE 5 — Admin Full Control

### TASK-015 — Settings Endpoint

**Branch:** `feat/task-015-settings-endpoint`

**Tujuan:** Endpoint settings untuk public (WA info) dan admin full control.

**Yang harus dibuat:**

- `app/Http/Controllers/Api/V1/SettingController.php` — public: `GET /settings/whatsapp` return admin_whatsapp + wa_template
- `app/Http/Controllers/Api/V1/Admin/SettingController.php` — `GET /admin/settings`, `PUT /admin/settings`
- `app/Http/Requests/Admin/UpdateSettingRequest.php` — validasi semua setting key

**Routes:**
- `GET /settings/whatsapp` — public
- `GET /admin/settings` — admin
- `PUT /admin/settings` — admin

**Referensi:** `docs/INTEGRATION.md`

**Commit:** `feat: add settings endpoints for public and admin`

---

### TASK-016 — Admin Users CRUD

**Branch:** `feat/task-016-admin-users`

**Tujuan:** Manajemen akun admin dari dashboard.

**Yang harus dibuat:**

- `app/Http/Controllers/Api/V1/Admin/UserController.php` — index, store, show, update, destroy
- `app/Http/Requests/Admin/StoreUserRequest.php` — name, email unique, password, password_confirmation
- `app/Http/Requests/Admin/UpdateUserRequest.php` — name, email
- Auto-assign role admin saat store
- Validasi: tidak bisa hapus akun sendiri (`$request->user()->id !== $user->id`)

**Routes:**
- `GET/POST /admin/users` — admin
- `GET/PUT/DELETE /admin/users/{id}` — admin

**Referensi:** `docs/INTEGRATION.md`, `docs/AUTHENTICATION.md`

**Commit:** `feat: add admin users CRUD`

---

### TASK-017 — Maintenance Mode

**Branch:** `feat/task-017-maintenance-mode`

**Tujuan:** Toggle maintenance mode dari dashboard admin.

**Yang harus dibuat:**

- `app/Http/Controllers/Api/V1/MaintenanceController.php` — public: `GET /maintenance/status`
- `app/Http/Controllers/Api/V1/Admin/MaintenanceController.php` — status, enable, disable
  - `enable`: `Artisan::call('down', ['--secret' => config('app.maintenance_secret')])`, update setting `is_maintenance=true`
  - `disable`: `Artisan::call('up')`, update setting `is_maintenance=false`
- Tambah `MAINTENANCE_SECRET` di `.env.example`

**Routes:**
- `GET /maintenance/status` — public
- `GET /admin/maintenance/status` — admin
- `POST /admin/maintenance/enable` — admin
- `POST /admin/maintenance/disable` — admin

**Referensi:** `docs/INTEGRATION.md`, `docs/ARCHITECTURE.md`

**Commit:** `feat: add maintenance mode toggle with admin bypass`

---

## PHASE 6 — Blacklist & Security

### TASK-018 — Blacklist Whitelist Full Control

**Branch:** `feat/task-018-blacklist-whitelist`

**Tujuan:** Admin full control untuk blacklist dan whitelist IP dan nomor WA.

**Yang harus dibuat:**

- `app/Http/Controllers/Api/V1/Admin/Blacklist/BlacklistIpController.php` — index, store, show, destroy, unban
- `app/Http/Controllers/Api/V1/Admin/Blacklist/WhitelistIpController.php` — index, store, destroy
- `app/Http/Controllers/Api/V1/Admin/Blacklist/BlacklistPhoneController.php` — index, store, show, destroy, unban
- `app/Http/Controllers/Api/V1/Admin/Blacklist/WhitelistPhoneController.php` — index, store, destroy
- Requests: StoreBlacklistIpRequest (ip_address, reason, duration_minutes opsional), StoreWhitelistIpRequest (ip_address, note), StoreBlacklistPhoneRequest (phone regex 62xxx, reason, duration_minutes opsional), StoreWhitelistPhoneRequest (phone, note)
- Resources: BlacklistIpResource, BlacklistPhoneResource

**Routes:**
- `GET/POST /admin/blacklist/ips` — admin
- `GET/DELETE /admin/blacklist/ips/{id}` — admin
- `POST /admin/blacklist/ips/{id}/unban` — admin
- (sama untuk whitelist/ips, blacklist/phones, whitelist/phones)

**Referensi:** `docs/INTEGRATION.md`, `docs/SERVICES.md`

**Commit:** `feat: add full admin control for IP and phone blacklist whitelist`

---

### TASK-019 — Auto-Ban System

**Branch:** `feat/task-019-auto-ban`

**Tujuan:** Implementasi penuh RateLimitService untuk auto-ban IP dan nomor WA.

**Yang harus diimplementasi di** `RateLimitService.php`:

- `checkAndLogIp(ip, endpoint)` — log ke request_logs, hitung count vs max_requests_per_minute, auto-ban jika melebihi
- `checkAndLogPhone(phone, endpoint)` — log ke request_logs, hitung count vs max_orders_per_phone dalam orders_window_hours, auto-ban jika melebihi
- `getIpRequestCount(ip, minutes)` — hitung request_logs dalam X menit terakhir
- `getPhoneOrderCount(phone, hours)` — hitung request_logs (kolom phone) dalam X jam terakhir
- `cleanOldLogs()` — hapus request_logs lebih dari 24 jam

**Logic auto-ban:**
- Skip jika IP/phone di whitelist
- Skip jika `auto_ban_enabled = false`
- Jika count > threshold: panggil BlacklistService::banIp/banPhone dengan is_auto=true dan durasi dari setting
- `auto_ban_duration_minutes = 0` berarti permanen (banned_until = null)

**Scheduler:** Daftarkan `cleanOldLogs` di `routes/console.php` — `Schedule::call(...)->daily()`

**Referensi:** `docs/SERVICES.md`, `docs/DATABASE.md`

**Commit:** `feat: complete auto-ban system for IP and phone rate limiting`

---

## PHASE 7 — Scheduler & Notifications

### TASK-020 — WhatsApp Service via Fonnte

**Branch:** `feat/task-020-whatsapp-service`

**Tujuan:** Service notifikasi WhatsApp via Fonnte API.

**Yang harus dibuat:**

- `app/Services/WhatsappService.php`
  - `send(string phone, string message): bool` — POST ke `https://api.fonnte.com/send`, header Authorization dari FONNTE_TOKEN, return true jika berhasil, false jika error (log error, tidak throw exception)
  - `sendReminderToDriver(DriverOrder order): bool` — build pesan berdasarkan is_overnight, panggil send()
- `config/services.php` — tambah `fonnte.token` dari env
- `.env.example` — tambah `FONNTE_TOKEN=`

**Format pesan reminder (plain text, tanpa emoji di response API tapi pesan WA boleh):**

One-day trip:
```
Reminder TanaOgi

Kamu perlu menjemput penumpang hari ini:

Nama    : {user_name}
Lokasi  : {pickup_location}
No HP   : {user_phone}

Pastikan hadir tepat waktu.
```

PP/Menginap (H-1):
```
Reminder TanaOgi

Besok kamu perlu menjemput penumpang:

Nama    : {user_name}
Lokasi  : {pickup_location}
Tanggal : {return_date}
No HP   : {user_phone}

Pastikan hadir tepat waktu.
```

**Referensi:** `docs/EXTERNAL.md`, `docs/SERVICES.md`

**Commit:** `feat: add WhatsApp notification service via Fonnte API`

---

### TASK-021 — Scheduler Reminder Driver

**Branch:** `feat/task-021-scheduler-reminder`

**Tujuan:** Laravel Scheduler untuk kirim reminder otomatis ke driver.

**Yang harus dibuat:**

- `app/Services/ReminderService.php`
  - `processReminders()` — panggil kedua method di bawah
  - `processOneDayTripReminders()` — cek setiap menit, kirim X jam sebelum return_date jam 17.00, untuk is_overnight=false
  - `processOvernightReminders()` — cek jam 07.00 saja (`if now()->format('H:i') !== '07:00' return`), untuk is_overnight=true dimana return_date = besok DAN return_reminded=false
  - `sendReturnReminder(DriverOrder order)` — panggil WhatsappService::sendReminderToDriver(), jika berhasil set return_reminded=true, jika gagal log error dan biarkan false untuk retry

- `app/Console/Commands/SendDriverReminders.php`
  - Signature: `reminders:send-driver`
  - Handle: `app(ReminderService::class)->processReminders()`

**Scheduler di** `routes/console.php`:
```php
Schedule::command('reminders:send-driver')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::call(fn() => app(RateLimitService::class)->cleanOldLogs())->daily()->at('02:00');
```

**Referensi:** `docs/SCHEDULER.md`, `docs/SERVICES.md`

**Commit:** `feat: add scheduler reminder system for driver pickup notifications`

---

## PHASE 8 — Polish & Deploy

### TASK-022 — Error Handling

**Branch:** `feat/task-022-error-handling`

**Tujuan:** Global exception handler — semua error return format standar, tanpa emoji, tanpa stack trace di production.

**Update** `app/Exceptions/Handler.php`:

| Exception | HTTP Status | Message |
|---|---|---|
| ModelNotFoundException | 404 | Data tidak ditemukan |
| AuthenticationException | 401 | Silakan login terlebih dahulu |
| AuthorizationException | 403 | Anda tidak memiliki akses |
| ValidationException | 422 | Data tidak valid (+ errors field) |
| DriverNotAvailableException | 422 | Driver tidak tersedia di tanggal tersebut |
| PhoneBlacklistedException | 403 | Nomor tidak dapat melakukan pemesanan |
| ThrottleRequestsException | 429 | Terlalu banyak request. Coba lagi nanti |
| Exception (generic) | 500 | Terjadi kesalahan pada server (production) / pesan asli (development) |

- Semua response pakai `ApiResponse::error()` format
- Tidak ada emoji di pesan manapun
- Tidak ada stack trace di production (APP_DEBUG=false)

**Referensi:** `docs/API_VERSIONING.md`

**Commit:** `feat: add global exception handler with standard error responses`

---

### TASK-023 — Deploy Railway + R2 Production

**Branch:** `feat/task-023-production-deploy`

**Tujuan:** Siapkan konfigurasi production untuk Railway dan R2 Cloudflare.

**Yang harus dibuat/diupdate:**

- `Dockerfile` — production stage: `--no-dev`, `config:cache`, `route:cache`, `view:cache`, expose port 8080, gunakan `pdo_mysql` (bukan `pdo_pgsql`)
- `railway.json` — builder DOCKERFILE, healthcheckPath `/api/v1/health`, restartPolicyType ON_FAILURE
- `.env.example` — pastikan semua production vars dari `docs/RAILWAY.md` ada dengan MySQL vars:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=${{tanaogi-db.MYSQLHOST}}`
  - `DB_PORT=${{tanaogi-db.MYSQLPORT}}`
  - `DB_DATABASE=${{tanaogi-db.MYSQLDATABASE}}`
  - `DB_USERNAME=${{tanaogi-db.MYSQLUSER}}`
  - `DB_PASSWORD=${{tanaogi-db.MYSQLPASSWORD}}`

**Pre-deploy checklist (dokumentasikan di** `docs/RAILWAY.md`):**
- Semua Railway env vars sudah diset termasuk MAINTENANCE_SECRET
- MySQL service sudah dibuat di Railway dan env vars ter-link
- `CLOUDFLARE_R2_*` vars sudah diset untuk bucket production
- `FONNTE_TOKEN` sudah diset untuk production
- `APP_DEBUG=false` di production
- `php artisan migrate --force` dijalankan saat deploy
- Password admin default sudah diganti setelah first deploy

**Referensi:** `docs/RAILWAY.md`, `docs/STORAGE.md`

**Commit:** `chore: prepare production Dockerfile and Railway deployment config`
