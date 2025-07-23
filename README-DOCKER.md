# Laravel E-Shop Docker Setup

Dự án Laravel E-Commerce được containerized với Docker để dễ dàng triển khai và phát triển.

## Yêu cầu hệ thống

- Docker Desktop
- Docker Compose
- Git (tùy chọn)

## Cài đặt và chạy

### Cách 1: Sử dụng script tự động (Windows)

```bash
# Chạy script build
build.bat

# Để dừng containers
stop.bat
```

### Cách 2: Chạy thủ công

1. **Clone repository** (nếu chưa có):
```bash
git clone <repository-url>
cd Complete-Ecommerce-in-laravel-10
```

2. **Tạo file .env**:
```bash
copy .env.docker .env
```

3. **Build và chạy containers**:
```bash
docker-compose up -d --build
```

4. **Cài đặt dependencies**:
```bash
docker-compose exec app composer install
```

5. **Khởi tạo ứng dụng**:
```bash
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force
docker-compose exec app php artisan storage:link
```

6. **Clear cache**:
```bash
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:cache
```

## Truy cập ứng dụng

- **Ứng dụng chính**: http://localhost:8000
- **phpMyAdmin**: http://localhost:8080
- **Database**: localhost:3306

## Thông tin database

- **Host**: db (trong container) / localhost:3306 (từ host)
- **Database**: eshop_db
- **Username**: eshop_user
- **Password**: user_password
- **Root Password**: root_password

## Cấu trúc Docker

### Services

1. **app**: Ứng dụng Laravel chạy trên PHP 8.1 + Apache
2. **db**: MySQL 8.0 database
3. **phpmyadmin**: Web interface cho database
4. **redis**: Cache và session storage

### Volumes

- **dbdata**: Lưu trữ dữ liệu MySQL
- **Application code**: Mount vào `/var/www/html`

## Lệnh hữu ích

### Xem logs
```bash
docker-compose logs -f app
docker-compose logs -f db
```

### Truy cập container
```bash
docker-compose exec app bash
docker-compose exec db mysql -uroot -p
```

### Chạy artisan commands
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app php artisan queue:work
```

### Restart services
```bash
docker-compose restart app
docker-compose restart db
```

### Dừng và xóa tất cả
```bash
docker-compose down -v
```

## Troubleshooting

### Lỗi quyền truy cập
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/storage
```

### Database connection issues
```bash
# Kiểm tra database đang chạy
docker-compose ps db

# Kiểm tra logs database
docker-compose logs db
```

### Clear toàn bộ cache
```bash
docker-compose exec app php artisan optimize:clear
```

## Production Deployment

Để deploy production, thay đổi các giá trị sau trong `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

Và chạy:
```bash
docker-compose exec app php artisan optimize
```
