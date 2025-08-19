# Thông báo Chat Mới cho Admin & Sub Admin (Message Center)

## Mục tiêu
- **Thông báo realtime** khi có tin nhắn mới trong hệ thống chat (Firebase) cho Admin và Sub Admin.
- **Hiển thị trong Message Center** (dropdown/bảng thông báo trên header) thay thế logic cũ.
- **Điều hướng nhanh** tới màn hình chat `/chat` với đúng cuộc hội thoại.
- **Quản lý trạng thái đọc/chưa đọc**, hiển thị badge count.

## Phạm vi
- Vai trò: `admin`, `sub_admin` (có thể mở rộng về sau).
- Nguồn dữ liệu: **Firebase Realtime Database** (đã dùng cho chat theo FIREBASE_SETUP.md).
- UI đích: **Message Center** ở khu vực Admin/Sub Admin.

## Điều kiện kích hoạt
- Sự kiện: Có bản ghi message mới được thêm vào `messages/{conversationId}/{messageId}` trên Firebase.
- Loại message: Text/Image (tuân theo schema hiện có).
- Đối tượng nhận thông báo: Tất cả Admin/Sub Admin tham gia hoặc được phân công hỗ trợ conversation tương ứng (quy định chi tiết ở phần phân quyền).

## Kiến trúc triển khai
**✅ ĐÃ TRIỂN KHAI: Phương án Hybrid (Firebase Realtime + Laravel Backup)**

### Phương án Hybrid: Kết hợp Firebase Realtime + Laravel Notifications
**Realtime Layer (Firebase):**
- Message Center **đăng ký listener** tới Firebase để nghe `child_added` tại nhánh `messages/{conversationId}`.
- Khi nhận message mới từ Firebase:
  1. Cập nhật **badge** số lượng chưa đọc ngay lập tức.
  2. Thêm item vào danh sách Message Center realtime.
  3. Phát **âm thanh thông báo**.

**Backup Layer (Laravel):**
- Sau khi gửi message lên Firebase, client gọi **API Laravel** để tạo notification backup.
- Laravel lưu vào bảng `notifications` để có lịch sử và thống kê.
- Polling API Laravel mỗi 60 giây để đồng bộ (giảm tần suất vì có Firebase realtime).

**Ưu điểm:**
- ✅ **Realtime tức thì** qua Firebase (không delay).
- ✅ **Có lưu vết** trong database Laravel.
- ✅ **Không mất thông báo** khi reload trang (Laravel backup).
- ✅ **Tương thích** với hệ thống chat Firebase hiện có.

**Nhược điểm:**
- Phức tạp hơn (2 layer), nhưng đáng giá cho trải nghiệm tốt.

## Quy tắc phân quyền & phạm vi lắng nghe
- Với Admin: mặc định có thể xem tất cả conversation (hoặc theo workspace cửa hàng nếu đa tenant).
- Với Sub Admin: chỉ xem conversation được phân công (ví dụ: theo `participants` hoặc theo trường `assigned_to` trên `conversations/{conversationId}`).
- Đề xuất: lưu trên `conversations/{conversationId}/participants` dạng `{ userId: role }` và/hoặc `assigned_to: {type: "admin"|"sub_admin", id: number}` để xác định phạm vi.

## Thiết kế UI/UX Message Center
- **Badge**: tổng số message chưa đọc (theo vai trò + phạm vi conversation của người dùng hiện tại).
- **Danh sách**: mỗi item gồm:
  - Avatar người gửi (hoặc icon theo role)
  - Tiêu đề: Tên cuộc trò chuyện hoặc tên người gửi
  - Nội dung tóm tắt: 1 dòng đầu nội dung hoặc "[Hình ảnh]"
  - Thời gian: `timeago`
  - Trạng thái: đã đọc/chưa đọc
- **Tương tác**:
  1. Click item -> mở `/chat?conversationId=...`
  2. Nút "Đánh dấu đã đọc tất cả"
  3. Tùy chọn bật/tắt âm thanh

## Dữ liệu Firebase liên quan (tham chiếu FIREBASE_SETUP.md)
- conversations/{conversationId}
  - participants: object (chứa userId/role)
  - lastMessage, unreadCount (tùy chọn)
- messages/{conversationId}/{messageId}
  - senderId, senderName, senderRole, content, type, timestamp, readBy

