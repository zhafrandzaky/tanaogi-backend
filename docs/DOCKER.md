# DOCKER.md — Setup Docker TanaOgi

## Struktur File Docker

```
tanaogi-backend/
├── docker-compose.yml
├── Dockerfile
└── docker/
    └── nginx/
        └── default.conf
```

---

## Dockerfile

```dockerfile
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl default-mysql-client libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --optimize-autoloader --no-dev

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
```

---

## docker-compose.yml

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: tanaogi_app
    restart: unless-stopped
    volumes:
      - .:/var/www
      - ./storage:/var/www/storage
    networks:
      - tanaogi_network
    depends_on:
      - mysql
    environment:
      - APP_ENV=local

  nginx:
    image: nginx:alpine
    container_name: tanaogi_nginx
    restart: unless-stopped
    ports:
      - "8000:80"
    volumes:
      - .:/var/www
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - tanaogi_network
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: tanaogi_mysql
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      MYSQL_DATABASE: tanaogi
      MYSQL_USER: tanaogi_user
      MYSQL_PASSWORD: secret
      MYSQL_ROOT_PASSWORD: rootsecret
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - tanaogi_network
    command: --default-authentication-plugin=mysql_native_password

networks:
  tanaogi_network:
    driver: bridge

volumes:
  mysql_data:
```

---

## docker/nginx/default.conf

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/public;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## .env untuk Docker (Development)

```env
APP_NAME=TanaOgi
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173
MAINTENANCE_SECRET=rahasia_tanaogi_admin_dev

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=tanaogi
DB_USERNAME=tanaogi_user
DB_PASSWORD=secret

SANCTUM_STATEFUL_DOMAINS=localhost:5173

# R2 dev credentials — dapat dari senior via chat, jangan commit ke repo
FILESYSTEM_DISK=r2
CLOUDFLARE_R2_ACCESS_KEY=
CLOUDFLARE_R2_SECRET_KEY=
CLOUDFLARE_R2_BUCKET=tanaogi-storage-dev
CLOUDFLARE_R2_ENDPOINT=https://{account_id}.r2.cloudflarestorage.com
CLOUDFLARE_R2_URL=https://dev-storage.tanaogi.zyy.my.id

ADMIN_WHATSAPP=628xxxxxxxxxx
WAAPI_URL=https://waapi.fyas.my.id
WAAPI_KEY=wapi_your_key_here

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS=noreply@tanaogi.zyy.my.id
MAIL_FROM_NAME=TanaOgi
```

---

## Perintah Harian

```bash
# Jalankan semua container
docker compose up -d

# Stop semua container
docker compose down

# Masuk ke container app
docker compose exec app bash

# Lihat log
docker compose logs -f app
docker compose logs -f mysql

# Restart container tertentu
docker compose restart app

# Jalankan artisan
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan route:list
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# Jalankan scheduler manual
docker compose exec app php artisan schedule:run
docker compose exec app php artisan reminders:send-driver

# Akses MySQL
docker compose exec mysql mysql -u tanaogi_user -psecret tanaogi
```

---

## Troubleshooting

### Permission error di storage
```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Upload foto gagal di development
Pastikan `CLOUDFLARE_R2_*` sudah diisi di `.env` (minta dari senior via chat).
Cek `FILESYSTEM_DISK=r2` dan bukan `local`.

### MySQL tidak bisa connect
Pastikan `DB_HOST=mysql` (nama service di docker-compose, bukan `localhost` atau `127.0.0.1`).
Tunggu beberapa detik setelah `docker compose up -d` — MySQL butuh waktu init sebelum siap menerima koneksi.

### Tabel tidak ada setelah up
```bash
docker compose exec app php artisan migrate
```
