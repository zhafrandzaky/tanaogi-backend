# TEAM.md — Panduan Tim TanaOgi Backend

Dokumen ini untuk semua anggota tim. Baca sekali di awal, simpan sebagai referensi.

---

## 1. Selamat Datang 👋

**TanaOgi** adalah platform wisata Sulawesi Selatan. Repo ini adalah **backend REST API** — frontend ada di repo terpisah.

Sebelum mulai, tentukan setup kamu:

| Setup | Panduan |
|---|---|
| Linux / Docker (senior) | `docs/DOCKER.md` |
| Windows / Laragon atau XAMPP | `docs/LOCAL_SETUP.md` |

Keduanya pakai R2 Cloudflare untuk storage foto — tidak ada MinIO.

---

## 2. Setup Storage R2

Semua foto tersimpan di **Cloudflare R2**, bukan di komputer kamu.

Yang perlu kamu tahu:

- Dev bucket: `tanaogi-storage-dev` — dipakai bersama semua developer
- Foto yang kamu upload langsung bisa dilihat senior, dan sebaliknya
- **Credentials R2 (`CLOUDFLARE_R2_ACCESS_KEY`, `SECRET_KEY`, `ENDPOINT`) minta dari senior via chat**
- Isi di `.env` lokal kamu — **jangan pernah commit ke repo**

---

## 3. Cara Kerja Tim (Git Workflow)

### Sebelum Mulai Task Apapun — WAJIB

```bash
git checkout main
git pull origin main
```

Kenapa penting? Kalau kamu langsung buat branch dari main yang lama, kamu akan kerja di atas kode yang sudah outdated. Nanti conflict banyak saat mau merge.

---

### Mulai Task Baru

1. Cek `docs/TASKS.md` — lihat task yang assign ke kamu
2. Baca spesifikasi teknis di `docs/TASKS_DETAIL.md` (atau minta senior kirim prompt)
3. Buat branch baru:
   ```bash
   git checkout -b feat/task-XXX-nama-singkat
   ```
4. Minta prompt task dari senior
5. Paste prompt ke Claude Code
6. Kirim summary hasil ke senior untuk direview

---

### Setelah Diapprove Senior

```bash
# 1. Ambil update terbaru dari main
git fetch origin

# 2. Rebase branch kamu di atas main terbaru
git rebase origin/main

# 3. Resolve konflik jika ada (lihat Section 5)

# 4. Push ke GitHub
git push origin feat/task-XXX-nama-singkat

# 5. Buat Pull Request di GitHub
#    Title: singkat dan jelas
#    Base: main ← Compare: feat/task-XXX-nama-singkat
#    Klik "Create Pull Request"

# 6. Kabari senior bahwa PR sudah siap
```

---

### Setelah PR Di-merge

```bash
git checkout main
git pull origin main
php artisan migrate        # jika ada migration baru
composer install           # jika ada package baru
php artisan config:clear
```

---

### Jika Senior Merge Duluan ke Main

Senior akan kabari via chat. Kamu lakukan:

```bash
git fetch origin
git rebase origin/main
```

---

## 4. Format Commit yang Benar

**Aturan:** satu baris saja, tidak ada tambahan apapun setelah pesan.

| Tipe | Kapan dipakai |
|---|---|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `chore` | Setup, konfigurasi, tooling |
| `migration` | Buat atau ubah tabel database |
| `refactor` | Rapikan kode tanpa ubah fungsi |
| `docs` | Update dokumentasi |
| `test` | Tambah atau perbaiki test |

**Contoh yang benar:**

```
feat: add regency list endpoint
fix: resolve UUID casting in DriverOrder model
migration: create destinations table
chore: setup Docker environment
```

**Contoh yang salah:**

```
update file
fix
bismillah
feat: add auth

Co-authored-by: Claude <claude@anthropic.com>
```

---

## 5. Cara Resolve Konflik

Konflik terjadi kalau kamu dan senior edit file yang sama di baris yang sama. Jangan panik — ini normal.

**Contoh konflik di `routes/api_v1.php`:**

```
<<<<<<< HEAD
Route::get('/regencies', [RegencyController::class, 'index']);
=======
Route::get('/destinations', [DestinationController::class, 'index']);
>>>>>>> feat/task-010-destinations-crud
```

