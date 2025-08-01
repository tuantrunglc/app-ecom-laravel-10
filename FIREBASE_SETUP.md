# Firebase Setup Guide for Chat System

## Tổng quan
Hệ thống chat này sử dụng Firebase Realtime Database cho frontend JavaScript, không cần Firebase PHP SDK.

## Bước 1: Cấu hình Firebase Authentication

1. Truy cập [Firebase Console](https://console.firebase.google.com/)
2. Chọn project `eshop-chat-system`
3. Vào **Authentication** > **Sign-in method**
4. Bật **Anonymous** authentication
5. Save changes

## Bước 2: Cập nhật Firebase Security Rules

**QUAN TRỌNG:** Do lỗi `auth/admin-restricted-operation`, hãy sử dụng rules đơn giản cho testing:

Vào **Realtime Database** > **Rules** và paste rules sau:

```json
{
  "rules": {
    ".read": true,
    ".write": true
  }
}
```

**Lưu ý:** Rules này chỉ dùng cho testing. Sau khi chat hoạt động, hãy cập nhật rules bảo mật:

```json
{
  "rules": {
    "conversations": {
      "$conversationId": {
        ".read": true,
        ".write": true
      }
    },
    "messages": {
      "$conversationId": {
        ".read": true,
        ".write": true
      }
    },
    "userPresence": {
      "$userId": {
        ".read": true,
        ".write": true
      }
    },
    "test": {
      ".read": true,
      ".write": true
    }
  }
}
```

## Bước 2.1: Bật Anonymous Authentication

1. Vào **Authentication** > **Sign-in method**
2. Bật **Anonymous** authentication
3. Save changes

## Bước 3: Test Chat System

1. Đăng nhập với tài khoản Admin
2. Truy cập `/chat` 
3. Chọn user để chat
4. Test gửi tin nhắn và hình ảnh

## Bước 4: Troubleshooting

### Lỗi Firebase Authentication
- Kiểm tra file `firebase-service-account.json` có đúng format không
- Kiểm tra project ID trong file JSON có khớp với .env không

### Lỗi Permission Denied
- Kiểm tra Firebase Security Rules đã được cập nhật chưa
- Kiểm tra user có quyền chat với nhau không (theo business rules)

### Lỗi không load được messages
- Kiểm tra Firebase Realtime Database URL trong .env
- Kiểm tra network connection

## Cấu trúc dữ liệu Firebase

```
/conversations/{conversationId}
  - id: string
  - type: "direct"
  - participants: object
  - lastMessage: object
  - unreadCount: object
  - createdAt: timestamp
  - updatedAt: timestamp

/messages/{conversationId}/{messageId}
  - id: string
  - conversationId: string
  - senderId: number
  - senderName: string
  - senderRole: string
  - content: string
  - type: "text" | "image"
  - timestamp: timestamp
  - readBy: object
  - imageUrl: string (optional)
  - imageName: string (optional)

/userPresence/{userId}
  - status: "online" | "offline"
  - lastSeen: timestamp
```