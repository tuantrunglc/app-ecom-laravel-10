# 🚀 Hướng dẫn Deploy Laravel E-Commerce lên Shared Hosting

## 📋 Chuẩn bị trước khi deploy

### 1. Kiểm tra yêu cầu của Shared Host
Đảm bảo hosting hỗ trợ:
- **PHP 8.1** hoặc cao hơn
- **MySQL 5.7+** hoặc **MariaDB 10.3+**
- **Composer** (hoặc có thể upload vendor/)
- **SSL Certificate** (khuyến nghị)
- **Subdomain** hoặc **domain** riêng

### 2. Chuẩn bị files trước khi upload

#### Tạo .env cho production:
```bash
# Tạo file .env.production
cp .env.docker .env.production
```

Chỉnh sửa `.env.production`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# Tạo key mới cho production
APP_KEY=base64:GENERATE_NEW_KEY_HERE

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Mail (nếu cần)
MAIL_MAILER=smtp
MAIL_HOST=your_host_smtp
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

#### Build production assets:
```bash
# BƯỚC 1: Cài đặt dependencies
npm install

# BƯỚC 2: Build assets cho production (QUAN TRỌNG!)
npm run production

# Kiểm tra các file đã được build
ls -la public/css/    # Phải có app.css
ls -la public/js/     # Phải có app.js
```

**⚠️ QUAN TRỌNG - Project này sử dụng Vue.js:**
- Project có **Vue.js components** trong `resources/js/`
- **PHẢI chạy `npm run production`** để compile Vue.js thành JavaScript thuần
- **KHÔNG thể** bỏ qua bước này vì:
  - Vue.js cần được compile từ `resources/js/app.js` → `public/js/app.js`
  - SCSS cần được compile từ `resources/sass/app.scss` → `public/css/app.css`
  - Shared hosting **KHÔNG có Node.js** để compile real-time

**Sau khi build xong:**
```bash
# XÓA node_modules để giảm dung lượng upload
rm -rf node_modules
# Hoặc trên Windows:
rmdir /s node_modules
```

**Tại sao có thể xóa node_modules:**
- `node_modules/` chỉ cần thiết cho việc COMPILE assets
- Sau khi chạy `npm run production`, tất cả đã được build vào `public/css/` và `public/js/`
- Server chỉ cần serve các file đã build, không cần Node.js runtime
- Vue.js đã được compile thành JavaScript thuần trong `public/js/app.js`

**❌ Nếu KHÔNG build assets:**
- Website sẽ bị lỗi JavaScript
- Vue.js components không hoạt động
- CSS styling bị mất

## 📁 Cấu trúc thư mục trên Shared Host

### Cách 1: Tách riêng (An toàn hơn - Khuyến nghị)
```
yourdomain.com/
├── public_html/          # Document root (chỉ chứa public folder)
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── images/
│   └── ...
├── laravel_app/          # Laravel core (ngoài public_html)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── resources/
│   ├── routes/
│   ├── storage/
│   ├── vendor/
│   ├── .env
│   ├── artisan
│   └── composer.json
```

### Cách 2: Đặt tất cả trong public_html (Đơn giản hơn)
```
yourdomain.com/
└── public_html/          # Toàn bộ Laravel project
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/           # Assets (css, js, images)
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env
    ├── .htaccess
    ├── artisan
    ├── index.php
    └── composer.json
```

## 🔧 Các bước deploy chi tiết

### Phương pháp 1: Tách riêng Laravel core (An toàn - Khuyến nghị)

#### Bước 1: Upload files
1. Nén toàn bộ project **NGOẠI TRỪ**:
   - `public` folder
   - `node_modules/` (không cần thiết)
   - `.git/` (không cần thiết)
   - `storage/logs/` (sẽ tạo lại)
2. Upload và giải nén vào thư mục `laravel_app/`
3. Upload riêng folder `public/` vào `public_html/`

#### Bước 2: Chỉnh sửa `public_html/index.php`:
```php
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Đường dẫn đến Laravel app (chỉnh sửa đường dẫn này)
require __DIR__.'/../laravel_app/vendor/autoload.php';

$app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

### Phương pháp 2: Đặt tất cả trong public_html (Đơn giản)

#### Bước 1: Upload toàn bộ project
1. Upload toàn bộ Laravel project vào `public_html/`
2. **KHÔNG** cần chỉnh sửa `index.php`

#### Bước 2: Tạo file `.htaccess` trong `public_html/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Handle Angular HTML5 Mode
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L]
    
    # Handle Laravel Routes
    RewriteCond %{REQUEST_URI} ^/public/
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^public/(.*)$ /public/index.php [L,QSA]
</IfModule>

