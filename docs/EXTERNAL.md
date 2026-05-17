# EXTERNAL.md — Integrasi Eksternal TanaOgi

TanaOgi hanya menggunakan 2 integrasi eksternal: Google Maps (deeplink) dan WhatsApp (deeplink + Fonnte API untuk reminder).

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

## 3. Fonnte API (Reminder ke Driver)

Digunakan oleh **Laravel Scheduler** untuk kirim reminder otomatis ke nomor WA driver.

**Ini satu-satunya API berbayar** — ~Rp100-150rb/bulan.

### Setup Fonnte

1. Daftar di https://fonnte.com
2. Sambungkan nomor WA admin/bisnis TanaOgi
3. Salin API Token
4. Set di `.env`:
```env
FONNTE_TOKEN=your_fonnte_token_here
```

### Cara Kirim Pesan via Fonnte

```php
// app/Services/WhatsappService.php

public function send(string $phone, string $message): bool
{
    try {
        $response = Http::withHeaders([
            'Authorization' => config('services.fonnte.token'),
        ])->post('https://api.fonnte.com/send', [
            'target'  => $phone,   // format: 628xxx (tanpa +)
            'message' => $message,
        ]);

        return $response->successful();
    } catch (\Exception $e) {
        Log::error('Fonnte API error: ' . $e->getMessage());
        return false;
    }
}
```

`config/services.php`:
```php
'fonnte' => [
    'token' => env('FONNTE_TOKEN'),
],
```

### Format Pesan Reminder ke Driver

**One-day trip** (beberapa jam sebelum penjemputan):
```
Reminder TanaOgi 🔔

Kamu perlu menjemput penumpang hari ini:

👤 Nama    : Budi Santoso
📌 Lokasi  : Pantai Tanjung Bira
📞 No HP   : 08123456789

Pastikan hadir tepat waktu ya!
```

**PP / Menginap** (H-1 sebelum tanggal pulang):
```
Reminder TanaOgi 🔔

Besok kamu perlu menjemput penumpang:

👤 Nama    : Budi Santoso
📌 Lokasi  : Hakuna Matata Resort
             Jl. Poros Bira, Bulukumba
📅 Tanggal : 22 Juni 2025
📞 No HP   : 08123456789

Pastikan hadir tepat waktu ya!
```

### Format Nomor WA Driver

Simpan di tabel `drivers` kolom `phone` dengan format internasional:
```
08123456789  →  628123456789
```

Validasi di FormRequest:
```php
'phone' => ['required', 'string', 'regex:/^62[0-9]{9,12}$/'],
```

---

## Ringkasan Integrasi

| Integrasi | Tujuan | Biaya | Butuh API Key |
|---|---|---|---|
| Google Maps deeplink | Navigasi user ke destinasi | Gratis | ❌ |
| WhatsApp deeplink | Pemesanan driver oleh user | Gratis | ❌ |
| Fonnte API | Reminder otomatis ke driver | ~Rp150rb/bln | ✅ |