## Phương án A — Mẫu mã Frontend (JS)
```html
<!-- Trong layout admin/sub_admin, nạp Firebase SDK và file chat-notify.js -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-database.js"></script>
<script>
  // TODO: thay bằng biến môi trường render từ server
  const firebaseConfig = {
    apiKey: "...",
    authDomain: "...",
    databaseURL: "...",
    projectId: "...",
    storageBucket: "...",
    messagingSenderId: "...",
    appId: "..."
  };
  firebase.initializeApp(firebaseConfig);
</script>
<script>
// chat-notify.js (rút gọn) — lắng nghe message mới và cập nhật Message Center
(function() {
  const db = firebase.database();
  const currentUser = {
    id: window.APP_USER_ID,     // render từ blade
    role: window.APP_USER_ROLE  // 'admin' | 'sub_admin'
  };

  // Lấy danh sách conversation người này phụ trách (có thể fetch từ REST hoặc Firebase)
  function getAssignedConversationIds() {
    // Tối giản: server render mảng window.ASSIGNED_CONVERSATIONS = [id,...]
    return (window.ASSIGNED_CONVERSATIONS || []);
  }

  function onNewMessage(conversationId, message) {
    // Bỏ qua nếu do chính mình gửi
    if (message.senderId === currentUser.id) return;

    // Cập nhật badge + danh sách Message Center (tùy theo HTML cụ thể)
    window.MessageCenter.add({
      conversationId,
      title: message.senderName || 'Tin nhắn mới',
      preview: message.type === 'image' ? '[Hình ảnh]' : (message.content || ''),
      timestamp: message.timestamp
    });

    // Phát âm thanh
    if (window.MessageCenterSoundEnabled) {
      document.getElementById('mc-audio').play();
    }
  }

  function bindListeners() {
    const ids = getAssignedConversationIds();
    ids.forEach(id => {
      // Lắng nghe phần tử mới
      db.ref('messages/' + id)
        .limitToLast(1)
        .on('child_added', snap => {
          const data = snap.val();
          onNewMessage(id, data);
        });
    });
  }

  document.addEventListener('DOMContentLoaded', bindListeners);
})();
</script>
<audio id="mc-audio" src="/sounds/notify.mp3" preload="auto"></audio>
```

## Phương án B — Backend (Laravel Notifications)
### 1) Migration (nếu chưa dùng notifications mặc định)
```php
// php artisan notifications:table
// php artisan migrate
```

### 2) Notification class
```php
<?php
// app/Notifications/ChatNewMessageNotification.php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class ChatNewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $conversationId,
        public int $senderId,
        public string $senderName,
        public string $preview,
        public string $type,
        public int $timestamp
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'conversation_id' => $this->conversationId,
            'sender_id'       => $this->senderId,
            'sender_name'     => $this->senderName,
            'preview'         => $this->type === 'image' ? '[Hình ảnh]' : $this->preview,
            'timestamp'       => $this->timestamp,
            'type'            => $this->type,
            'link'            => url('/chat?conversationId=' . $this->conversationId),
        ]);
    }
}
```

### 3) API nhận sự kiện message mới từ client
```php
// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatNotifyController;

Route::post('/chat/notify-new-message', [ChatNotifyController::class, 'store'])
    ->middleware(['auth:sanctum']);
```

```php
<?php
// app/Http/Controllers/ChatNotifyController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\ChatNewMessageNotification;

class ChatNotifyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'conversation_id' => 'required|string',
            'sender_id'       => 'required|integer',
            'sender_name'     => 'required|string',
            'preview'         => 'nullable|string',
            'type'            => 'required|string|in:text,image',
            'timestamp'       => 'required|integer',
        ]);

        // Lấy danh sách Admin/Sub Admin liên quan để notify
        $recipients = User::query()
            ->whereIn('role', ['admin','sub_admin'])
            // TODO: filter theo conversation assignment
            ->get();

        Notification::send($recipients, new ChatNewMessageNotification(
            $data['conversation_id'],
            $data['sender_id'],
            $data['sender_name'],
            $data['preview'] ?? '',
            $data['type'],
            $data['timestamp']
        ));

        return response()->json(['ok' => true]);
    }
}
```

