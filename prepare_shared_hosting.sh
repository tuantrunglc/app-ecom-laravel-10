#!/bin/bash

# Script chuẩn bị deploy lên shared hosting
# Chạy script này trước khi upload

echo "🚀 Chuẩn bị deploy Laravel lên Shared Hosting"

# 1. Kiểm tra môi trường
echo "📋 Kiểm tra môi trường..."
if ! command -v composer &> /dev/null; then
    echo "❌ Composer không được cài đặt"
    echo "Vui lòng cài đặt Composer: https://getcomposer.org/download/"
    exit 1
fi

if ! command -v npm &> /dev/null; then
    echo "❌ npm không được cài đặt"
    echo "Vui lòng cài đặt Node.js: https://nodejs.org/"
    exit 1
fi

echo "✅ Môi trường OK"

# 2. Cài đặt dependencies
echo "📦 Cài đặt PHP dependencies..."
composer install --optimize-autoloader --no-dev

echo "📦 Cài đặt Node.js dependencies..."
npm install

# 3. Build assets
echo "🏗️ Build assets cho production..."
npm run production

# 4. Optimize Laravel
echo "⚡ Optimize Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Tạo thư mục cần thiết cho upload
echo "📁 Tạo structure cho shared hosting..."

# Method 1: Laravel core riêng
mkdir -p shared_hosting_files/method1/laravel_app
mkdir -p shared_hosting_files/method1/public_html

# Copy Laravel core (không bao gồm public)
rsync -av --exclude='public' --exclude='node_modules' --exclude='.git' --exclude='tests' . shared_hosting_files/method1/laravel_app/

# Copy public folder contents
cp -r public/* shared_hosting_files/method1/public_html/

# Update index.php cho method 1
sed 's|__DIR__\.\"/\.\./bootstrap/autoload\.php\"|__DIR__\."/../laravel_app/bootstrap/autoload.php"|g' public/index.php > shared_hosting_files/method1/public_html/index.php
sed -i 's|__DIR__\.\"/\.\./bootstrap/app\.php\"|__DIR__\."/../laravel_app/bootstrap/app.php"|g' shared_hosting_files/method1/public_html/index.php

# Method 2: Tất cả trong public_html
mkdir -p shared_hosting_files/method2/public_html
cp -r . shared_hosting_files/method2/public_html/
rm -rf shared_hosting_files/method2/public_html/node_modules
rm -rf shared_hosting_files/method2/public_html/.git
rm -rf shared_hosting_files/method2/public_html/tests

# 6. Tạo .env template
echo "⚙️ Tạo .env template..."
cat > shared_hosting_files/.env.shared_hosting << 'EOF'
APP_NAME="Laravel Ecommerce"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# PayPal Config
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox

# Newsletter
MAILCHIMP_APIKEY=your_mailchimp_api_key
MAILCHIMP_LIST_ID=your_mailchimp_list_id

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF

# 7. Copy helper files
echo "🔧 Copy helper files..."
cp create_storage_link.php shared_hosting_files/
cp clear_cache.php shared_hosting_files/

# 8. Tạo hướng dẫn
echo "📚 Tạo hướng dẫn deploy..."
cat > shared_hosting_files/DEPLOY_INSTRUCTIONS.txt << 'EOF'
HƯỚNG DẪN DEPLOY LÊN SHARED HOSTING

=== CHUẨN BỊ ===
1. Export database từ phpMyAdmin container:
   - Truy cập http://localhost:8080
   - Login: root/root
   - Export database 'laravel_ecommerce' thành file .sql

2. Chọn phương pháp deploy:
   - Method 1: Laravel core tách riêng (bảo mật hơn)
   - Method 2: Tất cả trong public_html (đơn giản hơn)

=== METHOD 1: Laravel core tách riêng ===
1. Upload thư mục 'method1/laravel_app' lên server (ngoài public_html)
2. Upload nội dung 'method1/public_html' vào public_html
3. Upload file .env.shared_hosting đổi tên thành .env vào laravel_app/
4. Sửa thông tin database trong .env
5. Import database .sql qua cPanel
6. Chạy create_storage_link.php từ browser
7. Chạy clear_cache.php nếu cần

=== METHOD 2: Tất cả trong public_html ===
1. Upload nội dung 'method2/public_html' vào public_html
2. Upload file .env.shared_hosting đổi tên thành .env
3. Sửa thông tin database trong .env
4. Import database .sql qua cPanel
5. Chạy create_storage_link.php từ browser
6. Chạy clear_cache.php nếu cần

=== QUAN TRỌNG ===
- Đổi APP_KEY trong .env (generate từ Laravel key generator online)
- Xóa create_storage_link.php và clear_cache.php sau khi xong
- Set quyền 755 cho thư mục storage và bootstrap/cache
- Kiểm tra domain trỏ đúng đến public hoặc public_html

=== TROUBLESHOOTING ===
- Nếu lỗi 500: kiểm tra log trong storage/logs/
- Nếu assets không load: kiểm tra đường dẫn và quyền file
- Nếu database không kết nối: kiểm tra thông tin DB trong .env
EOF

echo ""
echo "✅ Hoàn thành chuẩn bị!"
echo "📁 Tất cả file cần thiết đã được tạo trong thư mục: shared_hosting_files/"
echo ""
echo "📋 Bước tiếp theo:"
echo "1. Export database từ phpMyAdmin (http://localhost:8080)"
echo "2. Chọn method deploy phù hợp"
echo "3. Upload file theo hướng dẫn trong DEPLOY_INSTRUCTIONS.txt"
echo "4. Cấu hình .env với thông tin database thực tế"
echo "5. Import database và test website"
EOF
