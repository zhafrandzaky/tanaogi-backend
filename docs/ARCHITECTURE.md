# ARCHITECTURE.md — Arsitektur TanaOgi Backend

## Prinsip Utama

TanaOgi backend menggunakan **Clean Architecture** dengan pemisahan layer yang ketat.
Setiap layer hanya boleh berkomunikasi dengan layer di bawahnya — tidak boleh melompat layer.

---

## Layer Diagram

```
┌─────────────────────────────────────────────────────┐
│                    HTTP Layer                        │
│    Routes · Middleware · FormRequest · ApiResource   │
└─────────────────────┬───────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│                 Controller Layer                     │
│   Terima request → panggil service → return resource │
│              Maksimal 30 baris/method                │
└─────────────────────┬───────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│                  Service Layer                       │
│            Semua business logic di sini             │
│         Tidak boleh return Response/Json            │
└─────────────────────┬───────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│               Repository Layer                       │
│          Semua query Eloquent di sini               │
│            Tidak boleh ada logic bisnis             │
└─────────────────────┬───────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────┐
│                  Model Layer                         │
│       Relasi · Cast · Scope · Fillable              │
│           Tidak boleh ada business logic            │
└─────────────────────────────────────────────────────┘
```

---

## Middleware Stack

Urutan middleware dieksekusi dari atas ke bawah untuk setiap request:

```
Request masuk
      ↓
1. CheckMaintenance      → cek apakah mode maintenance aktif
      ↓
2. CheckBlacklistIp      → cek apakah IP di-ban
      ↓
3. RateLimitAndLog       → log request + cek auto-ban threshold
      ↓
4. throttle:api          → Laravel built-in rate limit (fallback)
      ↓
5. auth:sanctum          → verifikasi token (hanya route protected)
      ↓
6. role:admin            → verifikasi role admin (hanya route admin)
      ↓
Controller
```

### CheckMaintenance

```php
// app/Http/Middleware/CheckMaintenance.php
public function handle(Request $request, Closure $next): Response
{
    // Admin dengan token tetap bisa akses saat maintenance
    if ($request->bearerToken() && $request->user()?->hasRole('admin')) {
        return $next($request);
    }

    if ($this->maintenanceService->isActive()) {
        return response()->json([
            'success' => false,
            'message' => 'Website sedang dalam mode maintenance',
            'errors'  => null,
        ], 503);
    }

    return $next($request);
}
```

### CheckBlacklistIp

```php
// app/Http/Middleware/CheckBlacklistIp.php
public function handle(Request $request, Closure $next): Response
{
    $ip = $request->ip();

    // Whitelist selalu lolos
    if ($this->blacklistService->isIpWhitelisted($ip)) {
        return $next($request);
    }

    // Blacklist ditolak
    if ($this->blacklistService->isIpBlacklisted($ip)) {
        return response()->json([
            'success' => false,
            'message' => 'Akses ditolak',
            'errors'  => null,
        ], 403);
    }

    return $next($request);
}
```

### RateLimitAndLog

```php
// app/Http/Middleware/RateLimitAndLog.php
public function handle(Request $request, Closure $next): Response
{
    $this->rateLimitService->checkAndLogIp(
        $request->ip(),
        $request->path()
    );

    // Setelah checkAndLogIp, IP mungkin baru saja di-ban
    // Cek ulang sebelum lanjut
    if ($this->blacklistService->isIpBlacklisted($request->ip())) {
        return response()->json([
            'success' => false,
            'message' => 'Terlalu banyak request. Coba lagi nanti',
            'errors'  => null,
        ], 429);
    }

    return $next($request);
}
```

### CheckBlacklistPhone (di DriverOrderController)

Pengecekan nomor WA dilakukan di `DriverOrderService::create()`, bukan middleware, karena nomor WA baru tersedia setelah body request diparse:

