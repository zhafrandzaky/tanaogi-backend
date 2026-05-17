# AUTHENTICATION.md — Auth & Role TanaOgi

## Strategi

- **Auth**: Laravel Sanctum (token-based)
- **Role**: Spatie Laravel Permission
- **User yang login**: Hanya admin — user biasa (wisatawan) tidak perlu login
- **Token**: Personal Access Token, tidak ada expiry default (bisa diset)

---

## Setup Sanctum

```bash
php artisan install:api
```

`.env`:
```env
SANCTUM_STATEFUL_DOMAINS=localhost:5173,tanaogi.com
```

`config/sanctum.php`:
```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),
```

---

## Setup Spatie Laravel Permission

```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\LaravelPermission\PermissionServiceProvider"
php artisan migrate
```

`app/Models/User.php`:
```php
use Spatie\LaravelPermission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles;
}
```

---

## Role yang Ada

| Role | Akses |
|---|---|
| `admin` | Full akses semua endpoint admin |

Hanya ada 1 role untuk MVP. Bisa ditambah role lain (misal `super_admin`) di Phase 2.

---

## Flow Login

```
POST /api/v1/auth/login
Body: { email, password }
        ↓
AuthController → AuthService
        ↓
Validasi credentials
        ↓
Cek user punya role admin
        ↓
Generate Sanctum token
        ↓
Return token + user data
```

### Request
```json
POST /api/v1/auth/login
{
  "email": "admin@tanaogi.com",
  "password": "password"
}
```

### Response Success
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "token": "1|laravel_sanctum_token_here",
    "user": {
      "id": "uuid",
      "name": "Admin TanaOgi",
      "email": "admin@tanaogi.com",
      "role": "admin"
    }
  }
}
```

### Response Error
```json
{
  "success": false,
  "message": "Email atau password salah",
  "errors": null
}
```

---

## Flow Logout

```
POST /api/v1/auth/logout
Header: Authorization: Bearer {token}
        ↓
Hapus token dari database
        ↓
Return success
```

---

## Middleware Auth

```php
// Protected route — harus login
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// Protected route — harus login DAN role admin
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    // semua endpoint admin
});
```

---

## Implementasi AuthService

```php
class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException('Email atau password salah');
        }

        if (!$user->hasRole('admin')) {
            throw new AuthorizationException('Anda tidak memiliki akses');
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return [
            'token' => $token,
            'user'  => $user,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
```

---

## Cara Frontend Kirim Token

Frontend menyimpan token setelah login dan menyertakannya di setiap request ke endpoint admin:

```
Authorization: Bearer 1|laravel_sanctum_token_here
Content-Type: application/json
```

---

## Seeder Admin Default

```php
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::create([
            'name'     => 'Admin TanaOgi',
            'email'    => 'admin@tanaogi.com',
            'password' => Hash::make('password'),
        ]);

        $user->assignRole('admin');
    }
}
```

**Ganti password default sebelum deploy ke production.**
