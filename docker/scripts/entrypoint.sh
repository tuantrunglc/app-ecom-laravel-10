#!/bin/bash
set -e

echo "Starting Laravel E-Shop setup..."

# Kiểm tra và tạo thư mục cần thiết
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/bootstrap/cache

# Thiết lập quyền chỉ cho các thư mục cần thiết
chmod -R 777 /var/www/html/storage
chmod -R 777 /var/www/html/bootstrap/cache

# Kiểm tra file .env
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env file..."
    cp /var/www/html/.env.docker /var/www/html/.env
fi

# Chờ database sẵn sàng (nếu có)
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database at $DB_HOST:$DB_PORT..."
    while ! nc -z $DB_HOST $DB_PORT; do
        sleep 1
    done
    echo "Database is ready!"
fi

# Chạy composer install nếu chưa có vendor hoặc autoload.php
if [ ! -d "/var/www/html/vendor" ] || [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Installing PHP dependencies..."
    composer install --optimize-autoloader --no-interaction
fi

# Chỉ chạy artisan commands nếu autoload.php tồn tại
if [ -f "/var/www/html/vendor/autoload.php" ]; then
    # Generate app key nếu chưa có
    if ! grep -q "APP_KEY=base64:" /var/www/html/.env; then
        echo "Generating application key..."
        php artisan key:generate --force
    fi

    # Tạo storage link nếu chưa có
    if [ ! -L "/var/www/html/public/storage" ]; then
        echo "Creating storage link..."
        php artisan storage:link
    fi
else
    echo "Warning: vendor/autoload.php not found, skipping artisan commands"
fi

# Build frontend assets nếu có package.json (chạy trong background)
if [ -f "/var/www/html/package.json" ]; then
    echo "Installing Node.js dependencies in background..."
    (
        npm install --legacy-peer-deps && \
        if [ -f "/var/www/html/webpack.mix.js" ]; then
            echo "Building frontend assets..."
            NODE_OPTIONS="--openssl-legacy-provider" npm run prod || npm run dev || echo "Asset build failed"
        fi
    ) &
    echo "Node.js setup running in background, continuing with Apache startup..."
fi

echo "Laravel setup completed!"

# Start Apache
exec apache2-foreground