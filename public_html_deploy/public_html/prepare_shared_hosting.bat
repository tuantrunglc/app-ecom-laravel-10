@echo off
echo ========================================
echo    Chuẩn bị files cho Shared Hosting
echo ========================================

echo.
echo Bước 1: Tạo thư mục deploy...
if not exist "shared_hosting_deploy" mkdir shared_hosting_deploy
cd shared_hosting_deploy

echo.
echo Bước 2: Copy Laravel core files...
if not exist "laravel_app" mkdir laravel_app

rem Copy tất cả files trừ public, node_modules, .git
robocopy ".." "laravel_app" /E /XD public node_modules .git vendor storage\logs bootstrap\cache /XF *.log

echo.
echo Bước 3: Copy public folder...
if not exist "public_html" mkdir public_html
robocopy "..\public" "public_html" /E

echo.
echo Bước 4: Tạo .env.production...
copy "..\env.docker" "laravel_app\.env.production" >nul

echo.
echo Bước 5: Chỉnh sửa index.php cho shared hosting...
(
echo ^<?php
echo.
echo use Illuminate\Contracts\Http\Kernel;
echo use Illuminate\Http\Request;
echo.
echo define('LARAVEL_START', microtime(true)^);
echo.
echo // Đường dẫn đến Laravel app
echo require __DIR__.'/../laravel_app/vendor/autoload.php';
echo.
echo $app = require_once __DIR__.'/../laravel_app/bootstrap/app.php';
echo.
echo $kernel = $app->make(Kernel::class^);
echo.
echo $response = $kernel->handle(
echo     $request = Request::capture()
echo )->send();
echo.
echo $kernel->terminate($request, $response^);
) > "public_html\index.php"

echo.
echo Bước 6: Tạo file .htaccess bảo mật...
(
echo # Deny access to Laravel core
echo ^<Files "*"^>
echo     Order Deny,Allow
echo     Deny from all
echo ^</Files^>
) > "laravel_app\.htaccess"

echo.
echo Bước 7: Tạo file hướng dẫn deploy...
(
echo ========================================
echo    HƯỚNG DẪN DEPLOY LÊN SHARED HOST
echo ========================================
echo.
echo 1. Upload thư mục 'laravel_app' lên thư mục root của hosting (ngoài public_html)
echo.
echo 2. Upload nội dung thư mục 'public_html' vào thư mục public_html của hosting
echo.
echo 3. Cấu hình database:
echo    - Tạo database trong cPanel
echo    - Cập nhật thông tin DB trong laravel_app/.env.production
echo    - Đổi tên .env.production thành .env
echo.
echo 4. Chạy các lệnh setup (qua SSH hoặc Terminal trong cPanel):
echo    cd laravel_app
echo    php artisan key:generate
echo    php artisan migrate --force
echo    php artisan db:seed --force
echo    php artisan storage:link
echo    php artisan config:cache
echo.
echo 5. Cấp quyền thư mục:
echo    chmod -R 755 ./
echo    chmod -R 775 storage/
echo    chmod -R 775 bootstrap/cache/
echo.
echo 6. Kiểm tra website hoạt động tại domain của bạn
echo.
echo ========================================
echo     THÔNG TIN QUAN TRỌNG
echo ========================================
echo.
echo Admin Login:
echo - URL: https://yourdomain.com/admin
echo - Email: admin@gmail.com
echo - Password: 1234
echo.
echo Database Info (cần cập nhật trong .env):
echo - DB_HOST=localhost (thường là localhost)
echo - DB_DATABASE=tên_database_bạn_tạo
echo - DB_USERNAME=username_database
echo - DB_PASSWORD=password_database
echo.
echo Các file quan trọng cần kiểm tra:
echo - laravel_app/.env (thông tin database)
echo - public_html/index.php (đường dẫn đúng)
echo - laravel_app/storage/ (quyền ghi)
echo.
) > "DEPLOY_INSTRUCTIONS.txt"

echo.
echo Bước 8: Tạo backup database...
echo Đang export database từ Docker...
docker exec laravel_db mysqldump -u laravel_user -p laravel_ecommerce > database_export.sql 2>nul
if errorlevel 1 (
    echo Không thể export database từ Docker. Hãy export manual qua phpMyAdmin.
) else (
    echo Database đã được export thành database_export.sql
)

echo.
echo ========================================
echo           HOÀN THÀNH!
echo ========================================
echo.
echo Files đã được chuẩn bị trong thư mục: shared_hosting_deploy\
echo.
echo Cấu trúc files:
echo ├── laravel_app\          (upload lên thư mục root của hosting)
echo ├── public_html\          (upload vào public_html của hosting)  
echo ├── database_export.sql   (import vào database hosting)
echo └── DEPLOY_INSTRUCTIONS.txt (hướng dẫn chi tiết)
echo.
echo Hãy đọc file DEPLOY_INSTRUCTIONS.txt để biết cách upload lên hosting!
echo.
pause
