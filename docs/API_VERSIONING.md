# API_VERSIONING.md — Strategi Versioning API TanaOgi

## Strategi

TanaOgi menggunakan **URL-based versioning** — versi ada di path URL.

```
https://api.tanaogi.zyy.my.id/api/v1/destinations
https://api.tanaogi.zyy.my.id/api/v2/destinations  ← nanti jika ada breaking change
```

---

## Struktur Routes

```
routes/
├── api.php        ← entry point, load semua versi
├── api_v1.php     ← semua endpoint v1
└── api_v2.php     ← nanti jika dibutuhkan
```

`routes/api.php`:
```php
Route::prefix('v1')->group(base_path('routes/api_v1.php'));
```

`routes/api_v1.php`:
```php
// Public endpoints
Route::get('/regencies', [RegencyController::class, 'index']);
Route::get('/destinations', [DestinationController::class, 'index']);
Route::get('/destinations/{slug}', [DestinationController::class, 'show']);
Route::get('/destinations/{slug}/accommodations', [AccommodationController::class, 'byDestination']);
Route::get('/vehicles', [VehicleController::class, 'index']);
Route::get('/settings/whatsapp', [SettingController::class, 'whatsapp']);
Route::get('/maintenance/status', [MaintenanceController::class, 'status']);
Route::get('/health', fn() => response()->json(['status' => 'ok', 'service' => 'TanaOgi API']));

// Auth
Route::post('/auth/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// Admin (protected)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('regencies', Admin\RegencyController::class);
    Route::patch('regencies/{id}/toggle-active', [Admin\RegencyController::class, 'toggleActive']);

    Route::apiResource('destinations', Admin\DestinationController::class);
    Route::patch('destinations/{id}/toggle-active', [Admin\DestinationController::class, 'toggleActive']);
    Route::post('destinations/{id}/images', [Admin\DestinationController::class, 'uploadImages']);
    Route::delete('destinations/{id}/images/{imageId}', [Admin\DestinationController::class, 'deleteImage']);

    Route::apiResource('drivers', Admin\DriverController::class);
    Route::patch('drivers/{id}/toggle-active', [Admin\DriverController::class, 'toggleActive']);
    Route::get('drivers/{id}/schedule', [Admin\DriverController::class, 'schedule']);

    Route::apiResource('driver-orders', Admin\DriverOrderController::class);
    Route::apiResource('accommodations', Admin\AccommodationController::class);
    Route::patch('accommodations/{id}/toggle-active', [Admin\AccommodationController::class, 'toggleActive']);

    Route::apiResource('vehicles', Admin\VehicleController::class);
    Route::patch('vehicles/{id}/toggle-active', [Admin\VehicleController::class, 'toggleActive']);

    Route::apiResource('users', Admin\UserController::class);

    Route::get('settings', [Admin\SettingController::class, 'index']);
    Route::put('settings', [Admin\SettingController::class, 'update']);

    Route::get('maintenance/status', [Admin\MaintenanceController::class, 'status']);
    Route::post('maintenance/enable', [Admin\MaintenanceController::class, 'enable']);
    Route::post('maintenance/disable', [Admin\MaintenanceController::class, 'disable']);
});
```

---

## Kapan Naik Versi

Naik ke v2 **hanya** jika ada **breaking change**:

| Perubahan | Breaking? | Naik Versi? |
|---|---|---|
| Tambah field baru di response | Tidak | Tidak |
| Tambah endpoint baru | Tidak | Tidak |
| Hapus field dari response | Ya | Ya |
| Ubah nama field | Ya | Ya |
| Ubah tipe data field | Ya | Ya |
| Ubah struktur response | Ya | Ya |
| Hapus endpoint | Ya | Ya |

---

## Format Response Standar

Semua response wajib menggunakan format ini via `Traits/ApiResponse.php`.

### Aturan Wajib Response

- TIDAK BOLEH ada emoji di field `message`, `errors`, atau key apapun di response JSON
- TIDAK BOLEH ada karakter unicode dekoratif di response
- Semua teks response harus plain text ASCII atau UTF-8 tanpa emoji
- Exception: nilai dari database (misal konten template WA) boleh mengandung emoji karena itu data user, bukan response sistem

### Success — Single Data
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": {
    "id": "uuid",
    "name": "Pantai Tanjung Bira"
  }
}
```

### Success — Collection
```json
{
  "success": true,
  "message": "Data berhasil diambil",
  "data": [
    { "id": "uuid", "name": "Pantai Tanjung Bira" }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

### Error — Validasi
```json
{
  "success": false,
  "message": "Data tidak valid",
  "errors": {
    "name": ["Nama wajib diisi"],
    "regency_id": ["Kabupaten tidak ditemukan"]
  }
}
```

### Error — Not Found
```json
{
  "success": false,
  "message": "Destinasi tidak ditemukan",
  "errors": null
}
```

### Error — Unauthorized
```json
{
  "success": false,
  "message": "Anda tidak memiliki akses",
  "errors": null
}
```

### Error — Server Error
```json
{
  "success": false,
  "message": "Terjadi kesalahan pada server",
  "errors": null
}
```

---

## HTTP Status Code

| Situasi | Status Code |
|---|---|
| Success GET | 200 |
| Success POST (create) | 201 |
| Success PUT/PATCH | 200 |
| Success DELETE | 200 |
| Validasi gagal | 422 |
| Tidak login | 401 |
| Tidak punya akses | 403 |
| Data tidak ditemukan | 404 |
| Server error | 500 |

---

## ApiResponse Trait

```php
// app/Traits/ApiResponse.php
trait ApiResponse
{
    protected function success($data = null, string $message = 'Berhasil', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function error(string $message, $errors = null, int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    protected function paginated($resource, string $message = 'Data berhasil diambil'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $resource->items(),
            'meta'    => [
                'current_page' => $resource->currentPage(),
                'last_page'    => $resource->lastPage(),
                'per_page'     => $resource->perPage(),
                'total'        => $resource->total(),
            ],
        ]);
    }
}
```
