# Hướng dẫn: Nút chuyển trạng thái đơn hàng (New -> Processing -> Delivered sau ~10 phút)

Tài liệu này hướng dẫn cách sử dụng nút chuyển trạng thái trong trang danh sách đơn hàng của User. Khi bấm nút, đơn hàng sẽ chuyển từ **New** sang **Processing**, và sau khoảng **10 phút** sẽ tự động chuyển sang **Delivered**.

## 1) Tính năng đã được thêm ở đâu?
- Trang: `resources/views/user/order/index.blade.php`
- Nút mới xuất hiện ở cột Actions cho các đơn hàng có trạng thái `New`.
- API xử lý: `POST /user/order/{id}/advance`
- Controller: `App\Http\Controllers\HomeController::advanceOrderStatus`
- Job xử lý tự động: `App\Jobs\DeliverOrderJob` (được delay 10 phút)

## 2) Cách sử dụng trên giao diện
1. Vào menu User -> My Orders (route: `user.order.index`).
2. Với đơn hàng có trạng thái **New**, ở cột **Actions** sẽ có nút mũi tên tiến (`>`):
   - Bấm nút này để chuyển trạng thái ngay sang **Processing**.
   - Sau khi bấm, hệ thống sẽ hẹn giờ tự động chuyển đơn sang **Delivered** sau ~10 phút.
3. Bạn sẽ thấy badge trạng thái trên dòng được đổi thành "Processing" ngay lập tức.

## 3) Điều kiện để tự động đổi trạng thái sau 10 phút (Queue với Docker Compose)
Tính năng auto sau 10 phút cần Queue worker hoạt động. Với môi trường VPS dùng `docker-compose.prod.yml`, hãy làm theo các bước sau (Database driver, không dùng Redis):

### Bước A: Cấu hình Queue (.env)
1. Sửa `.env` của ứng dụng:
   ```env
   QUEUE_CONNECTION=database
   ```
2. Đảm bảo cấu hình DB đúng, dùng tên service trong compose:
   ```env
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=ecom_walmart_db
   DB_USERNAME=ecom_walmart_user
   DB_PASSWORD=120811Trung@
   ```
3. Khuyến nghị thêm biến môi trường vào service `app` và `worker` trong `docker-compose.prod.yml`:
   ```yaml
   environment:
     - APP_ENV=production
     - QUEUE_CONNECTION=database
   ```

### Bước B: Khởi tạo bảng jobs (chạy một lần)
Chạy trong container `app` bằng Docker Compose:
```bash
docker compose -f docker-compose.prod.yml exec app php artisan queue:table
docker compose -f docker-compose.prod.yml exec app php artisan migrate
```

### Bước C: Chạy Queue Worker bằng Docker Compose
Service `worker` đã được định nghĩa để chạy nền lệnh `php artisan queue:work database`.
- Khởi động worker:
```bash
docker compose -f docker-compose.prod.yml up -d worker
```
- Xem log realtime:
```bash
docker compose -f docker-compose.prod.yml logs -f worker
```

Lưu ý: Nếu worker không chạy, job delay 10 phút sẽ **không** được xử lý (đơn hàng sẽ đứng ở trạng thái Processing).

Tham khảo hướng dẫn chi tiết: `docs/QUEUE_SETUP_VPS.md` (mục mẫu docker-compose và troubleshoot).

## 4) Kiểm thử nhanh
1. Tạo 1 đơn hàng mới (trạng thái `new`).
2. Mở trang User -> My Orders, nhấn nút chuyển trạng thái ở dòng đơn.
3. Xác nhận trạng thái đổi sang **Processing** ngay.
4. Đảm bảo đã bật queue worker. Chờ ~10 phút và reload trang, đơn hàng sẽ chuyển sang **Delivered** tự động.

## 5) Tuỳ chỉnh thời gian delay
- Mặc định là 10 phút trong controller:
  ```php
  \App\Jobs\DeliverOrderJob::dispatch($order->id)->delay(now()->addMinutes(10));
  ```
- Thay `10` bằng số phút bạn muốn.

## 6) Ghi chú bảo mật và điều kiện hợp lệ
- API chỉ cho phép user thao tác với chính đơn hàng của họ.
- Chỉ cho phép advance khi trạng thái là `new` hoặc `process`. Nếu là trạng thái khác, API trả lỗi 422.
- Job chỉ tự động chuyển **Processing -> Delivered** nếu đơn vẫn đang ở `process` tại thời điểm xử lý.

## 7) File/thay đổi liên quan
- Thêm route:
  - `routes/web.php`: `Route::post('/user/order/{id}/advance', ...)`
- Thêm method controller:
  - `App\Http\Controllers\HomeController::advanceOrderStatus`
- Thêm Job:
  - `app/Jobs/DeliverOrderJob.php`
- Sửa view để thêm nút và JS gọi API:
  - `resources/views/user/order/index.blade.php`

Nếu cần đổi icon, text, hoặc vị trí nút, chỉnh trong file view ở cột **Actions**. Nếu muốn đổi cách hiển thị thông báo, chỉnh phần JS (SweetAlert) trong cùng file.