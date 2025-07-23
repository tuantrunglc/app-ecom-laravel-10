@echo off
REM Script chuẩn bị deploy lên shared hosting cho Windows (Improved)
REM Chạy script này trước khi upload

echo 🚀 Chuẩn bị deploy Laravel lên Shared Hosting (Improved)

REM 1. Kiểm tra môi trường
echo 📋 Kiểm tra môi trường...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Composer không được cài đặt
    echo Vui lòng cài đặt Composer: https://getcomposer.org/download/
    pause
    exit /b 1
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ npm không được cài đặt
    echo Vui lòng cài đặt Node.js: https://nodejs.org/
    pause
    exit /b 1
)

echo ✅ Môi trường OK

REM 2. Cài đặt dependencies
echo 📦 Cài đặt PHP dependencies...
composer install --optimize-autoloader --no-dev

echo 📦 Cài đặt Node.js dependencies...
npm install

REM 3. Build assets
echo 🏗️ Build assets cho production...
npm run production

REM 4. Optimize Laravel
echo ⚡ Optimize Laravel...
php artisan config:cache
php artisan route:cache
php artisan view:cache

REM 5. Tạo thư mục cần thiết cho upload
echo 📁 Tạo structure cho shared hosting...

REM Method 1: Laravel core riêng
if not exist "shared_hosting_files\method1\laravel_app" mkdir "shared_hosting_files\method1\laravel_app"
if not exist "shared_hosting_files\method1\public_html" mkdir "shared_hosting_files\method1\public_html"

REM Copy Laravel core (không bao gồm public)
echo Copy Laravel core files...
robocopy . "shared_hosting_files\method1\laravel_app" /E /XD public node_modules .git tests

REM Copy public folder contents
echo Copy public files...
robocopy public "shared_hosting_files\method1\public_html" /E

REM Method 2: Tất cả trong public_html
if not exist "shared_hosting_files\method2\public_html" mkdir "shared_hosting_files\method2\public_html"
robocopy . "shared_hosting_files\method2\public_html" /E /XD node_modules .git tests

REM 6. Tạo .env template
echo ⚙️ Tạo .env template...
(
echo APP_NAME="Laravel Ecommerce"
echo APP_ENV=production
echo APP_KEY=
echo APP_DEBUG=false
echo APP_URL=https://yourdomain.com
echo.
echo LOG_CHANNEL=stack
echo LOG_DEPRECATIONS_CHANNEL=null
echo LOG_LEVEL=error
echo.
echo DB_CONNECTION=mysql
echo DB_HOST=localhost
echo DB_PORT=3306
echo DB_DATABASE=your_database_name
echo DB_USERNAME=your_database_user
echo DB_PASSWORD=your_database_password
echo.
echo BROADCAST_DRIVER=log
echo CACHE_DRIVER=file
echo FILESYSTEM_DISK=local
echo QUEUE_CONNECTION=sync
echo SESSION_DRIVER=file
echo SESSION_LIFETIME=120
echo.
echo MEMCACHED_HOST=127.0.0.1
echo.
echo REDIS_HOST=127.0.0.1
echo REDIS_PASSWORD=null
echo REDIS_PORT=6379
echo.
echo MAIL_MAILER=smtp
echo MAIL_HOST=mailpit
echo MAIL_PORT=1025
echo MAIL_USERNAME=null
echo MAIL_PASSWORD=null
echo MAIL_ENCRYPTION=null
echo MAIL_FROM_ADDRESS="hello@example.com"
echo MAIL_FROM_NAME="${APP_NAME}"
echo.
echo # PayPal Config
echo PAYPAL_CLIENT_ID=your_paypal_client_id
echo PAYPAL_CLIENT_SECRET=your_paypal_client_secret
echo PAYPAL_MODE=sandbox
echo.
echo # Newsletter
echo MAILCHIMP_APIKEY=your_mailchimp_api_key
echo MAILCHIMP_LIST_ID=your_mailchimp_list_id
echo.
echo AWS_ACCESS_KEY_ID=
echo AWS_SECRET_ACCESS_KEY=
echo AWS_DEFAULT_REGION=us-east-1
echo AWS_BUCKET=
echo AWS_USE_PATH_STYLE_ENDPOINT=false
echo.
echo PUSHER_APP_ID=
echo PUSHER_APP_KEY=
echo PUSHER_APP_SECRET=
echo PUSHER_HOST=
echo PUSHER_PORT=443
echo PUSHER_SCHEME=https
echo PUSHER_APP_CLUSTER=mt1
echo.
echo VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
echo VITE_PUSHER_HOST="${PUSHER_HOST}"
echo VITE_PUSHER_PORT="${PUSHER_PORT}"
echo VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
echo VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
) > "shared_hosting_files\.env.shared_hosting"