Artinya: HEAD (branch kamu) punya `regencies`, branch lain punya `destinations`. Keduanya benar — gabungkan:

```php
Route::get('/regencies', [RegencyController::class, 'index']);
Route::get('/destinations', [DestinationController::class, 'index']);
```

Hapus semua tanda `<<<<<<<`, `=======`, `>>>>>>>`.

Setelah selesai resolve semua file yang konflik:

```bash
git add nama-file-yang-sudah-difix
git rebase --continue
```

**Kalau bingung → screenshot → kirim ke senior SEBELUM lanjut.**
Jangan asal hapus kode yang tidak dipahami.

---

## 6. Aturan Wajib Tim

- ❌ Jangan push langsung ke `main` — selalu lewat branch dan PR
- ❌ Jangan mulai task yang sama dengan senior tanpa konfirmasi dulu
- ❌ Jangan commit `.env` ke repo
- ❌ Jangan commit folder `vendor/`
- ❌ Jangan commit credentials R2 (`CLOUDFLARE_R2_*`)
- ✅ Selalu `pull main` sebelum buat branch baru
- ✅ Selalu `rebase` sebelum push PR
- ✅ Kabari senior setelah PR siap di-review

---

## 7. Yang Harus Dilakukan Setelah Pull dari Main

Checklist setelah `git pull origin main`:

```bash
# Cek apakah ada migration baru
php artisan migrate

# Cek apakah ada package baru di composer.json
composer install

# Bersihkan config cache
php artisan config:clear
php artisan cache:clear
```

---

## 8. File Penting

| File | Fungsi |
|---|---|
| `.env` | Konfigurasi lokal — jangan di-push |
| `docs/TASKS.md` | Progress tracker task tim |
| `docs/TASKS_DETAIL.md` | Spesifikasi teknis per task |
| `docs/LOCAL_SETUP.md` | Setup Laragon atau XAMPP (Windows) |
| `docs/DOCKER.md` | Setup Docker (Linux/senior) |
| `docs/STORAGE.md` | Cara kerja R2 storage |
| `routes/api_v1.php` | Semua endpoint API |
| `database/migrations/` | Script tabel database |

---

## 9. Perintah Harian (Cheatsheet)

```bash
# Database
php artisan migrate                    # jalankan migration baru
php artisan migrate:fresh --seed       # reset + isi ulang data
php artisan db:seed                    # isi ulang data tanpa reset

# Routes
php artisan route:list                 # lihat semua endpoint

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Test
php artisan test
php artisan test --filter NamaTest

# Generate file
php artisan make:model NamaModel -m
php artisan make:controller Api/V1/NamaController
php artisan make:request NamaRequest
php artisan make:resource NamaResource

# Git (harian)
git status                             # lihat perubahan
git diff                               # lihat isi perubahan
git log --oneline -10                  # lihat 10 commit terakhir
git fetch origin                       # ambil update dari remote
git rebase origin/main                 # update branch dengan main terbaru
```

---

## 10. Error Umum dan Solusinya

| Error | Solusi |
|---|---|
| `Class not found` | `composer dump-autoload` |
| `Table not found` | `php artisan migrate` |
| `Route not found` | `php artisan route:clear` |
| `MySQL tidak bisa connect` | Pastikan MySQL service running, cek `DB_HOST=127.0.0.1` (Laragon/XAMPP) atau `DB_HOST=mysql` (Docker) |
| `Upload foto gagal` | Isi credentials R2 di `.env` — minta dari senior |
| `Key too long saat migrate` | Tambah `Schema::defaultStringLength(191)` di `AppServiceProvider::boot()` |
| `composer not found` | Install dari https://getcomposer.org/download/ |
| `php not found` | Buka terminal dari Laragon, atau tambah PHP ke PATH Windows |

---

## 11. Komunikasi Tim

- Kabari senior **sebelum mulai task** — pastikan tidak ada yang kerjakan task yang sama
- Kabari senior setelah **PR siap** di-review
- Kalau stuck lebih dari **30 menit** → screenshot error → tanya senior
- Jangan diam-diam fix kode yang dibuat senior — diskusi dulu
