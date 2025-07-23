#!/bin/bash

# Tạo file .env từ .env.docker
cp .env.docker .env

# Chờ database sẵn sàng
echo "Waiting for database..."
while ! mysqladmin ping -h"db" --silent; do
    sleep 1
done

echo "Database is ready!"

# Generate application key
php artisan key:generate

# Chạy migrations
php artisan migrate --force

# Seed database nếu cần
php artisan db:seed --force

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link

echo "Laravel application is ready!"