# Ẩn các thư mục nhạy cảm
<Files ~ "^(\.env|\.git|composer\.json|composer\.lock|artisan)">
    Order allow,deny
    Deny from all
</Files>

# Ẩn thư mục
RedirectMatch 404 /\.git
RedirectMatch 404 /\.env
RedirectMatch 404 /composer\.json
RedirectMatch 404 /composer\.lock
RedirectMatch 404 /vendor
RedirectMatch 404 /storage/(?!app/public/)
RedirectMatch 404 /bootstrap/cache
```

#### Bước 3: Cấu hình bảo mật bổ sung
Tạo file `.htaccess` trong thư mục `vendor/`:
```apache
# Deny access to vendor directory
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>
```

Tạo file `.htaccess` trong thư mục `storage/`:
```apache
# Deny access to storage directory (except public)
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>
```

### Bước 3: Cấu hình .env
Tạo file `.env` trong thư mục gốc (public_html/ hoặc laravel_app/):
```bash
# Copy từ .env.shared_hosting và cập nhật thông tin database
```

### Bước 4: Cài đặt dependencies (Shared Host không có command line)

**⚠️ QUAN TRỌNG: Shared hosting thường KHÔNG hỗ trợ command line**

#### Cách xử lý:
1. **Build sẵn vendor/ trước khi upload:**
   ```bash
   # Chạy trên máy local hoặc Docker
   composer install --optimize-autoloader --no-dev
   ```
2. **Upload toàn bộ thư mục `vendor/`** cùng với project
3. **KHÔNG XÓA** thư mục `vendor/` khi chuẩn bị upload

### Bước 5: Cấu hình database

#### Tạo database qua cPanel:
1. Vào **MySQL Databases**
2. Tạo database mới
3. Tạo user và gán quyền
4. Cập nhật thông tin vào `.env`

#### Import database:
```bash
# Export từ Docker
docker exec laravel_db mysqldump -u laravel_user -p laravel_ecommerce > database_backup.sql

# Import vào shared host qua phpMyAdmin hoặc command line
```

### Bước 6: Setup Laravel (Shared Host - KHÔNG có command line)

**⚠️ Shared hosting thường KHÔNG có SSH/Terminal access**

#### Cách thay thế:

**1. Generate APP_KEY:**
- Sử dụng online generator: https://generate-random.org/laravel-key-generator
- Hoặc trên máy local: `php artisan key:generate --show`
- Copy key vào file `.env`: `APP_KEY=base64:YOUR_GENERATED_KEY`

**2. Database Migration:**
- **KHÔNG thể chạy `php artisan migrate`**
- **Giải pháp:** Import file SQL đã có sẵn
- Export database từ Docker: `docker exec laravel_db mysqldump -u laravel_user -p laravel_ecommerce > database.sql`
- Import vào hosting qua **phpMyAdmin**

**3. Tạo Storage Link:**
- **KHÔNG thể chạy `php artisan storage:link`**
- **Giải pháp:** Tạo symlink manual hoặc copy files
- Tạo file `create_storage_link.php` trong public_html:
```php
<?php
// Tạo storage link cho shared hosting
$target = '../storage/app/public';
$link = './storage';

if (!file_exists($link)) {
    if (is_dir($target)) {
        symlink($target, $link);
        echo "Storage link created successfully!";
    } else {
        echo "Target directory does not exist: " . $target;
    }
} else {
    echo "Storage link already exists!";
}
?>
```
- Truy cập: `https://yourdomain.com/create_storage_link.php`
- **XÓA file này sau khi chạy xong**

**4. Cache Config (Tùy chọn):**
- Shared host thường không cần cache config
- Nếu cần, tạo file `clear_cache.php`:
```php
<?php
// Xóa cache cho shared hosting
$configCache = '../bootstrap/cache/config.php';
$routeCache = '../bootstrap/cache/routes.php';
$viewCache = '../storage/framework/views/*';

if (file_exists($configCache)) {
    unlink($configCache);
    echo "Config cache cleared!<br>";
}

if (file_exists($routeCache)) {
    unlink($routeCache);
    echo "Route cache cleared!<br>";
}

// Clear view cache
$viewFiles = glob('../storage/framework/views/*');
foreach($viewFiles as $file) {
    if(is_file($file)) {
        unlink($file);
    }
}
echo "View cache cleared!<br>";
echo "All caches cleared successfully!";
?>
```

## 🔒 Cấu hình bảo mật

