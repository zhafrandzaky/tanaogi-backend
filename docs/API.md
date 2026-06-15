# API Documentation — TanaOgi Backend

> **Dokumentasi lengkap API TanaOgi untuk frontend developer.**
> Complete API reference for consuming the TanaOgi backend.

---

## ⚠️ Peringatan / Warning

> **Jangan gunakan URL production untuk development!**
> Do NOT use the production URL for development work.

| Environment | Base URL |
|---|---|
| **Local (Development)** | `http://localhost:8000/api/v1` |
| **Production** | `https://api.tanaogi.zyy.my.id/api/v1` |

---

## Daftar Isi / Table of Contents

1. [Format Response Standar / Standard Response Format](#format-response-standar)
2. [Authentication / Autentikasi](#authentication)
3. [Health Check](#1-health)
4. [Auth — Login & Logout](#2-auth)
5. [Public — Regencies](#3-public--regencies)
6. [Public — Destinations](#4-public--destinations)
7. [Public — Accommodations](#5-public--accommodations)
8. [Public — Vehicles](#6-public--vehicles)
9. [Public — Settings & Maintenance](#7-public--settings--maintenance)
10. [Admin — Regencies](#8-admin--regencies)
11. [Admin — Destinations](#9-admin--destinations)
12. [Admin — Accommodations](#10-admin--accommodations)
13. [Admin — Vehicles](#11-admin--vehicles)
14. [Admin — Drivers](#12-admin--drivers)
15. [Admin — Driver Orders](#13-admin--driver-orders)
16. [Admin — Users](#14-admin--users)
17. [Admin — Settings](#15-admin--settings)
18. [Admin — Maintenance](#16-admin--maintenance)
19. [Admin — Blacklist IP](#17-admin--blacklist-ip)
20. [Admin — Whitelist IP](#18-admin--whitelist-ip)
21. [Admin — Blacklist Phone](#19-admin--blacklist-phone)
22. [Admin — Whitelist Phone](#20-admin--whitelist-phone)
23. [Error Codes Reference](#error-codes-reference)
24. [Pagination](#pagination)
25. [Date Format](#date-format)

---

## Format Response Standar

Semua endpoint mengembalikan response dalam format envelope yang konsisten.
All endpoints return a consistent envelope format.

### Success Response

```json
{
  "success": true,
  "message": "Deskripsi pesan berhasil",
  "data": { ... }
}
```

### Paginated Response

```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

### Error Response

```json
{
  "success": false,
  "message": "Deskripsi error",
  "errors": null
}
```

### Validation Error Response (HTTP 422)

```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "email": ["Email sudah terdaftar"],
    "name": ["Nama wajib diisi"]
  }
}
```

> **Catatan**: Tidak ada emoji di response JSON manapun. Semua field `message` adalah plain text.

---

## Authentication

Semua endpoint admin memerlukan token Bearer. Sertakan header berikut di setiap request:

```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

Untuk upload file (foto destinasi), gunakan `multipart/form-data` alih-alih `application/json`.

### Cara Mendapatkan Token

1. Kirim request `POST /api/v1/auth/login` dengan email dan password.
2. Simpan `token` dari response.
3. Sertakan token di header `Authorization: Bearer {token}` untuk setiap request admin.

---

## 1. Health

### `GET /health`

**Deskripsi**: Cek status API apakah aktif dan berjalan.

**Auth Required**: Tidak

**Request**: Tidak ada body.

**Response (200)**:
```json
{
  "status": "ok",
  "service": "TanaOgi API"
}
```

---

## 2. Auth

### `POST /auth/login`

**Deskripsi**: Login admin dan mendapatkan token Sanctum.

**Auth Required**: Tidak

**Request Body**:
```json
{
  "email": "admin@tanaogi.zyy.my.id",
  "password": "password"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "1|laravel_sanctum_token_here",
    "user": {
      "id": "uuid",
      "name": "Admin TanaOgi",
      "email": "admin@tanaogi.zyy.my.id",
      "role": "admin"
    }
  }
}
```

**Response Error (401)**:
```json
{
  "success": false,
  "message": "Email atau password salah",
  "errors": null
}
```

---

### `POST /auth/logout`

**Deskripsi**: Logout admin dan menghapus token yang sedang aktif.

**Auth Required**: Ya (`Bearer token`)

**Request**: Tidak ada body.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null
}
```

**Response Error (401)**:
```json
{
  "success": false,
  "message": "Unauthenticated",
  "errors": null
}
```

---

## 3. Public — Regencies

### `GET /regencies`

**Deskripsi**: Ambil daftar semua kabupaten yang aktif.

**Auth Required**: Tidak

**Request**: Tidak ada body.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "name": "Bulukumba",
      "slug": "bulukumba",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

## 4. Public — Destinations

### `GET /destinations`

**Deskripsi**: Ambil daftar destinasi. Bisa difilter berdasarkan kabupaten.

**Auth Required**: Tidak

**Query Parameters**:

| Parameter | Type | Required | Description |
|---|---|---|---|
| `regency_id` | UUID | Tidak | Filter destinasi berdasarkan kabupaten |

**Request Example**:
```
GET /destinations?regency_id=uuid-kabupaten
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "regency_id": "uuid",
      "name": "Pantai Tanjung Bira",
      "slug": "pantai-tanjung-bira",
      "description": "Pantai indah di ujung selatan Sulawesi...",
      "ticket_price": 25000,
      "facilities": ["Toilet", "Parkir", "Musholla"],
      "route_text": "Dari Makassar ambil arah selatan...",
      "latitude": -5.61034567,
      "longitude": 120.45867890,
      "is_active": true,
      "images": [
        "https://storage.tanaogi.zyy.my.id/destinations/uuid/foto1.jpg"
      ],
      "regency": {
        "id": "uuid",
        "name": "Bulukumba"
      },
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `GET /destinations/{slug}`

**Deskripsi**: Ambil detail destinasi berdasarkan slug.

**Auth Required**: Tidak

**Path Parameters**:

| Parameter | Type | Description |
|---|---|---|
| `slug` | string | Slug destinasi (contoh: `pantai-tanjung-bira`) |

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Detail destinasi berhasil diambil",
  "data": {
    "id": "uuid",
    "regency_id": "uuid",
    "name": "Pantai Tanjung Bira",
    "slug": "pantai-tanjung-bira",
    "description": "Pantai indah di ujung selatan Sulawesi...",
    "ticket_price": 25000,
    "facilities": ["Toilet", "Parkir", "Musholla"],
    "route_text": "Dari Makassar ambil arah selatan...",
    "latitude": -5.61034567,
    "longitude": 120.45867890,
    "is_active": true,
    "images": [
      "https://storage.tanaogi.zyy.my.id/destinations/uuid/foto1.jpg",
      "https://storage.tanaogi.zyy.my.id/destinations/uuid/foto2.jpg"
    ],
    "regency": {
      "id": "uuid",
      "name": "Bulukumba"
    },
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

**Response Error (404)**:
```json
{
  "success": false,
  "message": "Data tidak ditemukan",
  "errors": null
}
```

---

### `GET /destinations/{slug}/accommodations`

**Deskripsi**: Ambil daftar penginapan di sekitar destinasi tertentu.

**Auth Required**: Tidak

**Path Parameters**:

| Parameter | Type | Description |
|---|---|---|
| `slug` | string | Slug destinasi |

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "destination_id": "uuid",
      "name": "Hakuna Matata Resort",
      "type": "resort",
      "address": "Jl. Poros Bira No. 10",
      "phone": "08123456789",
      "price_per_night": 450000,
      "description": "Resort tepi pantai...",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

## 5. Public — Accommodations

### `GET /accommodations`

**Deskripsi**: Ambil daftar semua penginapan yang aktif.

**Auth Required**: Tidak

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "destination_id": "uuid",
      "name": "Hakuna Matata Resort",
      "type": "resort",
      "address": "Jl. Poros Bira No. 10",
      "phone": "08123456789",
      "price_per_night": 450000,
      "description": "Resort tepi pantai dengan pemandangan sunset...",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

> **Enum `type`**: `hotel` | `resort` | `homestay`

---

## 6. Public — Vehicles

### `GET /vehicles`

**Deskripsi**: Ambil daftar semua kendaraan yang aktif.

**Auth Required**: Tidak

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "name": "Toyota Avanza",
      "type": "car",
      "price_per_day": 500000,
      "description": "Mobil keluarga nyaman...",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

> **Enum `type`**: `car` | `bus`

---

## 7. Public — Settings & Maintenance

### `GET /settings/whatsapp`

**Deskripsi**: Ambil pengaturan WhatsApp publik — nomor admin dan template pesan untuk pemesanan driver.

**Auth Required**: Tidak

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "admin_whatsapp": "628123456789",
    "wa_template": "Halo Admin TanaOgi\n\nSaya ingin memesan kendaraan:\n\nKendaraan  : {vehicle}\nTujuan     : {destination}\nBerangkat  : {departure_date}\nPulang     : {return_date}\nMenginap   : {accommodation}\nJemput di  : {pickup_location}\n\nNama : {user_name}\nNo HP: {user_phone}"
  }
}
```

**Catatan untuk Frontend**:
- Gunakan `admin_whatsapp` untuk generate WhatsApp deeplink: `https://wa.me/{phone}?text={encoded_message}`
- Replace placeholder `{variable}` di `wa_template` dengan data yang dipilih user.

---

### `GET /maintenance/status`

**Deskripsi**: Cek apakah website sedang dalam mode maintenance.

**Auth Required**: Tidak

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diambil",
  "data": {
    "is_maintenance": false
  }
}
```

> Frontend bisa menampilkan banner atau redirect ke halaman maintenance jika `is_maintenance: true`.

---

## 8. Admin — Regencies

> Semua endpoint di bawah ini memerlukan header `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/regencies`

**Deskripsi**: Ambil daftar semua kabupaten (termasuk yang nonaktif).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "name": "Bulukumba",
      "slug": "bulukumba",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/regencies`

**Deskripsi**: Tambah kabupaten baru.

**Request Body**:
```json
{
  "name": "Bulukumba"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Data berhasil ditambahkan",
  "data": {
    "id": "uuid",
    "name": "Bulukumba",
    "slug": "bulukumba",
    "is_active": true,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

**Response Error (422)**:
```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "name": ["Nama sudah ada"]
  }
}
```

---

### `GET /admin/regencies/{id}`

**Deskripsi**: Ambil detail kabupaten.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": "uuid",
    "name": "Bulukumba",
    "slug": "bulukumba",
    "is_active": true,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `PUT /admin/regencies/{id}`

**Deskripsi**: Update data kabupaten.

**Request Body**:
```json
{
  "name": "Bulukumba Selatan"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diupdate",
  "data": {
    "id": "uuid",
    "name": "Bulukumba Selatan",
    "slug": "bulukumba-selatan",
    "is_active": true,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `DELETE /admin/regencies/{id}`

**Deskripsi**: Hapus kabupaten beserta semua destinasi terkait.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `PATCH /admin/regencies/{id}/toggle-active`

**Deskripsi**: Toggle status aktif/nonaktif kabupaten.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diubah",
  "data": {
    "id": "uuid",
    "is_active": false
  }
}
```

---

## 9. Admin — Destinations

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/destinations`

**Deskripsi**: Ambil daftar semua destinasi (termasuk yang nonaktif).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "regency_id": "uuid",
      "name": "Pantai Tanjung Bira",
      "slug": "pantai-tanjung-bira",
      "description": "...",
      "ticket_price": 25000,
      "facilities": ["Toilet", "Parkir"],
      "route_text": "Dari Makassar...",
      "latitude": -5.61034567,
      "longitude": 120.45867890,
      "is_active": true,
      "images": [
        "https://storage.tanaogi.zyy.my.id/destinations/uuid/foto1.jpg"
      ],
      "regency": { "id": "uuid", "name": "Bulukumba" },
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/destinations`

**Deskripsi**: Tambah destinasi baru.

**Request Body**:
```json
{
  "regency_id": "uuid-kabupaten",
  "name": "Pantai Tanjung Bira",
  "description": "Pantai pasir putih yang indah...",
  "ticket_price": 25000,
  "facilities": ["Toilet", "Parkir", "Musholla"],
  "route_text": "Dari Makassar ambil arah selatan menuju Bulukumba...",
  "latitude": -5.61034567,
  "longitude": 120.45867890
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Data berhasil ditambahkan",
  "data": {
    "id": "uuid",
    "regency_id": "uuid",
    "name": "Pantai Tanjung Bira",
    "slug": "pantai-tanjung-bira",
    "description": "Pantai pasir putih yang indah...",
    "ticket_price": 25000,
    "facilities": ["Toilet", "Parkir", "Musholla"],
    "route_text": "Dari Makassar ambil arah selatan menuju Bulukumba...",
    "latitude": -5.61034567,
    "longitude": 120.45867890,
    "is_active": true,
    "images": [],
    "regency": { "id": "uuid", "name": "Bulukumba" },
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `GET /admin/destinations/{id}`

**Deskripsi**: Ambil detail destinasi.

**Response Success (200)**: Sama seperti response `POST /admin/destinations`.

---

### `PUT /admin/destinations/{id}`

**Deskripsi**: Update data destinasi.

**Request Body** (semua field opsional, kirim yang ingin diubah saja):
```json
{
  "name": "Pantai Tanjung Bira Selatan",
  "description": "Deskripsi baru...",
  "ticket_price": 30000,
  "facilities": ["Toilet", "Parkir", "Musholla", "Warung"],
  "route_text": "Rute baru...",
  "latitude": -5.61034567,
  "longitude": 120.45867890,
  "regency_id": "uuid-kabupaten-lain"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diupdate",
  "data": { ... }
}
```

---

### `DELETE /admin/destinations/{id}`

**Deskripsi**: Hapus destinasi beserta semua foto di R2 dan database.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `PATCH /admin/destinations/{id}/toggle-active`

**Deskripsi**: Toggle status aktif/nonaktif destinasi.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diubah",
  "data": {
    "id": "uuid",
    "is_active": false
  }
}
```

---

### `POST /admin/destinations/{id}/images`

**Deskripsi**: Upload foto destinasi ke Cloudflare R2. Maks 10 foto, maks 2MB per foto.

**Content-Type**: `multipart/form-data`

**Request Body**:

| Field | Type | Required | Description |
|---|---|---|---|
| `images[]` | file | Ya | File foto (JPEG, PNG, WebP). Bisa multiple. |

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Foto berhasil diupload",
  "data": [
    {
      "id": "uuid",
      "url": "https://storage.tanaogi.zyy.my.id/destinations/uuid/abc.jpg",
      "order": 0
    },
    {
      "id": "uuid",
      "url": "https://storage.tanaogi.zyy.my.id/destinations/uuid/def.jpg",
      "order": 1
    }
  ]
}
```

**Response Error (422)**:
```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "images": ["Maksimal 10 foto per destinasi"]
  }
}
```

---

### `DELETE /admin/destinations/{id}/images/{imageId}`

**Deskripsi**: Hapus foto tertentu dari R2 dan database.

**Path Parameters**:

| Parameter | Type | Description |
|---|---|---|
| `id` | UUID | ID destinasi |
| `imageId` | UUID | ID foto yang ingin dihapus |

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Foto berhasil dihapus",
  "data": null
}
```

---

## 10. Admin — Accommodations

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/accommodations`

**Deskripsi**: Ambil daftar semua penginapan (termasuk yang nonaktif).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "destination_id": "uuid",
      "name": "Hakuna Matata Resort",
      "type": "resort",
      "address": "Jl. Poros Bira No. 10",
      "phone": "08123456789",
      "price_per_night": 450000,
      "description": "Resort tepi pantai...",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/accommodations`

**Deskripsi**: Tambah penginapan baru.

**Request Body**:
```json
{
  "destination_id": "uuid-destinasi",
  "name": "Hakuna Matata Resort",
  "type": "resort",
  "address": "Jl. Poros Bira No. 10",
  "phone": "08123456789",
  "price_per_night": 450000,
  "description": "Resort tepi pantai dengan pemandangan sunset yang indah"
}
```

> **Enum `type`**: `hotel` | `resort` | `homestay`

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Data berhasil ditambahkan",
  "data": { ... }
}
```

---

### `GET /admin/accommodations/{id}`

**Deskripsi**: Ambil detail penginapan.

**Response Success (200)**: Sama seperti response `POST /admin/accommodations`.

---

### `PUT /admin/accommodations/{id}`

**Deskripsi**: Update data penginapan.

**Request Body** (semua field opsional):
```json
{
  "name": "Hakuna Matata Resort Baru",
  "type": "hotel",
  "address": "Alamat baru",
  "phone": "08123456780",
  "price_per_night": 550000,
  "description": "Deskripsi baru...",
  "destination_id": "uuid-destinasi-lain"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diupdate",
  "data": { ... }
}
```

---

### `DELETE /admin/accommodations/{id}`

**Deskripsi**: Hapus penginapan.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `PATCH /admin/accommodations/{id}/toggle-active`

**Deskripsi**: Toggle status aktif/nonaktif penginapan.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diubah",
  "data": {
    "id": "uuid",
    "is_active": false
  }
}
```

---

## 11. Admin — Vehicles

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/vehicles`

**Deskripsi**: Ambil daftar semua kendaraan (termasuk yang nonaktif).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "name": "Toyota Avanza",
      "type": "car",
      "price_per_day": 500000,
      "description": "Mobil keluarga nyaman untuk perjalanan jauh",
      "is_active": true,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/vehicles`

**Deskripsi**: Tambah kendaraan baru.

**Request Body**:
```json
{
  "name": "Toyota HiAce",
  "type": "bus",
  "price_per_day": 1200000,
  "description": "Bus mini untuk rombongan"
}
```

> **Enum `type`**: `car` | `bus`

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Data berhasil ditambahkan",
  "data": { ... }
}
```

---

### `GET /admin/vehicles/{id}`

**Deskripsi**: Ambil detail kendaraan.

**Response Success (200)**: Sama seperti response `POST /admin/vehicles`.

---

### `PUT /admin/vehicles/{id}`

**Deskripsi**: Update data kendaraan.

**Request Body** (semua field opsional):
```json
{
  "name": "Toyota HiAce Premio",
  "type": "bus",
  "price_per_day": 1500000,
  "description": "Bus mini premium..."
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diupdate",
  "data": { ... }
}
```

---

### `DELETE /admin/vehicles/{id}`

**Deskripsi**: Hapus kendaraan.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `PATCH /admin/vehicles/{id}/toggle-active`

**Deskripsi**: Toggle status aktif/nonaktif kendaraan.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diubah",
  "data": {
    "id": "uuid",
    "is_active": false
  }
}
```

---

## 12. Admin — Drivers

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/drivers`

**Deskripsi**: Ambil daftar semua driver (termasuk yang nonaktif).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "name": "Ahmad",
      "phone": "628123456789",
      "is_available": true,
      "is_active": true,
      "vehicle_id": "uuid",
      "vehicle": {
        "id": "uuid",
        "name": "Toyota Avanza",
        "type": "car"
      },
      "blocked_dates": ["2025-06-20", "2025-06-22"],
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/drivers`

**Deskripsi**: Tambah driver baru.

**Request Body**:
```json
{
  "name": "Ahmad",
  "phone": "628123456789",
  "vehicle_id": "uuid-kendaraan",
  "blocked_dates": ["2025-06-20", "2025-06-22"]
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Data berhasil ditambahkan",
  "data": { ... }
}
```

---

### `GET /admin/drivers/{id}`

**Deskripsi**: Ambil detail driver.

**Response Success (200)**: Sama seperti response `POST /admin/drivers`.

---

### `PUT /admin/drivers/{id}`

**Deskripsi**: Update data driver.

**Request Body** (semua field opsional):
```json
{
  "name": "Ahmad Baru",
  "phone": "628123456780",
  "vehicle_id": "uuid-kendaraan-lain",
  "blocked_dates": ["2025-07-01"]
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diupdate",
  "data": { ... }
}
```

---

### `DELETE /admin/drivers/{id}`

**Deskripsi**: Hapus driver.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `PATCH /admin/drivers/{id}/toggle-active`

**Deskripsi**: Toggle status aktif/nonaktif driver.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diubah",
  "data": {
    "id": "uuid",
    "is_active": false
  }
}
```

---

### `GET /admin/drivers/{id}/schedule`

**Deskripsi**: Ambil jadwal driver per bulan — menampilkan tanggal blocked dan order yang sudah ada.

**Query Parameters**:

| Parameter | Type | Required | Description |
|---|---|---|---|
| `month` | integer | Tidak (default: bulan ini) | Bulan (1-12) |
| `year` | integer | Tidak (default: tahun ini) | Tahun |

**Request Example**:
```
GET /admin/drivers/uuid/schedule?month=6&year=2025
```

**Response Success (200)**:
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
      },
      {
        "date": "2025-06-22",
        "type": "return",
        "order_id": "uuid",
        "user_name": "Budi"
      }
    ]
  }
}
```

---

## 13. Admin — Driver Orders

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/driver-orders`

**Deskripsi**: Ambil daftar pemesanan driver. Mendukung filter status dan pagination.

**Query Parameters**:

| Parameter | Type | Required | Description |
|---|---|---|---|
| `status` | string | Tidak | Filter status: `pending`, `confirmed`, `completed`, `cancelled` |
| `page` | integer | Tidak (default: 1) | Halaman |
| `per_page` | integer | Tidak (default: 15) | Jumlah item per halaman |

**Request Example**:
```
GET /admin/driver-orders?status=pending&page=1&per_page=15
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "destination": { "id": "uuid", "name": "Pantai Tanjung Bira", "slug": "..." },
      "vehicle": { "id": "uuid", "name": "Toyota Avanza", "type": "car" },
      "driver": { "id": "uuid", "name": "Ahmad" },
      "accommodation": { "id": "uuid", "name": "Hakuna Matata Resort" },
      "user_name": "Budi Santoso",
      "user_phone": "08123456789",
      "departure_date": "2025-06-20",
      "return_date": "2025-06-22",
      "is_overnight": true,
      "pickup_location": "Hakuna Matata Resort, Jl. Poros Bira",
      "status": "pending",
      "departure_reminded": false,
      "return_reminded": false,
      "notes": "Catatan tambahan",
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

> **Enum `status`**: `pending` | `confirmed` | `completed` | `cancelled`

---

### `POST /admin/driver-orders`

**Deskripsi**: Buat pemesanan driver baru.

**Request Body**:
```json
{
  "destination_id": "uuid-destinasi",
  "vehicle_id": "uuid-kendaraan",
  "accommodation_id": "uuid-penginapan",
  "user_name": "Budi Santoso",
  "user_phone": "08123456789",
  "departure_date": "2025-06-20",
  "return_date": "2025-06-22",
  "is_overnight": true,
  "pickup_location": "Hakuna Matata Resort, Jl. Poros Bira",
  "notes": "Catatan tambahan"
}
```

> `accommodation_id` bisa `null` jika tidak menginap.

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Pesanan berhasil dibuat",
  "data": { ... }
}
```

**Response Error (422)**:
```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "departure_date": ["Tanggal berangkat wajib diisi"],
    "user_phone": ["Nomor telepon tidak valid"]
  }
}
```

---

### `GET /admin/driver-orders/{id}`

**Deskripsi**: Ambil detail pemesanan driver.

**Response Success (200)**: Sama seperti item di response `GET /admin/driver-orders`.

---

### `PUT /admin/driver-orders/{id}`

**Deskripsi**: Update pemesanan driver (assign driver dan ubah status).

**Request Body**:
```json
{
  "driver_id": "uuid-driver",
  "status": "confirmed"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Pesanan berhasil diupdate",
  "data": { ... }
}
```

---

### `DELETE /admin/driver-orders/{id}`

**Deskripsi**: Hapus pemesanan driver.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `PATCH /admin/driver-orders/{id}/confirm`

**Deskripsi**: Konfirmasi pemesanan driver (ubah status ke `confirmed`).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Pesanan berhasil dikonfirmasi",
  "data": { ... }
}
```

---

### `PATCH /admin/driver-orders/{id}/complete`

**Deskripsi**: Tandai pemesanan selesai (ubah status ke `completed`).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Pesanan selesai",
  "data": { ... }
}
```

---

### `PATCH /admin/driver-orders/{id}/cancel`

**Deskripsi**: Batalkan pemesanan (ubah status ke `cancelled`).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Pesanan dibatalkan",
  "data": { ... }
}
```

---

## 14. Admin — Users

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/users`

**Deskripsi**: Ambil daftar semua user admin.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "name": "Admin TanaOgi",
      "email": "admin@tanaogi.zyy.my.id",
      "role": "admin",
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/users`

**Deskripsi**: Tambah user admin baru.

**Request Body**:
```json
{
  "name": "Admin Baru",
  "email": "admin2@tanaogi.zyy.my.id",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Data berhasil ditambahkan",
  "data": {
    "id": "uuid",
    "name": "Admin Baru",
    "email": "admin2@tanaogi.zyy.my.id",
    "role": "admin",
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

**Response Error (422)**:
```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "email": ["Email sudah terdaftar"]
  }
}
```

---

### `GET /admin/users/{id}`

**Deskripsi**: Ambil detail user.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": "uuid",
    "name": "Admin TanaOgi",
    "email": "admin@tanaogi.zyy.my.id",
    "role": "admin",
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `PUT /admin/users/{id}`

**Deskripsi**: Update data user admin.

**Request Body** (password opsional, hanya jika ingin diubah):
```json
{
  "name": "Admin TanaOgi Baru",
  "email": "admin@tanaogi.zyy.my.id",
  "password": "password_baru",
  "password_confirmation": "password_baru"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diupdate",
  "data": { ... }
}
```

---

### `DELETE /admin/users/{id}`

**Deskripsi**: Hapus user admin.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

**Response Error (403)**:
```json
{
  "success": false,
  "message": "Tidak dapat menghapus user yang sedang login",
  "errors": null
}
```

---

## 15. Admin — Settings

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/settings`

**Deskripsi**: Ambil semua pengaturan sistem.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "admin_whatsapp": "628123456789",
    "wa_template": "Halo Admin TanaOgi...",
    "reminder_hours_before_pickup": 3,
    "max_requests_per_minute": 60,
    "max_orders_per_phone": 3,
    "orders_window_hours": 24,
    "auto_ban_enabled": true,
    "auto_ban_duration_minutes": 60
  }
}
```

---

### `PUT /admin/settings`

**Deskripsi**: Update pengaturan sistem. Kirim hanya key yang ingin diubah.

**Request Body**:
```json
{
  "admin_whatsapp": "628123456789",
  "wa_template": "Template pesan WA baru...",
  "reminder_hours_before_pickup": 3,
  "max_requests_per_minute": 60,
  "max_orders_per_phone": 3,
  "orders_window_hours": 24,
  "auto_ban_enabled": true,
  "auto_ban_duration_minutes": 60
}
```

| Key | Type | Description |
|---|---|---|
| `admin_whatsapp` | string | Nomor WA admin (format internasional) |
| `wa_template` | string | Template pesan WA dengan placeholder `{variable}` |
| `reminder_hours_before_pickup` | integer | Jam sebelum jemput untuk kirim reminder |
| `max_requests_per_minute` | integer | Batas request per menit per IP |
| `max_orders_per_phone` | integer | Batas order per nomor WA dalam window |
| `orders_window_hours` | integer | Window waktu (jam) untuk batas order |
| `auto_ban_enabled` | boolean | Aktifkan auto-ban jika melanggar batas |
| `auto_ban_duration_minutes` | integer | Durasi ban otomatis (menit) |

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Pengaturan berhasil diupdate",
  "data": { ... }
}
```

---

## 16. Admin — Maintenance

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/maintenance/status`

**Deskripsi**: Cek status maintenance saat ini.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Status berhasil diambil",
  "data": {
    "is_maintenance": false
  }
}
```

---

### `POST /admin/maintenance/enable`

**Deskripsi**: Aktifkan mode maintenance. Semua endpoint publik akan return HTTP 503.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Maintenance mode diaktifkan",
  "data": {
    "is_maintenance": true
  }
}
```

---

### `POST /admin/maintenance/disable`

**Deskripsi**: Nonaktifkan mode maintenance.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Maintenance mode dinonaktifkan",
  "data": {
    "is_maintenance": false
  }
}
```

> **Catatan**: Saat maintenance aktif, request publik tetap bisa bypass menggunakan query param `?secret={MAINTENANCE_SECRET}`.

---

## 17. Admin — Blacklist IP

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/blacklist/ips`

**Deskripsi**: Ambil daftar semua IP yang di-ban.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "ip_address": "192.168.1.1",
      "reason": "Spam request",
      "banned_until": "2025-06-01T01:00:00.000000Z",
      "is_permanent": false,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/blacklist/ips`

**Deskripsi**: Ban IP address secara manual.

**Request Body**:
```json
{
  "ip_address": "192.168.1.1",
  "reason": "Spam request",
  "duration_minutes": 60
}
```

> Set `duration_minutes` ke `null` untuk ban permanen.

**Response Success (201)**:
```json
{
  "success": true,
  "message": "IP berhasil diblokir",
  "data": {
    "id": "uuid",
    "ip_address": "192.168.1.1",
    "reason": "Spam request",
    "banned_until": "2025-06-01T01:00:00.000000Z",
    "is_permanent": false,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `GET /admin/blacklist/ips/{id}`

**Deskripsi**: Ambil detail IP yang di-ban.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": "uuid",
    "ip_address": "192.168.1.1",
    "reason": "Spam request",
    "banned_until": "2025-06-01T01:00:00.000000Z",
    "is_permanent": false,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `DELETE /admin/blacklist/ips/{id}`

**Deskripsi**: Hapus entri IP dari daftar blacklist.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `POST /admin/blacklist/ips/{id}/unban`

**Deskripsi**: Cabut ban pada IP (menandai sebagai un-banned tanpa menghapus record).

**Response Success (200)**:
```json
{
  "success": true,
  "message": "IP berhasil di-unban",
  "data": null
}
```

---

## 18. Admin — Whitelist IP

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/whitelist/ips`

**Deskripsi**: Ambil daftar semua IP yang di-whitelist.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "ip_address": "192.168.1.100",
      "note": "IP kantor admin",
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/whitelist/ips`

**Deskripsi**: Tambah IP ke whitelist.

**Request Body**:
```json
{
  "ip_address": "192.168.1.100",
  "note": "IP kantor admin"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "IP berhasil ditambahkan ke whitelist",
  "data": {
    "id": "uuid",
    "ip_address": "192.168.1.100",
    "note": "IP kantor admin",
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `DELETE /admin/whitelist/ips/{id}`

**Deskripsi**: Hapus IP dari whitelist.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

## 19. Admin — Blacklist Phone

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/blacklist/phones`

**Deskripsi**: Ambil daftar semua nomor WA yang di-ban.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "phone": "628123456789",
      "reason": "Spam order",
      "banned_until": "2025-06-02T00:00:00.000000Z",
      "is_permanent": false,
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/blacklist/phones`

**Deskripsi**: Ban nomor WA secara manual.

**Request Body**:
```json
{
  "phone": "628123456789",
  "reason": "Spam order",
  "duration_minutes": 1440
}
```

> Set `duration_minutes` ke `null` untuk ban permanen.

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Nomor berhasil diblokir",
  "data": {
    "id": "uuid",
    "phone": "628123456789",
    "reason": "Spam order",
    "banned_until": "2025-06-02T00:00:00.000000Z",
    "is_permanent": false,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `GET /admin/blacklist/phones/{id}`

**Deskripsi**: Ambil detail nomor WA yang di-ban.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": "uuid",
    "phone": "628123456789",
    "reason": "Spam order",
    "banned_until": "2025-06-02T00:00:00.000000Z",
    "is_permanent": false,
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `DELETE /admin/blacklist/phones/{id}`

**Deskripsi**: Hapus entri nomor WA dari daftar blacklist.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

### `POST /admin/blacklist/phones/{id}/unban`

**Deskripsi**: Cabut ban pada nomor WA.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Nomor berhasil di-unban",
  "data": null
}
```

---

## 20. Admin — Whitelist Phone

> Memerlukan `Authorization: Bearer {token}` dan role `admin`.

### `GET /admin/whitelist/phones`

**Deskripsi**: Ambil daftar semua nomor WA yang di-whitelist.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    {
      "id": "uuid",
      "phone": "628123456789",
      "note": "Nomor WA tim internal",
      "created_at": "2025-06-01T00:00:00.000000Z"
    }
  ]
}
```

---

### `POST /admin/whitelist/phones`

**Deskripsi**: Tambah nomor WA ke whitelist.

**Request Body**:
```json
{
  "phone": "628123456789",
  "note": "Nomor WA tim internal"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Nomor berhasil ditambahkan ke whitelist",
  "data": {
    "id": "uuid",
    "phone": "628123456789",
    "note": "Nomor WA tim internal",
    "created_at": "2025-06-01T00:00:00.000000Z"
  }
}
```

---

### `DELETE /admin/whitelist/phones/{id}`

**Deskripsi**: Hapus nomor WA dari whitelist.

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Data berhasil dihapus",
  "data": null
}
```

---

## Error Codes Reference

| HTTP Status | Condition | Message |
|---|---|---|
| `401` | Token tidak valid / tidak ada | `"Unauthenticated"` |
| `403` | IP di-ban | `"Akses ditolak"` |
| `403` | Nomor WA di-ban | `"Nomor tidak dapat melakukan pemesanan"` |
| `404` | Data tidak ditemukan | `"Data tidak ditemukan"` |
| `422` | Validasi gagal | `"Data tidak valid"` + `errors` field |
| `429` | Rate limit / auto-ban | `"Terlalu banyak request. Coba lagi nanti"` |
| `503` | Maintenance mode | `"Website sedang dalam mode maintenance"` |

---

## Pagination

Endpoint yang mendukung pagination menerima parameter:

| Parameter | Type | Default | Description |
|---|---|---|---|
| `page` | integer | 1 | Nomor halaman |
| `per_page` | integer | 15 | Jumlah item per halaman |

Response akan menyertakan field `meta`:
```json
{
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

---

## Date Format

| Format | Contoh | Penggunaan |
|---|---|---|
| Tanggal (`YYYY-MM-DD`) | `"2025-06-20"` | `departure_date`, `return_date`, `blocked_dates` |
| Datetime (ISO 8601) | `"2025-06-20T10:30:00.000000Z"` | `created_at`, `banned_until` |
