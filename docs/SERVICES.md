# SERVICES.md — Daftar Service TanaOgi

Semua business logic wajib ada di service. Tidak boleh ada logic di Controller atau Repository.

---

## DestinationService

**File:** `app/Services/DestinationService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `getAll()` | - | `Collection` | Semua destinasi aktif |
| `getByRegency(?string $regencyId)` | regency UUID | `Collection` | Destinasi per kabupaten |
| `findBySlug(string $slug)` | slug | `Destination` | Detail destinasi |
| `create(array $data)` | data | `Destination` | Buat destinasi baru |
| `update(Destination $destination, array $data)` | model, data | `Destination` | Update destinasi |
| `delete(Destination $destination)` | model | `bool` | Hapus destinasi |
| `toggleActive(string $id)` | UUID | `Destination` | Toggle aktif/nonaktif |
| `uploadImages(Destination $destination, array $files)` | model, files | `Collection` | Upload foto |
| `deleteImage(string $imageId)` | UUID | `bool` | Hapus satu foto |
| `generateSlug(string $name)` | nama | `string` | Generate slug unik |

---

## RegencyService

**File:** `app/Services/RegencyService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `getAll()` | - | `Collection` | Semua kabupaten aktif |
| `findById(string $id)` | UUID | `Regency` | Detail kabupaten |
| `create(array $data)` | data | `Regency` | Buat kabupaten baru |
| `update(Regency $regency, array $data)` | model, data | `Regency` | Update kabupaten |
| `toggleActive(string $id)` | UUID | `Regency` | Toggle aktif/nonaktif |

---

## VehicleService

**File:** `app/Services/VehicleService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `getAll()` | - | `Collection` | Semua kendaraan aktif |
| `findById(string $id)` | UUID | `Vehicle` | Detail kendaraan |
| `create(array $data)` | data | `Vehicle` | Tambah kendaraan |
| `update(Vehicle $vehicle, array $data)` | model, data | `Vehicle` | Update kendaraan |
| `toggleActive(string $id)` | UUID | `Vehicle` | Toggle aktif/nonaktif |

---

## DriverService

**File:** `app/Services/DriverService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `getAll()` | - | `Collection` | Semua driver aktif |
| `findById(string $id)` | UUID | `Driver` | Detail driver |
| `create(array $data)` | data | `Driver` | Tambah driver |
| `update(Driver $driver, array $data)` | model, data | `Driver` | Update driver |
| `toggleActive(string $id)` | UUID | `Driver` | Toggle aktif/nonaktif |
| `getAvailable(string $departureDate, string $returnDate, string $vehicleType)` | tanggal, tipe | `Collection` | Driver available di kedua tanggal |
| `getSchedule(string $driverId, string $month, string $year)` | ID, bulan, tahun | `array` | Kalender driver |

---

## DriverOrderService

**File:** `app/Services/DriverOrderService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `getAll(?string $status)` | status filter | `Collection` | Semua order |
| `findById(string $id)` | UUID | `DriverOrder` | Detail order |
| `create(array $data)` | data | `DriverOrder` | Input order baru |
| `assignDriver(DriverOrder $order, string $driverId)` | order, UUID | `DriverOrder` | Assign driver + block jadwal |
| `updateStatus(DriverOrder $order, string $status)` | order, status | `DriverOrder` | Update status |
| `markReturnReminded(DriverOrder $order)` | order | `void` | Tandai reminder sudah dikirim |

---

## AccommodationService

**File:** `app/Services/AccommodationService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `getByDestination(string $destinationId)` | UUID | `Collection` | Penginapan per destinasi |
| `findById(string $id)` | UUID | `Accommodation` | Detail penginapan |
| `create(array $data)` | data | `Accommodation` | Tambah penginapan |
| `update(Accommodation $accommodation, array $data)` | model, data | `Accommodation` | Update penginapan |
| `delete(Accommodation $accommodation)` | model | `bool` | Hapus penginapan |
| `toggleActive(string $id)` | UUID | `Accommodation` | Toggle aktif/nonaktif |

---

## BlacklistService

**File:** `app/Services/BlacklistService.php`

Service utama untuk semua operasi blacklist dan whitelist.

### IP Address

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `isIpBlacklisted(string $ip)` | IP | `bool` | Cek apakah IP di-ban |
| `isIpWhitelisted(string $ip)` | IP | `bool` | Cek apakah IP di-whitelist |
| `banIp(string $ip, string $reason, bool $isAuto, ?int $durationMinutes)` | IP, alasan, auto, durasi | `BlacklistIp` | Ban IP |
| `unbanIp(string $id)` | UUID | `bool` | Unban IP |
| `whitelistIp(string $ip, string $note)` | IP, catatan | `WhitelistIp` | Whitelist IP |
| `removeIpFromWhitelist(string $id)` | UUID | `bool` | Hapus dari whitelist |
| `getBlacklistedIps()` | - | `Collection` | Semua IP yang di-ban |
| `getWhitelistedIps()` | - | `Collection` | Semua IP whitelist |

### Nomor WhatsApp

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `isPhoneBlacklisted(string $phone)` | nomor | `bool` | Cek apakah nomor di-ban |
| `isPhoneWhitelisted(string $phone)` | nomor | `bool` | Cek apakah nomor di-whitelist |
| `banPhone(string $phone, string $reason, bool $isAuto, ?int $durationMinutes)` | nomor, alasan, auto, durasi | `BlacklistPhone` | Ban nomor |
| `unbanPhone(string $id)` | UUID | `bool` | Unban nomor |
| `whitelistPhone(string $phone, string $note)` | nomor, catatan | `WhitelistPhone` | Whitelist nomor |
| `removePhoneFromWhitelist(string $id)` | UUID | `bool` | Hapus dari whitelist |
| `getBlacklistedPhones()` | - | `Collection` | Semua nomor yang di-ban |
| `getWhitelistedPhones()` | - | `Collection` | Semua nomor whitelist |

