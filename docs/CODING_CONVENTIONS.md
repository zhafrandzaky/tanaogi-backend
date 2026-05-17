# CODING_CONVENTIONS.md — Konvensi Kode TanaOgi

## Penamaan

```
Model             : Destination          (PascalCase, singular)
Controller        : DestinationController
Service           : DestinationService
Repository        : DestinationRepository
Interface         : DestinationRepositoryInterface
FormRequest Store : StoreDestinationRequest
FormRequest Update: UpdateDestinationRequest
ApiResource       : DestinationResource
Collection        : DestinationCollection
Enum              : DriverOrderStatus
Migration         : create_destinations_table
Seeder            : DestinationSeeder
Table             : destinations          (snake_case, plural)
Column            : regency_id, is_active (snake_case)
Route name        : destinations.index
```

---

## Aturan Response — Wajib

### TIDAK BOLEH Ada Emoji di Response API

```php
// SALAH
return $this->success($data, 'Data berhasil diambil!');

// SALAH
return $this->error('Destinasi tidak ditemukan');

// BENAR — plain text saja
return $this->success($data, 'Data destinasi berhasil diambil');

// BENAR
return $this->error('Destinasi tidak ditemukan', null, 404);
```

Emoji hanya boleh ada di:
- Konten template WA yang disimpan di database (itu data user, bukan response sistem)
- Log message (tidak tampil ke user)

### TIDAK BOLEH Return JsonResponse Langsung di Controller

```php
// SALAH
return response()->json(['data' => $data]);

// BENAR — selalu pakai trait ApiResponse
return $this->success(DestinationResource::make($destination));
```

---

## Controller — Benar vs Salah

### Benar
```php
class DestinationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DestinationService $destinationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $destinations = $this->destinationService->getByRegency(
            $request->regency_id
        );

        return $this->success(
            DestinationCollection::make($destinations),
            'Data destinasi berhasil diambil'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $destination = $this->destinationService->findBySlug($slug);

        return $this->success(
            DestinationResource::make($destination),
            'Detail destinasi berhasil diambil'
        );
    }

    public function toggleActive(string $id): JsonResponse
    {
        $destination = $this->destinationService->toggleActive($id);

        return $this->success(
            ['id' => $destination->id, 'is_active' => $destination->is_active],
            'Status berhasil diubah'
        );
    }
}
```

### Salah — Logic di Controller
```php
public function index(Request $request): JsonResponse
{
    // SALAH: query langsung di controller
    $destinations = Destination::where('regency_id', $request->regency_id)->get();

    // SALAH: emoji di message
    return response()->json(['message' => 'Berhasil', 'data' => $destinations]);
}
```

---

## Service — Benar vs Salah

### Benar
```php
class DestinationService
{
    public function __construct(
        private readonly DestinationRepositoryInterface $destinationRepository
    ) {}

    public function getByRegency(?string $regencyId): Collection
    {
        if ($regencyId) {
            return $this->destinationRepository->findByRegency($regencyId);
        }
        return $this->destinationRepository->findAll();
    }

    public function findBySlug(string $slug): Destination
    {
        $destination = $this->destinationRepository->findBySlug($slug);

        if (!$destination) {
            throw new ModelNotFoundException('Destinasi tidak ditemukan');
        }

        return $destination;
    }

    public function toggleActive(string $id): Destination
    {
        $destination = $this->destinationRepository->findById($id);
        return $this->destinationRepository->update($destination, [
            'is_active' => !$destination->is_active,
        ]);
    }
}
```

### Salah — Query di Service
```php
class DestinationService
{
    public function getByRegency(?string $regencyId): Collection
    {
        // SALAH: query Eloquent langsung di service
        return Destination::where('regency_id', $regencyId)->get();
    }
}
```

---

## Repository — Benar vs Salah

### Benar
```php
interface DestinationRepositoryInterface
{
    public function findAll(): Collection;
    public function findByRegency(string $regencyId): Collection;
    public function findBySlug(string $slug): ?Destination;
    public function findById(string $id): ?Destination;
    public function create(array $data): Destination;
    public function update(Destination $destination, array $data): Destination;
    public function delete(Destination $destination): bool;
}

class DestinationRepository implements DestinationRepositoryInterface
{
    public function findByRegency(string $regencyId): Collection
    {
        return Destination::where('regency_id', $regencyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?Destination
    {
        return Destination::where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
```

