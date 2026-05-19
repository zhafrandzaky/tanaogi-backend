# STORAGE.md — R2 Cloudflare Storage TanaOgi

## Overview

TanaOgi menggunakan **Cloudflare R2** untuk semua environment — development dan production.
Tidak ada MinIO. Tidak ada local disk. Semua developer langsung ke R2.

---

## Dua Bucket R2

| Bucket | Dipakai Oleh | Domain |
|---|---|---|
| `tanaogi-storage-dev` | Semua developer (lokal) | `dev-storage.tanaogi.zyy.my.id` |
| `tanaogi-storage` | Production (Railway) | `storage.tanaogi.zyy.my.id` |

---

## Kenapa Langsung R2 untuk Development

- Tidak perlu MinIO atau setup storage lokal
- URL foto sama di semua mesin developer
- Foto yang diupload developer A langsung bisa dilihat developer B
- Lebih dekat dengan environment production
- Developer Windows (Laragon/XAMPP) tidak perlu setup apapun selain isi `.env`

---

## Setup R2 di Cloudflare (dilakukan sekali oleh senior)

1. Login ke https://dash.cloudflare.com → **R2 Object Storage**
2. Buat 2 bucket:
   - `tanaogi-storage-dev` (untuk development semua developer)
   - `tanaogi-storage` (untuk production Railway)
3. Buat API Token:
   - Di halaman R2 → **Manage R2 API tokens** → **Create API token**
   - Permission: **Object Read & Write**
   - Scope: kedua bucket
   - Salin: `Access Key ID`, `Secret Access Key`, `Endpoint URL`
4. Setup custom domain:
   - `dev-storage.tanaogi.zyy.my.id` → bucket `tanaogi-storage-dev`
   - `storage.tanaogi.zyy.my.id` → bucket `tanaogi-storage`
   - Set DNS CNAME di Cloudflare domain

---

## .env untuk Development (Docker dan Laragon/XAMPP — sama)

```env
FILESYSTEM_DISK=r2

# R2 dev credentials — dapat dari senior via chat, jangan commit ke repo
CLOUDFLARE_R2_ACCESS_KEY=dev_access_key_dari_senior
CLOUDFLARE_R2_SECRET_KEY=dev_secret_key_dari_senior
CLOUDFLARE_R2_BUCKET=tanaogi-storage-dev
CLOUDFLARE_R2_ENDPOINT=https://{account_id}.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://dev-storage.tanaogi.zyy.my.id
```

**Penting:** credentials ini dishare senior ke developer via chat — **JANGAN commit ke repo**.

---

## .env untuk Production (Railway)

```env
FILESYSTEM_DISK=r2
CLOUDFLARE_R2_BUCKET=tanaogi-storage
CLOUDFLARE_R2_URL=https://storage.tanaogi.zyy.my.id
```

Gunakan credentials terpisah dari dev (buat API token berbeda di Cloudflare).

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
tanaogi-storage-dev/          ← development
└── destinations/
    └── {destination_id}/
        ├── {uuid}.jpg
        └── {uuid}.png

tanaogi-storage/              ← production
└── destinations/
    └── {destination_id}/
        ├── {uuid}.jpg
        └── {uuid}.png
```

---

## Membersihkan Dev Bucket

Sesekali hapus foto dummy di dev bucket:
- Login Cloudflare Dashboard → R2 → `tanaogi-storage-dev`
- Hapus folder `destinations/` yang tidak dipakai

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
    "https://dev-storage.tanaogi.zyy.my.id/destinations/uuid/foto1.jpg",
    "https://dev-storage.tanaogi.zyy.my.id/destinations/uuid/foto2.jpg"
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