### Logic Prioritas

```
Request masuk
      ↓
Cek whitelist IP → jika ada, IZINKAN langsung (skip semua cek)
      ↓
Cek blacklist IP → jika ada dan aktif, TOLAK 403
      ↓
Lanjut proses normal
```

```
Order masuk dari nomor WA
      ↓
Cek whitelist phone → jika ada, IZINKAN langsung
      ↓
Cek blacklist phone → jika ada dan aktif, TOLAK 403
      ↓
Lanjut proses order
```

---

## RateLimitService

**File:** `app/Services/RateLimitService.php`

Menangani auto-ban berdasarkan rate limit.

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `checkAndLogIp(string $ip, string $endpoint)` | IP, endpoint | `void` | Log request + cek apakah perlu auto-ban |
| `checkAndLogPhone(string $phone, string $endpoint)` | nomor, endpoint | `void` | Log order + cek apakah perlu auto-ban nomor |
| `getIpRequestCount(string $ip, int $minutes)` | IP, menit | `int` | Hitung request IP dalam X menit terakhir |
| `getPhoneOrderCount(string $phone, int $hours)` | nomor, jam | `int` | Hitung order dari nomor dalam X jam terakhir |
| `cleanOldLogs()` | - | `void` | Hapus log lebih dari 24 jam (dipanggil scheduler) |

### Logic Auto-Ban IP

```php
public function checkAndLogIp(string $ip, string $endpoint): void
{
    // Skip jika IP di whitelist
    if ($this->blacklistService->isIpWhitelisted($ip)) {
        return;
    }

    // Log request
    RequestLog::create(['ip_address' => $ip, 'endpoint' => $endpoint]);

    // Cek apakah auto-ban aktif
    if (!$this->settingService->get('auto_ban_enabled')) {
        return;
    }

    // Hitung request dalam 1 menit terakhir
    $maxPerMinute = (int) $this->settingService->get('max_requests_per_minute');
    $count = $this->getIpRequestCount($ip, 1);

    if ($count > $maxPerMinute) {
        $duration = (int) $this->settingService->get('auto_ban_duration_minutes');
        $this->blacklistService->banIp(
            $ip,
            "Auto-ban: {$count} request dalam 1 menit (limit: {$maxPerMinute})",
            true,
            $duration ?: null
        );
    }
}
```

### Logic Auto-Ban Nomor WA

```php
public function checkAndLogPhone(string $phone, string $endpoint): void
{
    // Skip jika nomor di whitelist
    if ($this->blacklistService->isPhoneWhitelisted($phone)) {
        return;
    }

    // Log
    RequestLog::create(['ip_address' => request()->ip(), 'phone' => $phone, 'endpoint' => $endpoint]);

    if (!$this->settingService->get('auto_ban_enabled')) {
        return;
    }

    $maxOrders = (int) $this->settingService->get('max_orders_per_phone');
    $windowHours = (int) $this->settingService->get('orders_window_hours');
    $count = $this->getPhoneOrderCount($phone, $windowHours);

    if ($count > $maxOrders) {
        $duration = (int) $this->settingService->get('auto_ban_duration_minutes');
        $this->blacklistService->banPhone(
            $phone,
            "Auto-ban: {$count} order dalam {$windowHours} jam (limit: {$maxOrders})",
            true,
            $duration ?: null
        );
    }
}
```

---

## MaintenanceService

**File:** `app/Services/MaintenanceService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `enable()` | - | `void` | Aktifkan maintenance mode |
| `disable()` | - | `void` | Nonaktifkan maintenance mode |
| `isActive()` | - | `bool` | Cek status maintenance |

---

## WhatsappService

**File:** `app/Services/WhatsappService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `send(string $phone, string $message)` | nomor, pesan | `bool` | Kirim WA via WaAPI |
| `sendReminderToDriver(DriverOrder $order)` | order | `bool` | Kirim reminder ke driver |

---

## ReminderService

**File:** `app/Services/ReminderService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `processReminders()` | - | `void` | Main method scheduler |
| `processOneDayTripReminders()` | - | `void` | Reminder one-day trip |
| `processOvernightReminders()` | - | `void` | Reminder PP H-1 jam 07.00 |

---

## AuthService

**File:** `app/Services/AuthService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `login(array $credentials)` | email, password | `array` | Login, return token + user |
| `logout(User $user)` | user | `void` | Hapus current token |

---

## SettingService

**File:** `app/Services/SettingService.php`

| Method | Parameter | Return | Deskripsi |
|---|---|---|---|
| `get(string $key)` | key | `string` | Ambil satu setting |
| `getAll()` | - | `Collection` | Semua setting |
| `update(array $data)` | key-value pairs | `void` | Update multiple setting |
| `getAdminWhatsapp()` | - | `string` | Nomor WA admin |
| `getWaTemplate()` | - | `string` | Template WA |
| `getReminderHours()` | - | `int` | Jam sebelum reminder |
| `isAutoBanEnabled()` | - | `bool` | Status auto-ban |
| `getMaxRequestsPerMinute()` | - | `int` | Batas request/menit |
| `getMaxOrdersPerPhone()` | - | `int` | Batas order per nomor |
| `getOrdersWindowHours()` | - | `int` | Window waktu order |
| `getAutoBanDuration()` | - | `int` | Durasi auto-ban (menit) |
