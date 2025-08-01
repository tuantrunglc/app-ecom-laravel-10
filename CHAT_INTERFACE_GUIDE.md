# Chat Interface Guide - Role-Based Views

## Tổng quan
Hệ thống chat đã được tách thành các giao diện riêng biệt cho từng role với màu sắc và tính năng phù hợp.

## Cấu trúc Files

### 1. Admin Views
- **Index**: `resources/views/chat/admin/index.blade.php`
- **Conversation**: `resources/views/chat/admin/conversation.blade.php`
- **Màu chủ đạo**: Đỏ (Danger) - Thể hiện quyền cao nhất
- **Tính năng**: Chat với tất cả users và sub admins

### 2. Sub Admin Views  
- **Index**: `resources/views/chat/sub_admin/index.blade.php`
- **Conversation**: `resources/views/chat/sub_admin/conversation.blade.php`
- **Màu chủ đạo**: Vàng (Warning) - Thể hiện quyền trung gian
- **Tính năng**: Chat với Admin và users được quản lý

### 3. User Views
- **Index**: `resources/views/chat/user/index.blade.php`
- **Conversation**: `resources/views/chat/user/conversation.blade.php`
- **Màu chủ đạo**: Xanh dương (Info) - Thể hiện vai trò người dùng
- **Tính năng**: Chat với Admin và Sub Admin được phân công

## Đặc điểm từng giao diện

### Admin Interface
```
🎨 Theme: Red/Danger
👑 Icon: Crown (fa-crown)
📊 Statistics: 
  - Total Users
  - Sub Admins  
  - Active Chats
  - Total Contacts
🔧 Features:
  - Chat với tất cả
  - Quản lý toàn bộ hệ thống
  - Xem thông tin chi tiết
```

### Sub Admin Interface
```
🎨 Theme: Yellow/Warning
🛡️ Icon: Shield (fa-user-tie)
📊 Statistics:
  - Managed Users
  - Active Chats
  - Admin Contact
🔧 Features:
  - Chat với Admin (supervisor)
  - Chat với users được quản lý
  - Hướng dẫn quyền hạn
```

### User Interface
```
🎨 Theme: Blue/Info
👤 Icon: User (fa-user)
📊 Statistics:
  - Total Conversations
  - Support Contacts
🔧 Features:
  - Chat với Admin
  - Chat với Sub Admin được phân công
  - Thông tin tài khoản
  - Hướng dẫn sử dụng
```

## Message Styling

### Admin Messages
- Background: `bg-danger` (đỏ)
- Text: `text-white`
- Icon: 👑 (crown)
- Border: `border-danger`

### Sub Admin Messages  
- Background: `bg-warning` (vàng)
- Text: `text-dark`
- Icon: 🛡️ (shield)
- Border: `border-warning`

### User Messages
- Background: `bg-info` (xanh)
- Text: `text-white`  
- Icon: 👤 (user)
- Border: `border-info`

## Controller Updates

### ChatController.php
```php
public function index()
{
    // ... existing code ...
    
    // Return different views based on user role
    switch ($user->role) {
        case 'admin':
            return view('chat.admin.index', compact(...));
        case 'sub_admin':
            return view('chat.sub_admin.index', compact(...));
        case 'user':
            return view('chat.user.index', compact(...));
        default:
            return view('chat.index', compact(...));
    }
}

public function showConversation($conversationId)
{
    // ... existing code ...
    
    // Return different conversation views based on user role
    switch ($user->role) {
        case 'admin':
            return view('chat.admin.conversation', compact(...));
        case 'sub_admin':
            return view('chat.sub_admin.conversation', compact(...));
        case 'user':
            return view('chat.user.conversation', compact(...));
        default:
            return view('chat.conversation', compact(...));
    }
}
```

## Responsive Design

### Mobile Compatibility
- Tất cả views đều responsive
- Sử dụng Bootstrap grid system
- Tối ưu cho màn hình nhỏ

### Color Scheme
```css
/* Admin */
.admin-theme {
    --primary-color: #dc3545;
    --bg-gradient: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

/* Sub Admin */
.sub-admin-theme {
    --primary-color: #ffc107;
    --bg-gradient: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
}

/* User */
.user-theme {
    --primary-color: #17a2b8;
    --bg-gradient: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}
```

## Testing

### Test URLs
- Admin: `/chat` (khi đăng nhập với role admin)
- Sub Admin: `/chat` (khi đăng nhập với role sub_admin)  
- User: `/chat` (khi đăng nhập với role user)

### Test Scenarios
1. **Admin**: Tạo conversation với user và sub admin
2. **Sub Admin**: Chat với admin và user được quản lý
3. **User**: Chat với admin và sub admin được phân công

## Deployment Notes

### Files Created/Modified
```
✅ Created: chat/admin/index.blade.php
✅ Created: chat/admin/conversation.blade.php
✅ Created: chat/sub_admin/index.blade.php
✅ Created: chat/sub_admin/conversation.blade.php
✅ Created: chat/user/index.blade.php
✅ Created: chat/user/conversation.blade.php
✅ Modified: ChatController.php
```

### Backup Files
- Original `chat/index.blade.php` và `chat/conversation.blade.php` vẫn được giữ làm fallback

## Future Enhancements

### Planned Features
1. **Real-time notifications** cho từng role
2. **File sharing** với quyền hạn khác nhau
3. **Message templates** cho từng role
4. **Chat analytics** dashboard
5. **Mobile app** với giao diện tương ứng

### Security Considerations
- Role-based access control đã được implement
- Firebase rules cần cập nhật theo role
- Input validation cho từng role
- Rate limiting cho messages

## Support

### Troubleshooting
1. Nếu giao diện không đúng role → Kiểm tra `auth()->user()->role`
2. Nếu màu sắc không hiển thị → Kiểm tra CSS được load
3. Nếu Firebase lỗi → Kiểm tra console logs

### Contact
- Developer: System Administrator
- Documentation: This file
- Last Updated: {{ date('Y-m-d H:i:s') }}