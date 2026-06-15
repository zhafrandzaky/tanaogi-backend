# ============================================================
# Multi-stage Dockerfile — TanaOgi Backend (Laravel 13 + PHP 8.4)
# ============================================================
# Stage "dev"    → local development via docker-compose (default target)
# Stage "production" → Railway deployment (nginx + php-fpm, port 8080)
# ============================================================

# --- Base: PHP extensions + system packages -----------------
FROM php:8.4-fpm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl default-mysql-client libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql zip bcmath opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# --- Dev stage: all composer deps (including dev) -----------
FROM base AS dev

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]

# --- Production stage: optimised, nginx + php-fpm ----------
FROM base AS production

# Install nginx and supervisor (single-container orchestration)
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx supervisor \
    && rm -rf /var/lib/apt/lists/* \
    && rm /etc/nginx/sites-enabled/default

# Composer: production-only deps
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-autoloader

# Application source
COPY . .

# Ensure storage/cache directories exist before artisan cache commands
RUN mkdir -p /var/www/storage/framework/views \
    /var/www/storage/framework/cache/data \
    /var/www/storage/framework/sessions \
    /var/www/storage/logs \
    /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

RUN composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Nginx config — listen on 8080, proxy PHP to local php-fpm
RUN printf 'server {\n\
    listen 8080;\n\
    server_name _;\n\
    root /var/www/public;\n\
    index index.php;\n\
\n\
    client_max_body_size 20M;\n\
\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
\n\
    location ~ \\.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_index index.php;\n\
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
\n\
    location ~ /\\.(?!well-known).* {\n\
        deny all;\n\
    }\n\
}\n' > /etc/nginx/sites-available/default

# Supervisor: run nginx + php-fpm + cron (Laravel scheduler) together
RUN printf '[supervisord]\n\
nodaemon=true\n\
logfile=/dev/null\n\
logfile_maxbytes=0\n\
\n\
[program:php-fpm]\n\
command=php-fpm\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:nginx]\n\
command=nginx -g "daemon off;"\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n\
\n\
[program:cron]\n\
command=sh -c "while true; do php /var/www/artisan schedule:run; sleep 60; done"\n\
autostart=true\n\
autorestart=true\n\
stdout_logfile=/dev/stdout\n\
stdout_logfile_maxbytes=0\n\
stderr_logfile=/dev/stderr\n\
stderr_logfile_maxbytes=0\n' > /etc/supervisor/conf.d/supervisord.conf

EXPOSE 8080

# Run migrations on start, then launch supervisor (nginx + php-fpm + cron)
CMD ["sh", "-c", "php artisan migrate --force && supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
