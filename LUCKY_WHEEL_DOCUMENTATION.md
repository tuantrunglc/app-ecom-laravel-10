# Tài Liệu Chức Năng Vòng Quay May Mắn

## Tổng Quan
Chức năng vòng quay may mắn cho phép người dùng tham gia quay số để nhận các phần thưởng. Admin có thể quản lý và đặt kết quả quay cho từng user cụ thể.

## Tính Năng Chính

### 1. Phía User (Frontend)
- Giao diện vòng quay may mắn với hiệu ứng xoay
- Hiển thị các phần thưởng có thể nhận được
- Lịch sử quay của user
- Giới hạn số lần quay mỗi ngày
- Hiển thị phần thưởng nhận được

### 2. Phía Admin (Backend)
- Quản lý danh sách phần thưởng
- Đặt kết quả quay cho user cụ thể
- Xem thống kê lượt quay
- Cài đặt số lần quay tối đa mỗi ngày
- Quản lý lịch sử quay của tất cả user

## Cấu Trúc Database

### Bảng `lucky_wheel_prizes` (Phần thưởng)
- `id` - Primary key
- `name` - Tên phần thưởng
- `description` - Mô tả phần thưởng
- `image` - Hình ảnh phần thưởng
- `probability` - Tỷ lệ trúng (%)
- `quantity` - Số lượng phần thưởng
- `remaining_quantity` - Số lượng còn lại
- `is_active` - Trạng thái hoạt động
- `created_at`, `updated_at`

### Bảng `lucky_wheel_spins` (Lịch sử quay)
- `id` - Primary key
- `user_id` - ID người dùng
- `prize_id` - ID phần thưởng (nullable)
- `spin_date` - Ngày quay
- `is_winner` - Có trúng thưởng không
- `admin_set` - Admin đặt kết quả (boolean)
- `created_at`, `updated_at`

### Bảng `lucky_wheel_settings` (Cài đặt)
- `id` - Primary key
- `key` - Khóa cài đặt
- `value` - Giá trị cài đặt
- `description` - Mô tả
- `created_at`, `updated_at`

### Bảng `lucky_wheel_admin_sets` (Admin đặt kết quả)
- `id` - Primary key
- `user_id` - ID người dùng
- `prize_id` - ID phần thưởng được đặt
- `admin_id` - ID admin đặt
- `is_used` - Đã sử dụng chưa
- `expires_at` - Thời gian hết hạn
- `created_at`, `updated_at`

## Cấu Trúc File

### Models
- `app/Models/LuckyWheelPrize.php`
- `app/Models/LuckyWheelSpin.php`
- `app/Models/LuckyWheelSetting.php`
- `app/Models/LuckyWheelAdminSet.php`

### Controllers
- `app/Http/Controllers/LuckyWheelController.php` (Frontend)
- `app/Http/Controllers/Admin/LuckyWheelController.php` (Admin)

### Migrations
- `database/migrations/xxxx_create_lucky_wheel_prizes_table.php`
- `database/migrations/xxxx_create_lucky_wheel_spins_table.php`
- `database/migrations/xxxx_create_lucky_wheel_settings_table.php`
- `database/migrations/xxxx_create_lucky_wheel_admin_sets_table.php`

### Views
#### Frontend
- `resources/views/frontend/lucky-wheel/index.blade.php`
- `resources/views/frontend/lucky-wheel/history.blade.php`

#### Admin
- `resources/views/backend/lucky-wheel/prizes/index.blade.php`
- `resources/views/backend/lucky-wheel/prizes/create.blade.php`
- `resources/views/backend/lucky-wheel/prizes/edit.blade.php`
- `resources/views/backend/lucky-wheel/spins/index.blade.php`
- `resources/views/backend/lucky-wheel/admin-sets/index.blade.php`
- `resources/views/backend/lucky-wheel/admin-sets/create.blade.php`
- `resources/views/backend/lucky-wheel/settings/index.blade.php`

### Routes
#### Frontend Routes
```php
Route::get('/lucky-wheel', [LuckyWheelController::class, 'index'])->name('lucky-wheel');
Route::post('/lucky-wheel/spin', [LuckyWheelController::class, 'spin'])->name('lucky-wheel.spin')->middleware('user');
Route::get('/lucky-wheel/history', [LuckyWheelController::class, 'history'])->name('lucky-wheel.history')->middleware('user');
```

