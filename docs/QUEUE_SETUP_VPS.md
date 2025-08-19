# Queue trong Docker (prod) — Chỉ dùng Database driver

Hướng dẫn này tập trung vào 1 cách duy nhất để chạy queue trên VPS: sử dụng Database driver với `docker-compose.prod.yml`. Không dùng Redis.

---

## 1) Yêu cầu
- Đã deploy bằng `docker-compose.prod.yml` với các service: `app`, `db`, `nginx` (có thể có `redis` nhưng sẽ không dùng).
- Image PHP của bạn build từ `Dockerfile` trong repo này.
- File `.env` của ứng dụng nằm trong image hoặc bind-mount vào container `app`.

---

## 2) Cấu hình .env cho queue (Database)
Sửa `.env` của ứng dụng:
```env
QUEUE_CONNECTION=database
```
Đảm bảo cấu hình DB đúng (khớp với docker-compose.prod.yml):
```env
DB_CONNECTION=mysql
DB_HOST=db      # dùng tên service trong compose (không phải container_name)
DB_PORT=3306
DB_DATABASE=ecom_walmart_db
DB_USERNAME=ecom_walmart_user
DB_PASSWORD=120811Trung@
```

---

## 3) Thêm worker vào docker-compose.prod.yml
Thêm service `worker` (sau `app`). Worker dùng cùng image và chỉ mount các thư mục cần thiết như `app` để đảm bảo dữ liệu/persist:
```yaml
  worker:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_worker_prod
    restart: unless-stopped
    working_dir: /var/www/html
    command: php artisan queue:work database --sleep=3 --tries=3 --timeout=120 --queue=default
    environment:
      - APP_ENV=production
      - QUEUE_CONNECTION=database
    volumes:
      - ./storage:/var/www/html/storage
      - ./public/storage:/var/www/html/public/storage
      - ./public/photos:/var/www/html/public/photos
    depends_on:
      db:
        condition: service_healthy
    networks:
      - app-network
```
Lưu ý: Bạn có thể (khuyến nghị) thêm `- QUEUE_CONNECTION=database` vào service `app` để app và worker đồng nhất cấu hình.

---

## 4) Khởi tạo bảng jobs (chạy một lần)
Chạy các lệnh này trên VPS (thư mục chứa compose):
```bash
docker compose -f docker-compose.prod.yml exec app php artisan queue:table
# Nếu migrations đã ok trong CI/CD thì có thể bỏ; còn không thì chạy:
docker compose -f docker-compose.prod.yml exec app php artisan migrate
```

---

## 5) Khởi động worker và kiểm tra
```bash
docker compose -f docker-compose.prod.yml up -d worker
# Xem log realtime
docker compose -f docker-compose.prod.yml logs -f worker
```
Nếu thấy "Processing: ..." là worker đang chạy đúng.

---

## 6) Test delay job
- Viết 1 job và dispatch với delay: `dispatch(new MyJob(...))->delay(now()->addMinutes(10));`
- Giữ worker chạy; đến thời điểm delay, job sẽ được lấy từ bảng `jobs` và xử lý.

---

## 7) Cập nhật phiên bản/triển khai lại
Khi cập nhật code hoặc thay đổi `Dockerfile`/composer:
```bash
docker compose -f docker-compose.prod.yml build app worker
docker compose -f docker-compose.prod.yml up -d
```

---

## 8) Troubleshooting
- Worker không lên: `docker compose -f docker-compose.prod.yml logs -f worker`
- Job không chạy:
  - Kiểm tra `.env` có `QUEUE_CONNECTION=database` chưa.
  - Đã chạy `queue:table` + `migrate` chưa (bảng `jobs` phải tồn tại)?
  - Kiểm tra DB kết nối đúng (DB_HOST là `db`).
- Tối ưu tham số `queue:work`:
  - `--sleep=3`: nghỉ giữa lần poll
  - `--tries=3`: số lần retry khi lỗi
  - `--timeout=120`: timeout cho 1 job (giây)
  - `--queue=default`: tên hàng đợi (có thể là danh sách: `high,default,low`)

---

## 9) Mẫu docker-compose.prod.yml (Database queue)
Sao chép, kiểm tra lại các biến mật trước khi dùng:
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
      - QUEUE_CONNECTION=database
    volumes:
      - ./storage:/var/www/html/storage
      - ./public/storage:/var/www/html/public/storage
      - ./public/photos:/var/www/html/public/photos
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
      - ./docker/nginx/conf.d/:/etc/nginx/conf.d/
      - ./docker/nginx/ssl/:/etc/nginx/ssl/
      - /etc/letsencrypt:/etc/letsencrypt:ro
    networks:
      - app-network
    depends_on:
      - app

  db:
    image: mysql:8.0
    container_name: laravel_db_prod
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: ecom_walmart_db
      MYSQL_ROOT_PASSWORD: 120811Trung@
      MYSQL_PASSWORD: 120811Trung@
      MYSQL_USER: ecom_walmart_user
    volumes:
      - db_data:/var/lib/mysql
      - ./database/e-shop.sql:/docker-entrypoint-initdb.d/e-shop.sql
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      timeout: 20s
      retries: 10

  # Queue worker chỉ dùng Database driver
  worker:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: laravel_worker_prod
    restart: unless-stopped
    working_dir: /var/www/html
    command: php artisan queue:work database --sleep=3 --tries=3 --timeout=120 --queue=default
    environment:
      - APP_ENV=production
      - QUEUE_CONNECTION=database
    volumes:
      - ./storage:/var/www/html/storage
      - ./public/storage:/var/www/html/public/storage
      - ./public/photos:/var/www/html/public/photos
    depends_on:
      db:
        condition: service_healthy
    networks:
      - app-network

networks:
  app-network:
    driver: bridge

volumes:
  db_data:
    driver: local
```

---

Chỉ cần làm đúng các bước trên, bạn đã có queue chạy nền trong Docker (prod) dùng Database driver, ổn định và không phụ thuộc Redis.