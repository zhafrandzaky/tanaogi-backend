# FEATURES.md — Alur Bisnis TanaOgi

Dokumen ini menjelaskan alur bisnis lengkap TanaOgi dari sudut pandang user dan kebutuhan API backend.

---

## Tahap 1 — Preloader

**User experience:**
- User buka website TanaOgi
- Layar penuh warna Orange `#F5401B` dengan logo dan teks animasi `"Memuat Keindahan Sulawesi Selatan..."`
- Browser mengunduh aset berat (video, gambar) di background
- Preloader fade-out 0.5 detik setelah semua aset selesai dimuat

**Kebutuhan backend:** Tidak ada. Murni frontend.

---

## Tahap 2 — Hero Video

**User experience:**
- Video fullscreen Sulawesi Selatan (Rammang-Rammang / Tana Toraja / Tanjung Bira)
- Overlay hitam transparan untuk keterbacaan teks
- Tombol `"Explore Now"` di tengah layar

**Kebutuhan backend:** Tidak ada. Asset video dikelola frontend.

---

## Tahap 3 — Eksplorasi Destinasi

**User experience:**
- User klik `"Explore Now"` → scroll ke bagian daftar kabupaten/kota
- User pilih kabupaten (Bulukumba, Toraja Utara, Maros, Makassar, dll)
- Tampil kartu-kartu destinasi wisata di kabupaten tersebut
- User klik kartu destinasi → masuk halaman detail

**Kebutuhan backend:**

```
GET /api/v1/regencies
→ Daftar semua kabupaten/kota Sulawesi Selatan

GET /api/v1/destinations?regency_id=xxx
→ Daftar destinasi per kabupaten
→ Include: nama, foto utama, harga tiket, slug

GET /api/v1/destinations/{slug}
→ Detail destinasi
→ Include: deskripsi, semua foto, harga tiket,
           fasilitas, koordinat, rute teks dari Makassar
```

---

## Tahap 4 — Halaman Detail & Pemesanan Driver

### 4A — Info Destinasi

Tampil di bagian atas halaman:
- Deskripsi lengkap destinasi
- Harga tiket masuk
- Fasilitas tersedia
- Foto-foto destinasi

### 4B — Keputusan Transportasi

**Pertanyaan:** `"Apakah Anda memiliki kendaraan sendiri?"`

#### Jika YA — Punya Kendaraan

Tampil:
- Ringkasan rute teks dari Makassar ke destinasi
- Tombol orange → Google Maps deeplink ke koordinat destinasi

**Kebutuhan backend:**
```
Field di tabel destinations:
- route_text      : ringkasan rute teks
- latitude        : koordinat GPS
- longitude       : koordinat GPS
```

#### Jika TIDAK — Butuh Driver

**Flow pemesanan driver (semua PP):**

```
1. Pilih jenis kendaraan:
   ○ Mobil (Avanza/Innova) — Rp xxx.xxx/hari
   ○ Bus Pariwisata         — Rp xxx.xxx/hari

2. Pilih tanggal berangkat

3. "Apakah menginap?"
   
   TIDAK:
   - Pilih tanggal pulang
   - Lokasi jemput = nama & alamat destinasi (otomatis)
   
   YA:
   - Pilih penginapan dari daftar rekomendasi
   - Pilih tanggal pulang (= tanggal check-out)
   - Lokasi jemput = nama & alamat penginapan (otomatis)

4. Tampil ringkasan pesanan:
   - Kendaraan
   - Tanggal berangkat
   - Tanggal pulang
   - Lokasi penjemputan

5. Klik "Pesan via WhatsApp"
   → WA Admin terbuka dengan teks otomatis lengkap
```

**Kebutuhan backend:**
```
GET /api/v1/vehicles
→ Daftar jenis kendaraan & harga

GET /api/v1/destinations/{slug}/accommodations
→ Daftar rekomendasi penginapan di sekitar destinasi
→ Include: nama, tipe, harga mulai, alamat
  (untuk otomatis isi lokasi penjemputan)

GET /api/v1/settings/whatsapp-template
→ Nomor WA admin & template pesan
```

**Template WA otomatis (digenerate frontend):**
```
"Halo Admin TanaOgi 👋

Saya ingin memesan kendaraan:

🚗 Kendaraan  : Mobil (Avanza/Innova)
📍 Tujuan     : Pantai Tanjung Bira
📅 Berangkat  : 20 Juni 2025
📅 Pulang     : 22 Juni 2025
🏨 Menginap   : Hakuna Matata Resort
📌 Jemput di  : Jl. Poros Bira, Bulukumba

Nama : [nama user]
No HP: [no user]"
```

---

## Tahap 5 — Rekomendasi Penginapan

**Catatan penting:** Bagian ini muncul SETELAH user selesai mengurus transportasi.
Jika user sudah pilih menginap di Tahap 4 (untuk lokasi jemput driver), bagian ini tetap tampil sebagai konfirmasi rekomendasi.

**User experience:**
- Pertanyaan: `"Apakah Anda berencana menginap?"`
- TIDAK → selesai (one-day trip)
- YA → tampil daftar rekomendasi penginapan

**Tampilan rekomendasi penginapan:**
```
○ Hakuna Matata Resort
  Tipe    : Resort
  Estimasi: Mulai Rp 450.000/malam

○ Bira Beach Hotel
  Tipe    : Hotel
  Estimasi: Mulai Rp 350.000/malam
```

**Tidak ada tombol booking, tidak ada link eksternal.**
Murni rekomendasi informasi saja — user mencari sendiri cara booking.

**Kebutuhan backend:**
```
GET /api/v1/destinations/{slug}/accommodations
→ Sama dengan endpoint di Tahap 4B
→ Sudah include nama, tipe, harga, alamat
```

---

## Admin Dashboard

Admin mengelola semua data melalui dashboard (endpoint prefix `/api/v1/admin/`).

### Kelola Konten

```
CRUD /api/v1/admin/regencies        → kabupaten/kota
CRUD /api/v1/admin/destinations     → destinasi wisata
CRUD /api/v1/admin/vehicles         → jenis kendaraan & harga
CRUD /api/v1/admin/drivers          → data driver
CRUD /api/v1/admin/accommodations   → penginapan rekomendasi
PUT  /api/v1/admin/settings         → nomor WA admin, template
```

### Kelola Pemesanan Driver

```
POST /api/v1/admin/driver-orders         → input order dari WA user
GET  /api/v1/admin/driver-orders         → list semua order
GET  /api/v1/admin/driver-orders/{id}    → detail order
PUT  /api/v1/admin/driver-orders/{id}    → update (assign driver, ubah status)
GET  /api/v1/admin/drivers/{id}/schedule → kalender availability driver
```

**Flow admin setelah terima WA dari user:**
1. Buka dashboard → input order baru
2. Lihat kalender driver
3. Pilih driver yang available di tanggal berangkat DAN tanggal pulang
4. Assign driver → kedua tanggal otomatis BLOCKED
5. WA manual ke user untuk konfirmasi

---

## Sistem Reminder Driver

Detail lengkap di `docs/SCHEDULER.md`.

**Ringkasan:**
- One-day trip → WA ke driver beberapa jam sebelum penjemputan pulang
- PP / Menginap → WA ke driver H-1 sebelum tanggal pulang jam 07.00 pagi
- Reminder dikirim via **Fonnte API** ke nomor WA driver yang assigned