```php
// DriverOrderService::create()
public function create(array $data): DriverOrder
{
    $phone = $data['user_phone'];

    // Whitelist selalu lolos
    if (!$this->blacklistService->isPhoneWhitelisted($phone)) {
        // Cek blacklist
        if ($this->blacklistService->isPhoneBlacklisted($phone)) {
            throw new PhoneBlacklistedException('Nomor tidak dapat melakukan pemesanan');
        }

        // Log dan cek auto-ban
        $this->rateLimitService->checkAndLogPhone($phone, 'driver-orders');

        // Cek lagi setelah log (mungkin baru saja di-ban)
        if ($this->blacklistService->isPhoneBlacklisted($phone)) {
            throw new PhoneBlacklistedException('Terlalu banyak pemesanan. Coba lagi nanti');
        }
    }

    return $this->driverOrderRepository->create($data);
}
```

---

## Tanggung Jawab Per Layer

### Routes (`routes/api_v1.php`)
- Mendefinisikan endpoint dan middleware
- Tidak ada logic apapun

### Middleware
- `CheckMaintenance` — cek maintenance mode
- `CheckBlacklistIp` — cek IP di blacklist
- `RateLimitAndLog` — log + auto-ban IP
- `auth:sanctum` — verifikasi token
- `role:admin` — verifikasi role Spatie

### FormRequest
- Validasi semua input
- Authorization logic
- Pesan error dalam Bahasa Indonesia

### Controller
- Terima request yang sudah divalidasi
- Panggil satu service method
- Return ApiResource
- Maksimal 30 baris per method

### Service
- Semua logic bisnis
- Panggil repository untuk data
- Tidak boleh return Response/JsonResponse
- Tidak boleh ada query Eloquent langsung

### Repository
- Semua interaksi dengan database
- Implementasi dari Contract (interface)
- Tidak boleh ada logic bisnis

### Model
- Relasi, cast, scope, fillable
- Tidak boleh ada business logic

---

## Struktur Folder Lengkap

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── V1/
│   │           ├── Auth/
│   │           │   └── AuthController.php
│   │           ├── Admin/
│   │           │   ├── RegencyController.php
│   │           │   ├── DestinationController.php
│   │           │   ├── DriverController.php
│   │           │   ├── DriverOrderController.php
│   │           │   ├── AccommodationController.php
│   │           │   ├── VehicleController.php
│   │           │   ├── UserController.php
│   │           │   ├── SettingController.php
│   │           │   ├── MaintenanceController.php
│   │           │   └── Blacklist/
│   │           │       ├── BlacklistIpController.php
│   │           │       ├── BlacklistPhoneController.php
│   │           │       ├── WhitelistIpController.php
│   │           │       └── WhitelistPhoneController.php
│   │           ├── DestinationController.php
│   │           ├── RegencyController.php
│   │           ├── VehicleController.php
│   │           └── AccommodationController.php
│   ├── Requests/
│   ├── Resources/V1/
│   └── Middleware/
│       ├── CheckMaintenance.php
│       ├── CheckBlacklistIp.php
│       └── RateLimitAndLog.php
├── Services/
│   ├── DestinationService.php
│   ├── RegencyService.php
│   ├── DriverService.php
│   ├── DriverOrderService.php
│   ├── AccommodationService.php
│   ├── VehicleService.php
│   ├── BlacklistService.php
│   ├── RateLimitService.php
│   ├── MaintenanceService.php
│   ├── WhatsappService.php
│   ├── ReminderService.php
│   ├── AuthService.php
│   └── SettingService.php
├── Repositories/
│   └── Contracts/
├── Models/
│   ├── User.php
│   ├── Regency.php
│   ├── Destination.php
│   ├── DestinationImage.php
│   ├── Vehicle.php
│   ├── Driver.php
│   ├── DriverOrder.php
│   ├── Accommodation.php
│   ├── Setting.php
│   ├── BlacklistIp.php
│   ├── WhitelistIp.php
│   ├── BlacklistPhone.php
│   ├── WhitelistPhone.php
│   └── RequestLog.php
├── Enums/
│   ├── VehicleType.php
│   ├── DriverOrderStatus.php
│   └── AccommodationType.php
├── Exceptions/
│   ├── Handler.php
│   ├── PhoneBlacklistedException.php
│   └── DriverNotAvailableException.php
└── Traits/
    └── ApiResponse.php
```

---

## Registrasi Middleware

`bootstrap/app.php` (Laravel 13):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(CheckMaintenance::class);

    $middleware->appendToGroup('api', [
        CheckBlacklistIp::class,
        RateLimitAndLog::class,
    ]);
})
```
