# Deployment Guide

## Requirements

### Server Requirements
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ (for building assets)
- Web server (Nginx recommended)
- MySQL 8.0+ or PostgreSQL 13+

### PHP Extensions
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

---

## Production Setup

### 1. Clone and Install

```bash
# Clone repository
git clone https://github.com/your-org/sentinel.git /var/www/sentinel
cd /var/www/sentinel

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install and build frontend
npm ci
npm run build
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME=Sentinel
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sentinel.yourcompany.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sentinel
DB_USERNAME=sentinel_user
DB_PASSWORD=secure_password

SESSION_DRIVER=database
CACHE_DRIVER=database
QUEUE_CONNECTION=database
```

### 3. Database Setup

```bash
# Create database
mysql -u root -p -e "CREATE DATABASE sentinel;"
mysql -u root -p -e "CREATE USER 'sentinel_user'@'localhost' IDENTIFIED BY 'secure_password';"
mysql -u root -p -e "GRANT ALL PRIVILEGES ON sentinel.* TO 'sentinel_user'@'localhost';"

# Run migrations
php artisan migrate --force

# Seed initial admin (optional)
php artisan db:seed --force
```

### 4. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/sentinel
sudo chmod -R 755 /var/www/sentinel
sudo chmod -R 775 /var/www/sentinel/storage
sudo chmod -R 775 /var/www/sentinel/bootstrap/cache
```

### 5. Optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name sentinel.yourcompany.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name sentinel.yourcompany.com;
    root /var/www/sentinel/public;

    ssl_certificate /etc/ssl/certs/sentinel.crt;
    ssl_certificate_key /etc/ssl/private/sentinel.key;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Supervisor (Queue Worker)

Create `/etc/supervisor/conf.d/sentinel-worker.conf`:

```ini
[program:sentinel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sentinel/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sentinel/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sentinel-worker:*
```

---

## Scheduled Tasks

Add to crontab (`crontab -e -u www-data`):

```cron
* * * * * cd /var/www/sentinel && php artisan schedule:run >> /dev/null 2>&1
```

---

## SSL Certificate

Using Let's Encrypt:

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d sentinel.yourcompany.com
```

---

## Monitoring

### Health Check

```bash
curl https://sentinel.yourcompany.com/up
```

### Log Files

- **Application:** `storage/logs/laravel.log`
- **Queue Worker:** `storage/logs/worker.log`
- **Nginx:** `/var/log/nginx/access.log`, `/var/log/nginx/error.log`

---

## Updating

```bash
cd /var/www/sentinel

# Maintenance mode
php artisan down

# Pull updates
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Migrate database
php artisan migrate --force

# Clear caches
php artisan optimize:clear
php artisan optimize

# Back online
php artisan up
```

---

## Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] HTTPS enabled with valid certificate
- [ ] Database credentials secured
- [ ] File permissions properly set
- [ ] Firewall configured (only ports 80, 443, 22)
- [ ] Regular backups configured
- [ ] Log rotation enabled
- [ ] Rate limiting on API endpoints