### 1. File permissions:
```bash
# Phương pháp 1 (Laravel core tách riêng):
chmod -R 755 laravel_app/
chmod -R 775 laravel_app/storage/
chmod -R 775 laravel_app/bootstrap/cache/

# Phương pháp 2 (Tất cả trong public_html):
chmod -R 755 public_html/
chmod -R 775 public_html/storage/
chmod -R 775 public_html/bootstrap/cache/
```

### 2. Cấu hình bảo mật:

#### Phương pháp 1 - Ẩn thư mục Laravel core:
Tạo file `laravel_app/.htaccess`:
```apache
# Deny access to Laravel core
<Files "*">
    Order Deny,Allow
    Deny from all
</Files>
```

#### Phương pháp 2 - Bảo vệ files trong public_html:
File `.htaccess` chính đã được tạo ở bước trước với các rule bảo mật.

### 3. Cấu hình SSL (nếu có):
```env
# Trong .env
APP_URL=https://yourdomain.com
FORCE_HTTPS=true
```

## 🌐 Cấu hình domain/subdomain

### Nếu sử dụng subdomain:
1. Tạo subdomain trong cPanel
2. Point document root đến `public_html/`
3. Cập nhật `APP_URL` trong `.env`

### Nếu sử dụng domain chính:
- Đảm bảo files Laravel ở ngoài `public_html/`

## 📧 Cấu hình Email

```env
# SMTP Gmail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

## 🎯 Checklist sau khi deploy

- [ ] Website hiển thị bình thường
- [ ] Admin panel hoạt động
- [ ] Database connection OK
- [ ] File upload/storage hoạt động
- [ ] Email gửi được (test reset password)
- [ ] SSL certificate hoạt động
- [ ] Các routes admin được bảo vệ
- [ ] Log files có quyền ghi

## 🔧 Troubleshooting phổ biến

### Lỗi 500 Internal Server Error:
1. Kiểm tra file permissions (755 cho folders, 644 cho files)
2. Xem error logs trong cPanel > Error Logs
3. Đảm bảo `.env` có `APP_KEY` hợp lệ
4. Kiểm tra PHP version (cần PHP 8.1+)

### Lỗi database connection:
1. Kiểm tra thông tin DB trong `.env`
2. Đảm bảo user có quyền truy cập database
3. Kiểm tra hostname (thường là `localhost`)
4. Kiểm tra tên database có prefix không (vd: `cpanel_database`)

### Lỗi "No input file specified":
1. Kiểm tra đường dẫn trong `index.php`
2. Cấu hình `.htaccess` nếu cần
3. Kiểm tra mod_rewrite có được bật không

### Lỗi assets không load:
1. Cập nhật `APP_URL` trong `.env`
2. Kiểm tra đường dẫn CSS/JS trong views
3. Clear browser cache

### Lỗi "Class not found" hoặc "Autoload":
1. Đảm bảo thư mục `vendor/` đã được upload đầy đủ
2. Kiểm tra file `vendor/autoload.php` có tồn tại
3. Re-upload thư mục `vendor/` nếu cần

### Lỗi storage/upload file:
1. Tạo storage link bằng script `create_storage_link.php`
2. Kiểm tra permissions thư mục `storage/` (775)
3. Kiểm tra đường dẫn trong config `filesystems.php`

### Shared Hosting Limitations:
- **Không có SSH/Terminal access**
- **Không chạy được Artisan commands**
- **Giới hạn thời gian thực thi (30-60s)**
- **Giới hạn memory (128-512MB)**
- **Không thể cài package qua Composer**

### Workarounds cho Shared Hosting:
- Build tất cả ở local/Docker trước khi upload
- Sử dụng file SQL thay vì migration  
- Tạo script PHP thay thế Artisan commands
- Upload `vendor/` (PHẢI có)
- **KHÔNG upload `node_modules/`** (không cần thiết sau khi build)

### Vue.js và Assets trên Shared Hosting:
- **Vue.js components** đã được compile thành JavaScript thuần trong `public/js/app.js`
- **SCSS/CSS** đã được compile vào `public/css/app.css`
- **Node.js KHÔNG cần thiết** trên production server
- **`node_modules/` KHÔNG cần upload** (chỉ cần khi develop)
- **Chỉ cần upload thư mục `public/`** với assets đã build

## 📞 Liên hệ hỗ trợ

Nếu gặp vấn đề trong quá trình deploy, hãy:
1. Kiểm tra error logs trên hosting
2. Liên hệ support của hosting provider
3. Hoặc tham khảo docs Laravel về deployment

---

**🚀 Chúc bạn deploy thành công!**