#### Admin Routes
```php
Route::group(['prefix' => '/admin/lucky-wheel', 'middleware' => ['auth', 'admin']], function () {
    // Prizes management
    Route::resource('prizes', 'Admin\LuckyWheelController');
    
    // Spins history
    Route::get('spins', [Admin\LuckyWheelController::class, 'spins'])->name('admin.lucky-wheel.spins');
    
    // Admin sets
    Route::get('admin-sets', [Admin\LuckyWheelController::class, 'adminSets'])->name('admin.lucky-wheel.admin-sets');
    Route::get('admin-sets/create', [Admin\LuckyWheelController::class, 'createAdminSet'])->name('admin.lucky-wheel.admin-sets.create');
    Route::post('admin-sets', [Admin\LuckyWheelController::class, 'storeAdminSet'])->name('admin.lucky-wheel.admin-sets.store');
    
    // Settings
    Route::get('settings', [Admin\LuckyWheelController::class, 'settings'])->name('admin.lucky-wheel.settings');
    Route::post('settings', [Admin\LuckyWheelController::class, 'updateSettings'])->name('admin.lucky-wheel.settings.update');
});
```

## API Endpoints

### Frontend API
- `GET /api/lucky-wheel/prizes` - Lấy danh sách phần thưởng
- `POST /api/lucky-wheel/spin` - Thực hiện quay
- `GET /api/lucky-wheel/user-spins` - Lịch sử quay của user

### Admin API
- `GET /api/admin/lucky-wheel/statistics` - Thống kê tổng quan
- `POST /api/admin/lucky-wheel/set-result` - Đặt kết quả cho user

## Quy Trình Hoạt Động

### 1. User Quay Vòng Quay
1. User truy cập trang vòng quay
2. Kiểm tra số lần quay còn lại trong ngày
3. Kiểm tra xem admin có đặt kết quả cho user không
4. Nếu có kết quả được đặt: trả về kết quả đó
5. Nếu không: random theo tỷ lệ của các phần thưởng
6. Lưu kết quả vào database
7. Cập nhật số lượng phần thưởng còn lại

### 2. Admin Đặt Kết Quả
1. Admin chọn user cần đặt kết quả
2. Chọn phần thưởng muốn user nhận được
3. Đặt thời gian hết hạn (optional)
4. Lưu vào bảng `lucky_wheel_admin_sets`
5. Khi user quay, hệ thống sẽ ưu tiên kết quả được đặt

## Cài Đặt Hệ Thống

### Các Setting Có Thể Cấu Hình
- `max_spins_per_day` - Số lần quay tối đa mỗi ngày
- `wheel_enabled` - Bật/tắt chức năng vòng quay
- `require_login` - Yêu cầu đăng nhập để quay
- `animation_duration` - Thời gian animation (ms)

## Bảo Mật

### Frontend
- Validate số lần quay
- Kiểm tra đăng nhập
- Rate limiting

### Backend
- Validate dữ liệu đầu vào
- Kiểm tra quyền admin
- Log các thao tác quan trọng

## Hiệu Ứng Frontend

### CSS/JavaScript
- Animation xoay vòng quay
- Hiệu ứng âm thanh
- Popup hiển thị kết quả
- Loading states

### Libraries Sử Dụng
- jQuery cho animation
- SweetAlert2 cho popup
- Chart.js cho thống kê (admin)

## Tối Ưu Hóa

### Performance
- Cache danh sách phần thưởng
- Optimize database queries
- Lazy loading cho lịch sử

### UX/UI
- Responsive design
- Loading indicators
- Error handling
- Success animations

## Testing

### Unit Tests
- Test logic random phần thưởng
- Test validation rules
- Test admin set logic

### Feature Tests
- Test user spin flow
- Test admin management
- Test API endpoints

## Deployment

### Database Migration
```bash
php artisan migrate
```

### Seeder
```bash
php artisan db:seed --class=LuckyWheelSeeder
```

### Assets
```bash
npm run production
```

## Maintenance

### Regular Tasks
- Cleanup expired admin sets
- Archive old spin history
- Update prize quantities
- Monitor system performance

### Monitoring
- Track spin rates
- Monitor prize distribution
- Check for anomalies
- User engagement metrics