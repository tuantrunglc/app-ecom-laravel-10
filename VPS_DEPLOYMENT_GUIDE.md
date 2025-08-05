# Hướng Dẫn Deploy Ứng Dụng Laravel E-commerce Lên VPS

## Mục Lục
1. [Yêu Cầu Hệ Thống](#yêu-cầu-hệ-thống)
2. [Chuẩn Bị VPS](#chuẩn-bị-vps)
3. [Phương Pháp 1: Deploy Với Docker](#phương-pháp-1-deploy-với-docker)
4. [Phương Pháp 2: Deploy Truyền Thống](#phương-pháp-2-deploy-truyền-thống)
5. [Cấu Hình SSL](#cấu-hình-ssl)
6. [Tối Ưu Hóa Production](#tối-ưu-hóa-production)
7. [Backup và Monitoring](#backup-và-monitoring)
8. [Troubleshooting](#troubleshooting)

---

## Yêu Cầu Hệ Thống

### Minimum Requirements
- **RAM**: 2GB (khuyến nghị 4GB+)
- **Storage**: 20GB SSD
- **CPU**: 2 cores
- **OS**: Ubuntu 20.04/22.04 LTS hoặc CentOS 8+

### Software Requirements
- PHP 8.1+
- MySQL 8.0+ hoặc MariaDB 10.6+
- Nginx hoặc Apache
- Redis (optional, cho cache)
- Node.js 16+ (cho build assets)
- Composer
- Git

---

## Chuẩn Bị VPS

### 1. Cập Nhật Hệ Thống
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y

# CentOS/RHEL
sudo yum update -y
```

### 2. Tạo User Deploy
```bash
# Tạo user mới
sudo adduser deployer
sudo usermod -aG sudo deployer

# Chuyển sang user deployer
su - deployer
```

### 3. Cài Đặt Firewall
```bash
# Ubuntu UFW
sudo ufw allow ssh
sudo ufw allow 80
sudo ufw allow 443
sudo ufw enable

# CentOS Firewalld
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

---

## Phương Pháp 1: Deploy Với Docker

### 1. Cài Đặt Docker và Docker Compose

#### Ubuntu:
```bash
# Cài đặt Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Cài đặt Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Logout và login lại để áp dụng group changes
exit
```

### 2. Clone và Cấu Hình Project
```bash
# Clone project
git clone <your-repository-url> /home/deployer/laravel-ecom
cd /home/deployer/laravel-ecom

# Copy và cấu hình environment
cp .env.example .env
```

### 3. Cấu Hình Environment (.env)
```env
APP_NAME="E-SHOP"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=eshop_db
DB_USERNAME=eshop_user
DB_PASSWORD=your_secure_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# PayPal Configuration
PAYPAL_MODE=live
PAYPAL_SANDBOX_CLIENT_ID=
PAYPAL_SANDBOX_CLIENT_SECRET=
PAYPAL_LIVE_CLIENT_ID=your_live_client_id
PAYPAL_LIVE_CLIENT_SECRET=your_live_client_secret

# Pusher Configuration (nếu sử dụng)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=mt1
```

### 4. Cấu Hình Docker Compose cho Production
Tạo file `docker-compose.prod.yml`:
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_app_prod
    restart: unless-stopped
    environment:
      - APP_ENV=production
    volumes:
      - ./storage:/var/www/html/storage
      - ./public/storage:/var/www/html/public/storage
    networks:
      - app-network
    depends_on:
      db:
        condition: service_healthy

  nginx:
    image: nginx:alpine
    container_name: laravel_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/nginx/sites/:/etc/nginx/sites-available/
      - ./docker/nginx/ssl/:/etc/nginx/ssl/
    networks:
      - app-network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: laravel_db_prod
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: eshop_db
      MYSQL_ROOT_PASSWORD: your_root_password
      MYSQL_PASSWORD: your_secure_password
      MYSQL_USER: eshop_user
    volumes:
      - db_data:/var/lib/mysql
      - ./database/e-shop.sql:/docker-entrypoint-initdb.d/e-shop.sql
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10

  redis:
    image: redis:7-alpine
    container_name: laravel_redis_prod
    restart: unless-stopped
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  db_data:
    driver: local
```

### 5. Cấu Hình Nginx
Tạo thư mục và file cấu hình:
```bash
mkdir -p docker/nginx/sites
```

Tạo file `docker/nginx/sites/laravel.conf`:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/html/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private must-revalidate auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

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

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 6. Deploy Application
```bash
# Build và start containers
docker-compose -f docker-compose.prod.yml up -d --build

# Generate application key
docker-compose -f docker-compose.prod.yml exec app php artisan key:generate

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Create storage link
docker-compose -f docker-compose.prod.yml exec app php artisan storage:link

# Cache configuration
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec app php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/
```

---

## Phương Pháp 2: Deploy Truyền Thống

### 1. Cài Đặt LEMP Stack

#### Ubuntu:
```bash
# Nginx
sudo apt install nginx -y

# PHP 8.1
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.1-fpm php8.1-mysql php8.1-mbstring php8.1-xml php8.1-gd php8.1-curl php8.1-zip php8.1-bcmath php8.1-intl php8.1-redis -y

# MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Redis
sudo apt install redis-server -y

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js
curl -fsSL https://deb.nodesource.com/setup_16.x | sudo -E bash -
sudo apt-get install -y nodejs
```

### 2. Cấu Hình MySQL
```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE eshop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'eshop_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON eshop_db.* TO 'eshop_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Deploy Application
```bash
# Clone project
cd /var/www
sudo git clone <your-repository-url> laravel-ecom
sudo chown -R deployer:www-data laravel-ecom
cd laravel-ecom

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run production

# Environment setup
cp .env.example .env
# Chỉnh sửa .env với thông tin production

# Laravel setup
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Cấu Hình Nginx
Tạo file `/etc/nginx/sites-available/laravel-ecom`:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/laravel-ecom/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|txt)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/laravel-ecom /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Cấu Hình SSL

### 1. Sử Dụng Let's Encrypt (Miễn Phí)
```bash
# Cài đặt Certbot
sudo apt install certbot python3-certbot-nginx -y

# Tạo SSL certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo crontab -e
# Thêm dòng sau:
0 12 * * * /usr/bin/certbot renew --quiet
```

### 2. Cấu Hình SSL cho Docker
Nếu sử dụng Docker, cập nhật nginx config để redirect HTTP sang HTTPS:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;
    
    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;
    
    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Rest of your configuration...
}
```

---

## Tối Ưu Hóa Production

### 1. PHP Configuration
Chỉnh sửa `/etc/php/8.1/fpm/php.ini`:
```ini
memory_limit = 512M
max_execution_time = 300
max_input_vars = 3000
upload_max_filesize = 64M
post_max_size = 64M
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### 2. MySQL Optimization
Chỉnh sửa `/etc/mysql/mysql.conf.d/mysqld.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
query_cache_type = 1
query_cache_size = 128M
```

### 3. Redis Configuration
Chỉnh sửa `/etc/redis/redis.conf`:
```ini
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

### 4. Laravel Optimization
```bash
# Tối ưu hóa autoloader
composer dump-autoload --optimize

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Queue workers (nếu sử dụng)
sudo nano /etc/systemd/system/laravel-worker.service
```

Nội dung file service:
```ini
[Unit]
Description=Laravel queue worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/laravel-ecom/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable laravel-worker
sudo systemctl start laravel-worker
```

---

## Backup và Monitoring

### 1. Database Backup Script
Tạo file `/home/deployer/backup-db.sh`:
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/deployer/backups"
DB_NAME="eshop_db"
DB_USER="eshop_user"
DB_PASS="your_secure_password"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Remove backups older than 7 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: db_backup_$DATE.sql.gz"
```

```bash
chmod +x /home/deployer/backup-db.sh

# Crontab cho backup hàng ngày
crontab -e
# Thêm dòng:
0 2 * * * /home/deployer/backup-db.sh
```

### 2. File Backup
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/home/deployer/backups"
APP_DIR="/var/www/laravel-ecom"

# Backup storage và uploads
tar -czf $BACKUP_DIR/files_backup_$DATE.tar.gz -C $APP_DIR storage/app/public

# Remove old file backups
find $BACKUP_DIR -name "files_backup_*.tar.gz" -mtime +7 -delete
```

### 3. Monitoring với Supervisor
```bash
sudo apt install supervisor -y

# Tạo config cho queue worker
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laravel-ecom/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/laravel-ecom/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

---

## Troubleshooting

### 1. Lỗi Permissions
```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Fix bootstrap/cache permissions
sudo chown -R www-data:www-data bootstrap/cache/
sudo chmod -R 775 bootstrap/cache/
```

### 2. Lỗi 500 Internal Server Error
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Nginx error logs
sudo tail -f /var/log/nginx/error.log

# Check PHP-FPM logs
sudo tail -f /var/log/php8.1-fpm.log
```

### 3. Lỗi Database Connection
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql
```

### 4. Lỗi Queue Jobs
```bash
# Check queue status
php artisan queue:work --once

# Restart queue workers
sudo supervisorctl restart laravel-worker:*
```

### 5. Performance Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. SSL Issues
```bash
# Test SSL certificate
openssl s_client -connect yourdomain.com:443

# Renew Let's Encrypt certificate
sudo certbot renew --dry-run
```

---

## Checklist Deploy

### Pre-deployment:
- [ ] VPS đã được cấu hình và bảo mật
- [ ] Domain đã được trỏ về VPS
- [ ] Database đã được tạo và cấu hình
- [ ] SSL certificate đã được cài đặt
- [ ] Environment variables đã được cấu hình

### Deployment:
- [ ] Code đã được deploy
- [ ] Dependencies đã được cài đặt
- [ ] Database migrations đã chạy
- [ ] Storage link đã được tạo
- [ ] Caches đã được build
- [ ] Permissions đã được set đúng

### Post-deployment:
- [ ] Website hoạt động bình thường
- [ ] SSL certificate hoạt động
- [ ] Email gửi được
- [ ] Payment gateway hoạt động
- [ ] Backup system đã được setup
- [ ] Monitoring đã được cấu hình

---

## Liên Hệ và Hỗ Trợ

Nếu gặp vấn đề trong quá trình deploy, hãy kiểm tra:
1. Log files của Laravel, Nginx, PHP-FPM
2. System resources (RAM, disk space)
3. Network connectivity
4. File permissions
5. Environment configuration

**Lưu ý**: Luôn backup dữ liệu trước khi thực hiện bất kỳ thay đổi nào trên production server.