REM 7. Copy helper files
echo 🔧 Copy helper files...
copy "create_storage_link.php" "shared_hosting_files\"
copy "clear_cache.php" "shared_hosting_files\"

REM 8. Update index.php cho method 1
echo ⚙️ Update index.php cho method 1...
powershell -Command "(Get-Content 'shared_hosting_files\method1\public_html\index.php') -replace '__DIR__\.\"\/\.\.\/bootstrap\/autoload\.php\"', '__DIR__\.\"/\.\./laravel_app/bootstrap/autoload.php\"' -replace '__DIR__\.\"\/\.\.\/bootstrap\/app\.php\"', '__DIR__\.\"/\.\./laravel_app/bootstrap/app.php\"' | Set-Content 'shared_hosting_files\method1\public_html\index.php'"

REM 9. Tạo hướng dẫn
echo 📚 Tạo hướng dẫn deploy...
(
echo HƯỚNG DẪN DEPLOY LÊN SHARED HOSTING
echo.
echo === CHUẨN BỊ ===
echo 1. Export database từ phpMyAdmin container:
echo    - Truy cập http://localhost:8080
echo    - Login: root/root
echo    - Export database 'laravel_ecommerce' thành file .sql
echo.
echo 2. Chọn phương pháp deploy:
echo    - Method 1: Laravel core tách riêng (bảo mật hơn^)
echo    - Method 2: Tất cả trong public_html (đơn giản hơn^)
echo.
echo === METHOD 1: Laravel core tách riêng ===
echo 1. Upload thư mục 'method1/laravel_app' lên server (ngoài public_html^)
echo 2. Upload nội dung 'method1/public_html' vào public_html
echo 3. Upload file .env.shared_hosting đổi tên thành .env vào laravel_app/
echo 4. Sửa thông tin database trong .env
echo 5. Import database .sql qua cPanel
echo 6. Chạy create_storage_link.php từ browser
echo 7. Chạy clear_cache.php nếu cần
echo.
echo === METHOD 2: Tất cả trong public_html ===
echo 1. Upload nội dung 'method2/public_html' vào public_html
echo 2. Upload file .env.shared_hosting đổi tên thành .env
echo 3. Sửa thông tin database trong .env
echo 4. Import database .sql qua cPanel
echo 5. Chạy create_storage_link.php từ browser
echo 6. Chạy clear_cache.php nếu cần
echo.
echo === QUAN TRỌNG ===
echo - Đổi APP_KEY trong .env (generate từ Laravel key generator online^)
echo - Xóa create_storage_link.php và clear_cache.php sau khi xong
echo - Set quyền 755 cho thư mục storage và bootstrap/cache
echo - Kiểm tra domain trỏ đúng đến public hoặc public_html
echo.
echo === TROUBLESHOOTING ===
echo - Nếu lỗi 500: kiểm tra log trong storage/logs/
echo - Nếu assets không load: kiểm tra đường dẫn và quyền file
echo - Nếu database không kết nối: kiểm tra thông tin DB trong .env
) > "shared_hosting_files\DEPLOY_INSTRUCTIONS.txt"

echo.
echo ✅ Hoàn thành chuẩn bị!
echo 📁 Tất cả file cần thiết đã được tạo trong thư mục: shared_hosting_files/
echo.
echo 📋 Bước tiếp theo:
echo 1. Export database từ phpMyAdmin (http://localhost:8080^)
echo 2. Chọn method deploy phù hợp
echo 3. Upload file theo hướng dẫn trong DEPLOY_INSTRUCTIONS.txt
echo 4. Cấu hình .env với thông tin database thực tế
echo 5. Import database và test website
echo.
pause
