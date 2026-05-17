# STORAGE.md — R2 Cloudflare Storage TanaOgi

TanaOgi menggunakan **Cloudflare R2** untuk menyimpan semua file upload (foto destinasi).
R2 dipilih karena gratis 10GB, CDN global otomatis, dan tidak ada egress fee.

---

## Kenapa R2 Bukan Local Storage

Railway tidak punya persistent storage — setiap redeploy semua file di `storage/app` hilang.
R2 menjamin foto destinasi tetap ada meski redeploy berkali-kali.

---

## Setup R2 di Cloudflare

### 1. Buat R2 Bucket

- Login ke https://dash.cloudflare.com
- Pilih menu **R2 Object Storage**
- Klik **Create bucket**
- Nama bucket: `tanaogi-storage`
- Location: Auto (atau pilih Asia Pacific)

### 2. Buat API Token R2

- Di halaman R2 → **Manage R2 API tokens**
- Klik **Create API token**
- Permission: **Object Read & Write**
- Scope: **Specific bucket** → `tanaogi-storage`
- Salin:
  - `Access Key ID`
  - `Secret Access Key`
  - `Endpoint URL` → format: `https://{account_id}.r2.cloudflarestorage.com`

### 3. Setup Custom Domain (Opsional tapi Dianjurkan)

- Di halaman bucket `tanaogi-storage` → **Settings** → **Custom Domains**
- Tambah domain: `storage.tanaogi.com`
- Set DNS CNAME di Cloudflare domain kamu
- Ini membuat URL foto jadi: `https://storage.tanaogi.com/destinations/foto.jpg`

---

## Setup Laravel 13

### Install Package

R2 kompatibel dengan S3 API — cukup pakai AWS S3 driver bawaan Laravel:

```bash
composer require league/flysystem-aws-s3-v3
```

### Konfigurasi Filesystem

`config/filesystems.php`:
```php
'disks' => [
    // ...

    'r2' => [
        'driver'   => 's3',
        'key'      => env('CLOUDFLARE_R2_ACCESS_KEY'),
        'secret'   => env('CLOUDFLARE_R2_SECRET_KEY'),
        'region'   => 'auto',
        'bucket'   => env('CLOUDFLARE_R2_BUCKET'),
        'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
        'url'      => env('CLOUDFLARE_R2_URL'),
        'use_path_style_endpoint' => true,
    ],
],

'default' => env('FILESYSTEM_DISK', 'r2'),
```

### Environment Variables

`.env`:
```env
FILESYSTEM_DISK=r2

CLOUDFLARE_R2_ACCESS_KEY=your_access_key_id
CLOUDFLARE_R2_SECRET_KEY=your_secret_access_key
CLOUDFLARE_R2_BUCKET=tanaogi-storage
CLOUDFLARE_R2_ENDPOINT=https://{account_id}.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://storage.tanaogi.com
```

Jika tidak pakai custom domain, `CLOUDFLARE_R2_URL` pakai endpoint publik R2:
```env
CLOUDFLARE_R2_URL=https://pub-{hash}.r2.dev
```

---

## Cara Upload File di Service

```php
// app/Services/DestinationService.php

public function uploadImages(Destination $destination, array $files): Collection
{
    $uploaded = collect();

    foreach ($files as $file) {
        // Generate path: destinations/{destination_id}/{uuid}.{ext}
        $path = "destinations/{$destination->id}/" . Str::uuid() . '.' . $file->getClientOriginalExtension();

        // Upload ke R2
        Storage::disk('r2')->put($path, file_get_contents($file), 'public');

        // Generate URL publik
        $url = Storage::disk('r2')->url($path);

        // Simpan ke DB
        $image = DestinationImage::create([
            'destination_id' => $destination->id,
            'path'           => $path,
            'url'            => $url,
            'order'          => $destination->images()->count(),
        ]);

        $uploaded->push($image);
    }

    return $uploaded;
}

public function deleteImage(string $imageId): bool
{
    $image = DestinationImage::findOrFail($imageId);

    // Hapus dari R2
    Storage::disk('r2')->delete($image->path);

    // Hapus dari DB
    return $image->delete();
}
```

---

## Struktur Path di R2

```
tanaogi-storage/
└── destinations/
    └── {destination_id}/
        ├── {uuid}.jpg
        ├── {uuid}.jpg
        └── {uuid}.png
```

---

## Validasi Upload

`app/Http/Requests/Admin/UploadDestinationImageRequest.php`:
```php
public function rules(): array
{
    return [
        'images'   => ['required', 'array', 'max:10'],
        'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    ];
}

public function messages(): array
{
    return [
        'images.required'      => 'Minimal satu foto wajib diupload',
        'images.max'           => 'Maksimal 10 foto sekaligus',
        'images.*.image'       => 'File harus berupa gambar',
        'images.*.mimes'       => 'Format foto harus jpg, jpeg, png, atau webp',
        'images.*.max'         => 'Ukuran foto maksimal 2MB',
    ];
}
```

---

## Response URL Foto

URL foto disimpan langsung di DB kolom `url` tabel `destination_images`.
Frontend langsung pakai URL ini untuk tampilkan foto — tidak perlu generate ulang.

```json
{
  "id": "uuid",
  "name": "Pantai Tanjung Bira",
  "images": [
    "https://storage.tanaogi.com/destinations/uuid/foto1.jpg",
    "https://storage.tanaogi.com/destinations/uuid/foto2.jpg"
  ]
}
```

---

## Biaya R2 Cloudflare

| Item | Free Tier | Harga Setelah Free |
|---|---|---|
| Storage | 10 GB/bulan | $0.015/GB |
| Class A ops (upload) | 1 juta/bulan | $4.50/juta |
| Class B ops (download) | 10 juta/bulan | $0.36/juta |
| Egress (bandwidth) | Gratis | Gratis |

Untuk TanaOgi skala awal, free tier lebih dari cukup.

---

## Testing Upload Lokal

Untuk development lokal, bisa pakai disk `local` agar tidak perlu koneksi R2:

`.env.local` (override untuk dev):
```env
FILESYSTEM_DISK=local
```

Atau pakai **MinIO** (R2-compatible, bisa dijalankan via Docker):
```yaml
# tambahkan ke docker-compose.yml
minio:
  image: minio/minio
  ports:
    - "9000:9000"
    - "9001:9001"
  environment:
    MINIO_ROOT_USER: tanaogi
    MINIO_ROOT_PASSWORD: password
  command: server /data --console-address ":9001"
```

```env
# .env untuk dev dengan MinIO
FILESYSTEM_DISK=r2
CLOUDFLARE_R2_ACCESS_KEY=tanaogi
CLOUDFLARE_R2_SECRET_KEY=password
CLOUDFLARE_R2_BUCKET=tanaogi-storage
CLOUDFLARE_R2_ENDPOINT=http://minio:9000
CLOUDFLARE_R2_URL=http://localhost:9000/tanaogi-storage
```