### 4) Frontend gọi API sau khi push message lên Firebase
```js
// Sau khi gửi message thành công (push vào Firebase):
fetch('/api/chat/notify-new-message', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': window.csrfToken,
    'Authorization': 'Bearer ' + window.apiToken // nếu dùng Sanctum/Token
  },
  body: JSON.stringify({
    conversation_id: conversationId,
    sender_id: currentUser.id,
    sender_name: currentUser.name,
    preview: message.type === 'text' ? message.content : '',
    type: message.type,
    timestamp: Date.now()
  })
});
```

### 5) API cho Message Center
- GET `/api/notifications` — danh sách thông báo chưa đọc + gần đây.
- POST `/api/notifications/read` — đánh dấu đã đọc 1 hoặc nhiều thông báo.

Có thể dùng luôn `auth()->user()->unreadNotifications` của Laravel và endpoint rất gọn.

## Âm thanh thông báo
- Thêm thẻ `<audio>` preload + file mp3 ngắn.
- Tùy chọn tắt/bật âm thanh lưu vào localStorage hoặc user settings.

## Cấu hình môi trường
- `.env`:
  - `FIREBASE_API_KEY=...`
  - `FIREBASE_DATABASE_URL=...`
  - `FIREBASE_PROJECT_ID=...`
  - (Render ra Blade để frontend khởi tạo Firebase)

## Kế hoạch thay thế logic cũ
1. Xác định toàn bộ điểm hiển thị/đếm thông báo cũ ở Message Center.
2. Bọc bằng feature flag `CHAT_MESSAGE_CENTER_V2`.
3. Triển khai Phương án A hoặc B.
4. So sánh số liệu kiểm thử UAT, đảm bảo không mất thông báo.
5. Gỡ bỏ code cũ sau khi ổn định.

## Kiểm thử (Checklist)
1. Gửi text từ user -> Admin/Sub Admin nhận thông báo realtime.
2. Gửi image -> hiển thị `[Hình ảnh]` trong Message Center.
3. Badge count tăng đúng khi chưa đọc, giảm khi click xem.
4. Điều hướng tới `/chat?conversationId=...` đúng cuộc hội thoại.
5. Sub Admin chỉ nhận thông báo cho conversation được phân công.
6. Reload trang vẫn giữ được danh sách (Phương án B) hoặc tái tạo từ Firebase (Phương án A).
7. Không thông báo trùng lặp khi nhiều listener.

## Rủi ro & xử lý
- Trùng lặp thông báo: sử dụng key `conversationId:messageId` để dedupe (client hoặc server).
- Quyền truy cập Firebase: đảm bảo rules chỉ cho phép đọc conversation liên quan.
- Trì hoãn realtime do mạng: hiển thị fallback polling nếu cần.

## Trạng thái triển khai

### ✅ Đã hoàn thành:
1. **Backend Laravel Notifications:**
   - ✅ `ChatNewMessageNotification` class
   - ✅ `ChatNotifyController` (API nhận sự kiện)
   - ✅ `NotificationsController` (API lấy/đánh dấu đọc)
   - ✅ Routes API: `/api/chat/notify-new-message`, `/api/notifications`, `/api/notifications/read`

2. **Frontend Message Center Hybrid:**
   - ✅ Firebase realtime listeners cho tin nhắn mới
   - ✅ Laravel API polling (backup layer)
   - ✅ Kết hợp hiển thị cả realtime + database notifications
   - ✅ Âm thanh thông báo
   - ✅ Đánh dấu đã đọc (cả realtime và database)

3. **Chat Integration:**
   - ✅ Tích hợp API notify vào tất cả chat conversation files
   - ✅ Admin, Sub Admin, User conversation đều gọi API sau khi gửi tin

### 🔄 Cần cải thiện:
1. **Filter recipients theo conversation assignment** (hiện tại notify tất cả admin/sub_admin)
2. **UI toggle âm thanh** + lưu trạng thái user preference
3. **Tests** cho API và Notification class
4. **Error handling** tốt hơn cho Firebase connection failures

### 📋 Hướng dẫn sử dụng:
- Message Center tự động hoạt động khi có tin nhắn mới
- Realtime notifications qua Firebase (tức thì)
- Database backup qua Laravel (lưu vết, reload-safe)
- Click notification → chuyển đến chat conversation
- "Đánh dấu tất cả đã đọc" → xóa cả realtime và database notifications