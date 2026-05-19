# DATABASE.md — Skema Database TanaOgi

## Ketentuan Umum

- **Database**: MySQL 8.0
- **Primary Key**: CHAR(36) UUID semua tabel via `HasUuids` trait Laravel (tidak ada auto-increment integer)
- **Timestamps**: `created_at`, `updated_at` di semua tabel
- **File Storage**: Cloudflare R2 — URL foto disimpan di DB, file di R2

---

## ERD Ringkas

```
regencies
    └── destinations
            ├── destination_images   (URL foto di R2)
            ├── accommodations
            └── driver_orders (via vehicles & drivers)

users (admin)
drivers
    └── driver_orders
            ├── destinations
            ├── vehicles
            └── accommodations (nullable)

vehicles
settings

blacklist_ips
whitelist_ips
blacklist_phones
whitelist_phones
request_logs
```

---

## Tabel Detail

### `regencies` — Kabupaten/Kota

```sql
id          CHAR(36) PRIMARY KEY    -- UUID via HasUuids trait
name        VARCHAR(255) NOT NULL
slug        VARCHAR(255) UNIQUE NOT NULL
is_active   BOOLEAN DEFAULT true
created_at  TIMESTAMP
updated_at  TIMESTAMP
```

---

### `destinations` — Destinasi Wisata

```sql
id            CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
regency_id    CHAR(36) FK → regencies.id
name          VARCHAR(255) NOT NULL
slug          VARCHAR(255) UNIQUE NOT NULL
description   TEXT NOT NULL
ticket_price  INTEGER DEFAULT 0
facilities    JSON
route_text    TEXT
latitude      DECIMAL(10,8)
longitude     DECIMAL(11,8)
is_active     BOOLEAN DEFAULT true
created_at    TIMESTAMP
updated_at    TIMESTAMP
```

**Index:**
```sql
INDEX idx_destinations_regency_id ON destinations(regency_id)
INDEX idx_destinations_slug ON destinations(slug)
INDEX idx_destinations_is_active ON destinations(is_active)
```

---

### `destination_images` — Foto Destinasi

File foto disimpan di **Cloudflare R2**, hanya URL-nya yang disimpan di DB.

```sql
id             CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
destination_id CHAR(36) FK → destinations.id
path           VARCHAR(500) NOT NULL
               -- path di R2: "destinations/{destination_id}/{uuid}.jpg"
url            VARCHAR(500) NOT NULL
               -- URL publik R2: "https://storage.tanaogi.com/destinations/..."
order          INTEGER DEFAULT 0
               -- urutan tampil foto (0 = thumbnail utama)
created_at     TIMESTAMP
updated_at     TIMESTAMP
```

**Index:**
```sql
INDEX idx_destination_images_destination_id ON destination_images(destination_id)
```

---

### `accommodations` — Rekomendasi Penginapan

