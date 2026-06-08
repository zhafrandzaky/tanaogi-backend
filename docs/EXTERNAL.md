# EXTERNAL.md — Integrasi Eksternal TanaOgi

TanaOgi hanya menggunakan 2 integrasi eksternal: Google Maps (deeplink) dan WhatsApp (deeplink + WaAPI untuk reminder).

---

## 1. Google Maps Deeplink

Digunakan di Tahap 4 ketika user pilih **"Punya Kendaraan Sendiri"**.

**Tidak butuh Google Maps API Key.** Cukup deeplink biasa.

### Format URL

```
https://maps.google.com/?q={latitude},{longitude}
```

Contoh untuk Pantai Tanjung Bira:
```
https://maps.google.com/?q=-5.6103,120.4586
```

### Yang Backend Sediakan

Field di tabel `destinations`:
```
latitude  : DECIMAL(10,8)  → -5.61034567
longitude : DECIMAL(11,8)  → 120.45867890
```

### Yang Frontend Lakukan

Frontend generate URL deeplink dari koordinat yang didapat API:
```javascript
const mapsUrl = `https://maps.google.com/?q=${destination.latitude},${destination.longitude}`;
window.open(mapsUrl, '_blank');
```

---

## 2. WhatsApp Deeplink (Pemesanan Driver)

Digunakan ketika user klik **"Pesan via WhatsApp"** setelah mengisi form driver.

**Tidak butuh WA API berbayar.** Cukup deeplink biasa.

### Format URL

```
https://wa.me/{phone}?text={encoded_message}
```

Contoh:
```
https://wa.me/628123456789?text=Halo%20Admin%20TanaOgi...
```

### Nomor Phone

Format internasional tanpa `+` dan tanpa `0` di depan:
```
08123456789  →  628123456789
```

### Template Pesan

Template disimpan di tabel `settings` dengan key `wa_template`.
Frontend ambil template via:
```
GET /api/v1/settings/whatsapp
```

Template menggunakan placeholder `{variable}`:
```
Halo Admin TanaOgi 👋

Saya ingin memesan kendaraan:

🚗 Kendaraan  : {vehicle}
📍 Tujuan     : {destination}
📅 Berangkat  : {departure_date}
📅 Pulang     : {return_date}
🏨 Menginap   : {accommodation}
📌 Jemput di  : {pickup_location}

Nama : {user_name}
No HP: {user_phone}
```

Jika tidak menginap, baris `🏨 Menginap` dan `📌 Jemput di` diganti otomatis:
```
📌 Jemput di  : {destination_name}, {destination_address}
```

### Yang Frontend Lakukan

```javascript
const message = template
  .replace('{vehicle}', selectedVehicle.name)
  .replace('{destination}', destination.name)
  .replace('{departure_date}', formatDate(departureDate))
  .replace('{return_date}', formatDate(returnDate))
  .replace('{accommodation}', selectedAccommodation?.name || '-')
  .replace('{pickup_location}', pickupLocation)
  .replace('{user_name}', userName)
  .replace('{user_phone}', userPhone);

const waUrl = `https://wa.me/${adminWhatsapp}?text=${encodeURIComponent(message)}`;
window.open(waUrl, '_blank');
```

---

## 3. WaAPI (Reminder ke Driver)

Digunakan oleh Laravel Scheduler untuk kirim reminder otomatis ke nomor WA driver.

### Endpoint
POST https://waapi.fyas.my.id/api/whatsapp/send-message

### Auth
Header: X-API-Key: {WAAPI_KEY}

### Request Body
```json
{
  "number": "628xxx",
  "message": "teks pesan"
}
```

### Response
```json
{ "success": true }
```
atau jika gagal:
```json
{ "success": false, "error": "pesan error" }
```

### Rate Limit
1 pesan per 30 detik per API key.
Jika melewati batas, request akan ditolak sementara.
Scheduler harus mempertimbangkan delay antar pengiriman — gunakan sleep(1) jika kirim banyak reminder sekaligus.
Jaga kerahasiaan API key.

### Setup
1. Daftar di https://waapi.fyas.my.id/dashboard — dapatkan API key
2. Hubungkan device WhatsApp: Settings → Linked Devices → Link a Device → scan QR
3. Pastikan status koneksi aktif sebelum scheduler berjalan
4. Set di .env:
WAAPI_URL=https://waapi.fyas.my.id
WAAPI_KEY=wapi_your_key_here

### Cara Kirim via Laravel (Http facade)
```php
Http::withHeaders([
    'Content-Type' => 'application/json',
    'X-API-Key'    => config('services.waapi.key'),
])->post(config('services.waapi.url') . '/api/whatsapp/send-message', [
    'number'  => $phone,
    'message' => $message,
]);
```

### Endpoint Lain (tersedia tapi tidak dipakai backend)
- GET  /api/whatsapp/status  — cek status koneksi device
- GET  /api/whatsapp/qr      — ambil QR code untuk link device
- POST /api/whatsapp/send-image — kirim gambar (number, imageUrl, caption)
- POST /api/whatsapp/send-group-message — kirim ke grup (groupId, message)

### Format Nomor WA
Format internasional tanpa + dan tanpa 0 di depan:
08123456789  →  628123456789

---

## Ringkasan Integrasi

| Integrasi | Tujuan | Biaya | Butuh API Key |
|---|---|---|---|
| Google Maps deeplink | Navigasi user ke destinasi | Gratis | Tidak |
| WhatsApp deeplink | Pemesanan driver oleh user | Gratis | Tidak |
| WaAPI | Reminder otomatis ke driver | Gratis (self-hosted) | Ya |
