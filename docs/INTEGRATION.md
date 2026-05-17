# INTEGRATION.md — Kontrak API TanaOgi dengan Frontend

Dokumen ini adalah "kontrak" antara backend dan frontend. Frontend mengkonsumsi semua endpoint di bawah ini.

---

## Base URL

```
Local      : http://localhost:8000/api/v1
Production : https://api.tanaogi.com/api/v1
```

---

## CORS

`config/cors.php`:
```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
```

---

## Auth Header (Endpoint Admin)

```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

Untuk upload foto gunakan `multipart/form-data`.

---

## Aturan Response API

- TIDAK BOLEH ada emoji di response JSON apapun
- Semua field `message` harus plain text
- Konten template WA di DB boleh ada emoji karena itu data user bukan response sistem

---

## Endpoint Public — Tidak Butuh Token

```
GET /regencies
GET /destinations?regency_id=uuid
GET /destinations/{slug}
GET /destinations/{slug}/accommodations
GET /vehicles
GET /settings/whatsapp
GET /maintenance/status
GET /health
```

Response `GET /destinations/{slug}` menyertakan array URL foto dari R2:
```json
{
  "success": true,
  "message": "Detail destinasi berhasil diambil",
  "data": {
    "id": "uuid",
    "name": "Pantai Tanjung Bira",
    "slug": "pantai-tanjung-bira",
    "description": "...",
    "ticket_price": 25000,
    "facilities": ["Toilet", "Parkir"],
    "route_text": "Dari Makassar...",
    "latitude": -5.6103,
    "longitude": 120.4586,
    "images": [
      "https://storage.tanaogi.com/destinations/uuid/foto1.jpg",
      "https://storage.tanaogi.com/destinations/uuid/foto2.jpg"
    ],
    "regency": { "id": "uuid", "name": "Bulukumba" }
  }
}
```

---

## Endpoint Auth

```
POST /auth/login
POST /auth/logout     (butuh token)
```

---

## Endpoint Admin — Butuh Token + Role Admin

### Regencies
```
GET    /admin/regencies
POST   /admin/regencies
GET    /admin/regencies/{id}
PUT    /admin/regencies/{id}
DELETE /admin/regencies/{id}
PATCH  /admin/regencies/{id}/toggle-active
```

### Destinations
```
GET    /admin/destinations
POST   /admin/destinations
GET    /admin/destinations/{id}
PUT    /admin/destinations/{id}
DELETE /admin/destinations/{id}
PATCH  /admin/destinations/{id}/toggle-active
```

**Upload foto** (multipart/form-data, max 10 foto, max 2MB/foto):
```
POST /admin/destinations/{id}/images
Content-Type: multipart/form-data
Body: images[] = file1.jpg, images[] = file2.jpg
```

Response upload:
```json
{
  "success": true,
  "message": "Foto berhasil diupload",
  "data": [
    {
      "id": "uuid",
      "url": "https://storage.tanaogi.com/destinations/uuid/abc.jpg",
      "order": 0
    },
    {
      "id": "uuid",
      "url": "https://storage.tanaogi.com/destinations/uuid/def.jpg",
      "order": 1
    }
  ]
}
```

**Hapus foto** (hapus dari R2 + DB):
```
DELETE /admin/destinations/{id}/images/{imageId}
```

Response:
```json
{
  "success": true,
  "message": "Foto berhasil dihapus",
  "data": null
}
```

### Drivers
```
GET    /admin/drivers
POST   /admin/drivers
GET    /admin/drivers/{id}
PUT    /admin/drivers/{id}
DELETE /admin/drivers/{id}
PATCH  /admin/drivers/{id}/toggle-active
GET    /admin/drivers/{id}/schedule?month=6&year=2025
```

Response schedule:
```json
{
  "success": true,
  "message": "Jadwal driver berhasil diambil",
  "data": {
    "driver_id": "uuid",
    "driver_name": "Ahmad",
    "month": 6,
    "year": 2025,
    "blocked_dates": ["2025-06-20", "2025-06-22"],
    "orders": [
      {
        "date": "2025-06-20",
        "type": "departure",
        "order_id": "uuid",
        "user_name": "Budi"
      }
    ]
  }
}
```

### Driver Orders
```
GET    /admin/driver-orders
GET    /admin/driver-orders?status=pending
POST   /admin/driver-orders
GET    /admin/driver-orders/{id}
PUT    /admin/driver-orders/{id}
DELETE /admin/driver-orders/{id}
```

Body POST:
```json
{
  "destination_id": "uuid",
  "vehicle_id": "uuid",
  "accommodation_id": "uuid atau null",
  "user_name": "Budi Santoso",
  "user_phone": "08123456789",
  "departure_date": "2025-06-20",
  "return_date": "2025-06-22",
  "is_overnight": true,
  "pickup_location": "Hakuna Matata Resort, Jl. Poros Bira",
  "notes": "catatan tambahan"
}
```

Body PUT:
```json
{
  "driver_id": "uuid",
  "status": "confirmed"
}
```

### Accommodations
```
GET    /admin/accommodations
POST   /admin/accommodations
GET    /admin/accommodations/{id}
PUT    /admin/accommodations/{id}
DELETE /admin/accommodations/{id}
PATCH  /admin/accommodations/{id}/toggle-active
```

### Vehicles
```
GET    /admin/vehicles
POST   /admin/vehicles
GET    /admin/vehicles/{id}
PUT    /admin/vehicles/{id}
DELETE /admin/vehicles/{id}
PATCH  /admin/vehicles/{id}/toggle-active
```

### Admin Users
```
GET    /admin/users
POST   /admin/users
GET    /admin/users/{id}
PUT    /admin/users/{id}
DELETE /admin/users/{id}
```

### Settings
```
GET /admin/settings
PUT /admin/settings
```

Body PUT:
```json
{
  "admin_whatsapp": "628123456789",
  "wa_template": "template pesan WA...",
  "reminder_hours_before_pickup": 3,
  "max_requests_per_minute": 60,
  "max_orders_per_phone": 3,
  "orders_window_hours": 24,
  "auto_ban_enabled": true,
  "auto_ban_duration_minutes": 60
}
```

### Maintenance Mode
```
GET  /admin/maintenance/status
POST /admin/maintenance/enable
POST /admin/maintenance/disable
```

### Blacklist IP
```
GET    /admin/blacklist/ips
POST   /admin/blacklist/ips
GET    /admin/blacklist/ips/{id}
DELETE /admin/blacklist/ips/{id}
POST   /admin/blacklist/ips/{id}/unban
```

Body POST:
```json
{
  "ip_address": "192.168.1.1",
  "reason": "Spam request",
  "duration_minutes": 60
}
```

### Whitelist IP
```
GET    /admin/whitelist/ips
POST   /admin/whitelist/ips
DELETE /admin/whitelist/ips/{id}
```

Body POST:
```json
{
  "ip_address": "192.168.1.100",
  "note": "IP kantor admin"
}
```

### Blacklist Nomor WA
```
GET    /admin/blacklist/phones
POST   /admin/blacklist/phones
GET    /admin/blacklist/phones/{id}
DELETE /admin/blacklist/phones/{id}
POST   /admin/blacklist/phones/{id}/unban
```

Body POST:
```json
{
  "phone": "628123456789",
  "reason": "Spam order",
  "duration_minutes": 1440
}
```

### Whitelist Nomor WA
```
GET    /admin/whitelist/phones
POST   /admin/whitelist/phones
DELETE /admin/whitelist/phones/{id}
```

Body POST:
```json
{
  "phone": "628123456789",
  "note": "Nomor WA tim internal"
}
```

---

## Format Error Response

```json
// IP di-ban
HTTP 403
{ "success": false, "message": "Akses ditolak", "errors": null }

// Rate limit / auto-ban
HTTP 429
{ "success": false, "message": "Terlalu banyak request. Coba lagi nanti", "errors": null }

// Nomor WA di-ban
HTTP 403
{ "success": false, "message": "Nomor tidak dapat melakukan pemesanan", "errors": null }

// Maintenance mode
HTTP 503
{ "success": false, "message": "Website sedang dalam mode maintenance", "errors": null }

// Validasi gagal
HTTP 422
{ "success": false, "message": "Data tidak valid", "errors": { "field": ["pesan error"] } }

// Not found
HTTP 404
{ "success": false, "message": "Data tidak ditemukan", "errors": null }
```

---

## Toggle Active

```
PATCH /admin/{resource}/{id}/toggle-active
```

Response:
```json
{
  "success": true,
  "message": "Status berhasil diubah",
  "data": { "id": "uuid", "is_active": false }
}
```

---

## Format Tanggal

```
Tanggal  : YYYY-MM-DD       contoh: "2025-06-20"
Datetime : ISO 8601         contoh: "2025-06-20T10:30:00.000000Z"
```

---

## Pagination

```
GET /admin/driver-orders?page=1&per_page=15
```

Response:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```