```sql
id              CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
destination_id  CHAR(36) FK → destinations.id
name            VARCHAR(255) NOT NULL
type            VARCHAR(50) NOT NULL      -- hotel | resort | homestay
price_per_night INTEGER NOT NULL
address         TEXT NOT NULL
latitude        DECIMAL(10,8)
longitude       DECIMAL(11,8)
is_active       BOOLEAN DEFAULT true
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

### `vehicles` — Jenis Kendaraan

```sql
id            CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
type          VARCHAR(50) NOT NULL        -- car | bus
name          VARCHAR(255) NOT NULL
price_per_day INTEGER NOT NULL
description   TEXT
is_active     BOOLEAN DEFAULT true
created_at    TIMESTAMP
updated_at    TIMESTAMP
```

---

### `drivers` — Data Driver

```sql
id           CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
name         VARCHAR(255) NOT NULL
phone        VARCHAR(20) NOT NULL         -- format: 628xxx
vehicle_type VARCHAR(50) NOT NULL         -- car | bus
is_active    BOOLEAN DEFAULT true
created_at   TIMESTAMP
updated_at   TIMESTAMP
```

---

### `driver_orders` — Pemesanan Driver

```sql
id                CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
destination_id    CHAR(36) FK → destinations.id
vehicle_id        CHAR(36) FK → vehicles.id
driver_id         CHAR(36) FK → drivers.id (nullable)
accommodation_id  CHAR(36) FK → accommodations.id (nullable)
user_name         VARCHAR(255) NOT NULL
user_phone        VARCHAR(20) NOT NULL
departure_date    DATE NOT NULL
return_date       DATE NOT NULL
is_overnight      BOOLEAN DEFAULT false
pickup_location   TEXT NOT NULL
status            VARCHAR(50) DEFAULT 'pending'
departure_reminded BOOLEAN DEFAULT false
return_reminded   BOOLEAN DEFAULT false
notes             TEXT
created_at        TIMESTAMP
updated_at        TIMESTAMP
```

**Index:**
```sql
INDEX idx_driver_orders_driver_id ON driver_orders(driver_id)
INDEX idx_driver_orders_departure_date ON driver_orders(departure_date)
INDEX idx_driver_orders_return_date ON driver_orders(return_date)
INDEX idx_driver_orders_status ON driver_orders(status)
INDEX idx_driver_orders_user_phone ON driver_orders(user_phone)
```

**Query cek availability driver:**
```sql
SELECT * FROM drivers
WHERE id NOT IN (
    SELECT driver_id FROM driver_orders
    WHERE driver_id IS NOT NULL
    AND status NOT IN ('completed', 'cancelled')
    AND (departure_date = :date OR return_date = :date)
)
AND is_active = true
AND vehicle_type = :vehicle_type
```

---

### `users` — Admin

```sql
id         CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
name       VARCHAR(255) NOT NULL
email      VARCHAR(255) UNIQUE NOT NULL
password   VARCHAR(255) NOT NULL
created_at TIMESTAMP
updated_at TIMESTAMP
```

Role dikelola via Spatie Laravel Permission.

---

### `settings` — Pengaturan Aplikasi

```sql
id         CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
key        VARCHAR(255) UNIQUE NOT NULL
value      TEXT NOT NULL
created_at TIMESTAMP
updated_at TIMESTAMP
```

**Data settings:**

| key | default | keterangan |
|---|---|---|
| `admin_whatsapp` | `628xxx` | Nomor WA admin |
| `wa_template_driver_order` | template | Template pesan WA |
| `reminder_hours_before_pickup` | `3` | Jam sebelum reminder one-day |
| `max_requests_per_minute` | `60` | Batas request per IP per menit |
| `max_orders_per_phone` | `3` | Batas order per nomor WA |
| `orders_window_hours` | `24` | Window waktu hitung order (jam) |
| `auto_ban_enabled` | `true` | Auto-ban aktif atau tidak |
| `auto_ban_duration_minutes` | `60` | Durasi auto-ban (menit, 0 = permanen) |
| `is_maintenance` | `false` | Status maintenance mode |

---

### `blacklist_ips` — IP yang Diblokir

```sql
id           CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
ip_address   VARCHAR(45) NOT NULL
reason       TEXT
is_auto      BOOLEAN DEFAULT false
banned_at    TIMESTAMP NOT NULL
banned_until TIMESTAMP                    -- null = permanen
is_active    BOOLEAN DEFAULT true
created_at   TIMESTAMP
updated_at   TIMESTAMP
```

**Index:**
```sql
-- MySQL tidak support partial unique index (WHERE clause)
-- Gunakan regular unique index pada (ip_address, is_active)
-- dan handle soft-delete pattern di query layer
UNIQUE INDEX idx_blacklist_ips_ip_active ON blacklist_ips(ip_address, is_active)
INDEX idx_blacklist_ips_is_active ON blacklist_ips(is_active)
```

---

### `whitelist_ips` — IP yang Selalu Diizinkan

```sql
id         CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
ip_address VARCHAR(45) UNIQUE NOT NULL
note       TEXT
is_active  BOOLEAN DEFAULT true
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

### `blacklist_phones` — Nomor WA yang Diblokir

```sql
id           CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
phone        VARCHAR(20) NOT NULL
reason       TEXT
is_auto      BOOLEAN DEFAULT false
banned_at    TIMESTAMP NOT NULL
banned_until TIMESTAMP                    -- null = permanen
is_active    BOOLEAN DEFAULT true
created_at   TIMESTAMP
updated_at   TIMESTAMP
```

**Index:**
```sql
-- MySQL tidak support partial unique index (WHERE clause)
-- Gunakan regular unique index pada (phone, is_active)
-- dan handle soft-delete pattern di query layer
UNIQUE INDEX idx_blacklist_phones_phone_active ON blacklist_phones(phone, is_active)
INDEX idx_blacklist_phones_is_active ON blacklist_phones(is_active)
```

---

### `whitelist_phones` — Nomor WA yang Selalu Diizinkan

```sql
id         CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
phone      VARCHAR(20) UNIQUE NOT NULL
note       TEXT
is_active  BOOLEAN DEFAULT true
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

### `request_logs` — Log untuk Auto-Ban

```sql
id         CHAR(36) PRIMARY KEY   -- UUID via HasUuids trait
ip_address VARCHAR(45) NOT NULL
phone      VARCHAR(20)
endpoint   VARCHAR(255) NOT NULL
created_at TIMESTAMP
```

**Index:**
```sql
INDEX idx_request_logs_ip_created ON request_logs(ip_address, created_at)
INDEX idx_request_logs_phone_created ON request_logs(phone, created_at)
```

Data lama dihapus scheduler setiap hari (hapus log lebih dari 24 jam).

---

## Relasi Eloquent

```
Regency       hasMany    Destination
Destination   belongsTo  Regency
Destination   hasMany    DestinationImage
Destination   hasMany    Accommodation
Destination   hasMany    DriverOrder

Accommodation belongsTo  Destination
Accommodation hasMany    DriverOrder (nullable)

Vehicle       hasMany    DriverOrder
Driver        hasMany    DriverOrder
DriverOrder   belongsTo  Destination
DriverOrder   belongsTo  Vehicle
DriverOrder   belongsTo  Driver (nullable)
DriverOrder   belongsTo  Accommodation (nullable)
```

---

## Seeder Data Awal

```
RegencySeeder       → 24 kabupaten/kota Sulawesi Selatan
VehicleSeeder       → Mobil (Avanza/Innova), Bus Pariwisata
SettingSeeder       → semua default settings
AdminUserSeeder     → 1 akun admin default
RoleSeeder          → role admin via Spatie
```
