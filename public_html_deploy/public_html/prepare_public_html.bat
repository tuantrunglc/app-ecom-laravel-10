@echo off
echo ========================================
echo   Chuẩn bị files cho Public_html Deploy
echo ========================================

echo.
echo Bước 1: Tạo thư mục deploy...
if not exist "public_html_deploy" mkdir public_html_deploy
cd public_html_deploy

echo.
echo Bước 2: Copy toàn bộ Laravel project...
if not exist "public_html" mkdir public_html

rem Copy tất cả files (trừ node_modules, .git, docker files)
robocopy ".." "public_html" /E /XD node_modules .git docker shared_hosting_deploy public_html_deploy /XF *.log docker-compose.yml Dockerfile

echo.
echo Bước 3: Tạo .env cho production...
copy "..\env.shared_hosting" "public_html\.env.production" >nul

echo.
echo Bước 4: Tạo file .htaccess chính...
(
echo ^<IfModule mod_rewrite.c^>
echo     RewriteEngine On
echo.    
echo     # Handle Angular HTML5 Mode
echo     RewriteCond %%{REQUEST_FILENAME} !-f
echo     RewriteCond %%{REQUEST_FILENAME} !-d
echo     RewriteCond %%{REQUEST_URI} !^^/public/
echo     RewriteRule ^^(.*^)$ /public/$1 [L]
echo.    
echo     # Handle Laravel Routes
echo     RewriteCond %%{REQUEST_URI} ^^/public/
echo     RewriteCond %%{REQUEST_FILENAME} !-f
echo     RewriteCond %%{REQUEST_FILENAME} !-d
echo     RewriteRule ^^public/(.*^)$ /public/index.php [L,QSA]
echo ^</IfModule^>
echo.
echo # Ẩn các file nhạy cảm
echo ^<Files ~ "^^(\.env^|\.git^|composer\.json^|composer\.lock^|artisan^)"^>
echo     Order allow,deny
echo     Deny from all
echo ^</Files^>
echo.
echo # Ẩn thư mục
echo RedirectMatch 404 /\.git
echo RedirectMatch 404 /\.env
echo RedirectMatch 404 /composer\.json
echo RedirectMatch 404 /composer\.lock
echo RedirectMatch 404 /vendor
echo RedirectMatch 404 /storage/(?!app/public/^)
echo RedirectMatch 404 /bootstrap/cache
) > "public_html\.htaccess"

echo.
echo Bước 5: Tạo .htaccess bảo mật cho vendor...
(
echo # Deny access to vendor directory
echo ^<Files "*"^>
echo     Order Deny,Allow
echo     Deny from all
echo ^</Files^>
) > "public_html\vendor\.htaccess"

echo.
echo Bước 6: Tạo .htaccess bảo mật cho storage...
(
echo # Deny access to storage directory (except public^)
echo ^<Files "*"^>
echo     Order Deny,Allow
echo     Deny from all
echo ^</Files^>
) > "public_html\storage\.htaccess"

echo.
echo Bước 7: Tạo file hướng dẫn deploy...
(
echo ========================================
echo     HƯỚNG DẪN DEPLOY VÀO PUBLIC_HTML
echo ========================================
echo.
echo 1. Upload toàn bộ thư mục 'public_html' lên hosting:
echo    - Nén thư mục public_html thành file .zip
echo    - Upload file .zip lên thư mục public_html của hosting
echo    - Giải nén trực tiếp trên hosting
echo.
echo 2. Cấu hình database:
echo    - Đăng nhập cPanel ^> MySQL Databases
echo    - Tạo database mới
echo    - Tạo user và gán quyền Full cho database
echo    - Ghi nhớ: Database name, Username, Password
echo.
echo 3. Cấu hình Laravel:
echo    - Đổi tên file .env.production thành .env
echo    - Chỉnh sửa file .env với thông tin database:
echo      DB_HOST=localhost
echo      DB_DATABASE=tên_database_vừa_tạo
echo      DB_USERNAME=username_database
echo      DB_PASSWORD=password_database
echo.
echo 4. Chạy setup qua Terminal trong cPanel:
echo    cd public_html
echo    php artisan key:generate
echo    php artisan migrate --force
echo    php artisan db:seed --force
echo    php artisan storage:link
echo    php artisan config:cache
echo.
echo 5. Cấp quyền thư mục (nếu cần):
echo    chmod -R 755 ./
echo    chmod -R 775 storage/
echo    chmod -R 775 bootstrap/cache/
echo.
echo 6. Truy cập website:
echo    - Trang chủ: https://yourdomain.com
echo    - Admin: https://yourdomain.com/admin
echo.
echo ========================================
echo     THÔNG TIN QUAN TRỌNG
echo ========================================
echo.
echo Cấu trúc sau khi upload:
echo public_html/
echo ├── app/              (Laravel core)
echo ├── bootstrap/
echo ├── config/
echo ├── database/
echo ├── public/           (Assets: css, js, images)
echo ├── resources/
echo ├── routes/
echo ├── storage/
echo ├── vendor/
echo ├── .env              (Cấu hình database)
echo ├── .htaccess          (URL rewrite và bảo mật)
echo ├── artisan
echo └── composer.json
echo.
echo URL truy cập:
echo - Website: https://yourdomain.com
echo - Admin: https://yourdomain.com/admin
echo   * Email: admin@gmail.com
echo   * Password: 1234
echo.
echo Lưu ý bảo mật:
echo - File .htaccess đã cấu hình ẩn các file/folder nhạy cảm
echo - Thư mục vendor/ và storage/ có bảo vệ riêng
echo - File .env chứa thông tin database (tuyệt đối không public)
echo.
echo Troubleshooting:
echo - Nếu lỗi 500: kiểm tra file permissions và .env
echo - Nếu assets không load: kiểm tra APP_URL trong .env
echo - Nếu database lỗi: kiểm tra thông tin kết nối DB
echo.
) > "DEPLOY_INSTRUCTIONS_PUBLIC_HTML.txt"

echo.
echo Bước 8: Export database...
echo Đang export database từ Docker...
docker exec laravel_db mysqldump -u laravel_user -p laravel_ecommerce > database_export.sql 2>nul
if errorlevel 1 (
    echo Không thể export database từ Docker. Hãy export manual qua phpMyAdmin.
    echo URL: http://localhost:8080
) else (
    echo Database đã được export thành database_export.sql
)

echo.
echo ========================================
echo           HOÀN THÀNH!
echo ========================================
echo.
echo Files đã được chuẩn bị trong thư mục: public_html_deploy\
echo.
echo Cấu trúc files:
echo ├── public_html\              (upload toàn bộ vào public_html hosting)
echo ├── database_export.sql       (import vào database hosting)
echo └── DEPLOY_INSTRUCTIONS_PUBLIC_HTML.txt (hướng dẫn chi tiết)
echo.
echo BƯỚC TIẾP THEO:
echo 1. Nén thư mục public_html thành file .zip
echo 2. Upload và giải nén trên hosting
echo 3. Đọc file DEPLOY_INSTRUCTIONS_PUBLIC_HTML.txt để setup
echo.
pause