### Salah — Logic di Repository
```php
class DestinationRepository
{
    public function findBySlug(string $slug): Destination
    {
        $destination = Destination::where('slug', $slug)->first();

        // SALAH: business logic di repository
        if (!$destination) {
            throw new ModelNotFoundException();
        }

        return $destination;
    }
}
```

---

## Model — Benar vs Salah

### Benar
```php
class Destination extends Model
{
    use HasUuids;

    protected $fillable = [
        'regency_id', 'name', 'slug', 'description',
        'ticket_price', 'facilities', 'route_text',
        'latitude', 'longitude', 'is_active',
    ];

    protected $casts = [
        'ticket_price' => 'integer',
        'latitude'     => 'float',
        'longitude'    => 'float',
        'is_active'    => 'boolean',
        'facilities'   => 'array',
    ];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
```

### Salah — Logic di Model
```php
class Destination extends Model
{
    // SALAH: business logic di model
    public function getAvailableDrivers(string $date): Collection
    {
        return Driver::whereDoesntHave('orders', function ($q) use ($date) {
            $q->where('departure_date', $date);
        })->get();
    }
}
```

---

## FormRequest

```php
class StoreDestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(): array
    {
        return [
            'regency_id'   => ['required', 'uuid', 'exists:regencies,id'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['required', 'string'],
            'ticket_price' => ['required', 'integer', 'min:0'],
            'latitude'     => ['required', 'numeric'],
            'longitude'    => ['required', 'numeric'],
            'route_text'   => ['required', 'string'],
            'is_active'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'regency_id.required' => 'Kabupaten wajib dipilih',
            'name.required'       => 'Nama destinasi wajib diisi',
            'ticket_price.min'    => 'Harga tiket tidak boleh negatif',
        ];
    }
}
```

---

## ApiResource

```php
class DestinationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'ticket_price'=> $this->ticket_price,
            'facilities'  => $this->facilities,
            'route_text'  => $this->route_text,
            'latitude'    => $this->latitude,
            'longitude'   => $this->longitude,
            'is_active'   => $this->is_active,
            'regency'     => RegencyResource::make($this->whenLoaded('regency')),
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
```

---

## Enum

```php
enum DriverOrderStatus: string
{
    case PENDING   = 'pending';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}

enum VehicleType: string
{
    case CAR = 'car';
    case BUS = 'bus';
}

enum AccommodationType: string
{
    case HOTEL    = 'hotel';
    case RESORT   = 'resort';
    case HOMESTAY = 'homestay';
}
```

---

## UUID Wajib

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Destination extends Model
{
    use HasUuids;
}

// Migration
$table->uuid('id')->primary();
$table->foreignUuid('regency_id')->constrained();
```

---

## Toggle Active — Pattern Standar

Setiap resource yang punya `is_active` wajib punya method toggle:

```php
// Controller
public function toggleActive(string $id): JsonResponse
{
    $model = $this->service->toggleActive($id);
    return $this->success(
        ['id' => $model->id, 'is_active' => $model->is_active],
        'Status berhasil diubah'
    );
}

// Service
public function toggleActive(string $id): Model
{
    $model = $this->repository->findById($id);
    return $this->repository->update($model, ['is_active' => !$model->is_active]);
}
```

---

## Batas Baris File

| File | Maksimal |
|---|---|
| Controller method | 30 baris |
| Controller class | 150 baris |
| Service method | 50 baris |
| Repository method | 30 baris |
| Model | 100 baris |

---

## Larangan Keras

- JANGAN tulis query Eloquent di Controller atau Service
- JANGAN tulis business logic di Controller, Repository, atau Model
- JANGAN return JsonResponse langsung — selalu pakai ApiResponse trait
- JANGAN pakai emoji atau karakter dekoratif di message response API
- JANGAN hardcode string — gunakan Enum atau config
- JANGAN skip FormRequest — semua input wajib divalidasi
- JANGAN gunakan auto-increment integer untuk primary key — wajib UUID
- JANGAN buat file melebihi batas baris yang ditentukan
