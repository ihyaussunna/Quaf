# QUAF Fest 09 — Production Deployment Guide
**Server Provisioning, Nginx Configuration, SQLite File Permissions, and Optimization**

---

## 1. Production Requirements
- **Server**: Ubuntu 22.04 / 24.04 LTS VPS or Laravel Cloud
- **PHP**: PHP 8.3-FPM (`php8.3-fpm`, `php8.3-sqlite3`, `php8.3-mbstring`, `php8.3-xml`, `php8.3-curl`)
- **Web Server**: Nginx
- **Node.js**: v20 LTS
- **Process Manager**: Systemd / Supervisor

---

## 2. Directory & Storage Permissions (Crucial for SQLite)
When deploying SQLite in production, the web server user (`www-data`) must have write permissions on **both the database file AND the parent directory** to allow SQLite to create `.wal` (Write-Ahead Logging) and `.shm` shared memory lock files:

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/quaf
sudo chmod -R 775 /var/www/quaf/storage
sudo chmod -R 775 /var/www/quaf/bootstrap/cache
sudo chmod -R 775 /var/www/quaf/database
sudo chmod 664 /var/www/quaf/database/database.sqlite
```

---

## 3. Production Nginx Configuration

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name fest.quaf.org;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name fest.quaf.org;
    root /var/www/quaf/public;

    ssl_certificate /etc/letsencrypt/live/fest.quaf.org/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/fest.quaf.org/privkey.pem;

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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 4. Production Build & Optimization Pipeline

Execute the following deployment script:
```bash
cd /var/www/quaf

# 1. Pull latest code
git pull origin main

# 2. Install production dependencies
composer install --no-dev --optimize-autoloader

# 3. Build frontend assets
npm ci
npm run build

# 4. Database migrations
php artisan migrate --force

# 5. Cache configurations & routes
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 6. Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

---

## 5. Automated SQLite Backup Cron
To ensure zero data loss during festival operations, set up an automated backup cron:
```bash
sudo crontab -e
```
Add:
```bash
*/30 * * * * sqlite3 /var/www/quaf/database/database.sqlite ".backup '/var/backups/quaf/quaf_$(date +\%Y\%m\%d_\%H\%M).sqlite'"
```
