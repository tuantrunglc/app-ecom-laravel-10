# Hệ Thống Chat Firebase - Tài Liệu Phân Tích và Hướng Dẫn Triển Khai

## 1. TỔNG QUAN HỆ THỐNG

### 1.1 Mục Tiêu
Xây dựng hệ thống chat real-time giữa Admin, Sub Admin và User sử dụng Firebase Realtime Database hoặc Firestore, tích hợp với hệ thống Laravel hiện tại.

### 1.2 Yêu Cầu Chức Năng
- **Admin**: Chat với tất cả User và Sub Admin
- **Sub Admin**: Chỉ chat với User thuộc quyền quản lý (parent_sub_admin_id)
- **User**: Chat với Admin và Sub Admin quản lý mình
- Real-time messaging với thông báo push
- Lưu trữ lịch sử chat
- Upload file/hình ảnh trong chat
- Trạng thái online/offline
- Đánh dấu tin nhắn đã đọc/chưa đọc

### 1.3 Kiến Trúc Tổng Thể
```
Laravel Backend ←→ Firebase (Real-time Chat) ←→ Blade Templates + JavaScript
        ↓                    ↓                           ↓
   User Management    Message Storage              Chat Interface
   Authentication     Real-time Sync               Push Notifications  
   Permission Control File Storage                 Online Status
   Session Management Firebase Auth Integration    DOM Manipulation
```

**Chi tiết kiến trúc:**
- **Laravel Backend**: Xử lý authentication, user management, permissions, file upload
- **Firebase**: Real-time messaging, presence, notifications, file storage
- **Blade Templates**: Render chat interface, user lists, conversation layouts
- **Vanilla JavaScript**: Firebase SDK integration, real-time listeners, DOM updates

## 2. PHÂN TÍCH HỆ THỐNG HIỆN TẠI

### 2.1 Cấu Trúc User Hiện Tại (Từ SUB_ADMIN_SYSTEM_DOCUMENTATION.md)
```sql
users table:
- id (Primary Key)
- name, email, password
- role (enum: 'admin', 'sub_admin', 'user')
- parent_sub_admin_id (Sub Admin quản lý User)
- sub_admin_code (Mã Sub Admin)
- status ('active', 'inactive')
- photo (avatar)
```

### 2.2 Quyền Hạn Chat Theo Role

**ADMIN (Super Admin)**
```
Chat Permissions:
├── Chat với tất cả Users
├── Chat với tất cả Sub Admins
├── Tạo group chat
├── Xem tất cả conversations
├── Quản lý/xóa tin nhắn
├── Broadcast message tới nhiều users
├── Xem thống kê chat
└── Cấu hình chat settings
```

**SUB_ADMIN**
```
Chat Permissions:
├── Chat với Users có parent_sub_admin_id = sub_admin.id
├── Chat với Admin (Super Admin)
├── Xem conversations của users thuộc quyền
├── Không thể chat với Sub Admin khác
├── Không thể chat với Users của Sub Admin khác
├── Nhận thông báo từ Admin
└── Báo cáo chat statistics của users thuộc quyền

Restrictions:
├── Không thể xem chat giữa Admin và Users khác
├── Không thể xem chat giữa Sub Admin khác và Users
├── Không thể tạo group chat
└── Không thể broadcast message
```

**USER**
```
Chat Permissions:
├── Chat với Admin
├── Chat với Sub Admin quản lý (nếu có parent_sub_admin_id)
├── Xem lịch sử chat của mình
├── Upload file/hình ảnh
└── Nhận support từ Admin/Sub Admin

Restrictions:
├── Không thể chat với Users khác
├── Không thể chat với Sub Admin không phải của mình
├── Không thể tạo group chat
└── Chỉ xem được conversations của mình
```

## 3. THIẾT KẾ FIREBASE DATABASE

### 3.1 Cấu Trúc Firebase Realtime Database

```json
{
  "chats": {
    "conversations": {
      "{conversationId}": {
        "id": "conv_123456",
        "type": "direct", // "direct" | "group" | "support"
        "participants": {
          "user_1": {
            "id": 1,
            "role": "admin",
            "name": "Admin Name",
            "avatar": "avatar_url",
            "joinedAt": "2024-01-01T00:00:00Z",
            "lastSeen": "2024-01-01T12:00:00Z"
          },
          "user_2": {
            "id": 5,
            "role": "user", 
            "name": "User Name",
            "avatar": "avatar_url",
            "parentSubAdminId": 3,
            "joinedAt": "2024-01-01T00:00:00Z",
            "lastSeen": "2024-01-01T11:30:00Z"
          }
        },
        "metadata": {
          "createdAt": "2024-01-01T00:00:00Z",
          "updatedAt": "2024-01-01T12:00:00Z",
          "createdBy": 1,
          "title": "Support Chat",
          "description": "Customer support conversation",
          "status": "active", // "active" | "archived" | "closed"
          "priority": "normal", // "low" | "normal" | "high" | "urgent"
          "category": "support", // "support" | "sales" | "general"
          "tags": ["order", "payment"]
        },
        "lastMessage": {
          "id": "msg_789",
          "senderId": 1,
          "senderName": "Admin Name",
          "content": "How can I help you?",
          "type": "text",
          "timestamp": "2024-01-01T12:00:00Z"
        },
        "unreadCount": {
          "user_1": 0,
          "user_2": 2
        }
      }
    },
    
    "messages": {
      "{conversationId}": {
        "{messageId}": {
          "id": "msg_123456",
          "conversationId": "conv_123456",
          "senderId": 1,
          "senderName": "Admin Name",
          "senderRole": "admin",
          "senderAvatar": "avatar_url",
          "content": "Hello, how can I help you?",
          "type": "text", // "text" | "image" | "file" | "system" | "order_update"
          "timestamp": "2024-01-01T12:00:00Z",
          "editedAt": null,
          "status": "sent", // "sending" | "sent" | "delivered" | "read" | "failed"
          "readBy": {
            "user_1": "2024-01-01T12:00:00Z",
            "user_2": null
          },
          "metadata": {
            "fileUrl": null,
            "fileName": null,
            "fileSize": null,
            "fileType": null,
            "thumbnailUrl": null,
            "orderId": null, // Nếu là tin nhắn về đơn hàng
            "replyTo": null, // ID tin nhắn được reply
            "isEdited": false,
            "isDeleted": false,
            "reactions": {
              "👍": ["user_1", "user_2"],
              "❤️": ["user_1"]
            }
          }
        }
      }
    },
    
    "userPresence": {
      "{userId}": {
        "status": "online", // "online" | "offline" | "away"
        "lastSeen": "2024-01-01T12:00:00Z",
        "currentConversation": "conv_123456",
        "deviceInfo": {
          "platform": "web", // "web" | "mobile" | "desktop"
          "userAgent": "Mozilla/5.0...",
          "ipAddress": "192.168.1.1"
        }
      }
    },
    
    "notifications": {
      "{userId}": {
        "{notificationId}": {
          "id": "notif_123456",
          "type": "new_message", // "new_message" | "mention" | "new_conversation"
          "conversationId": "conv_123456",
          "messageId": "msg_123456",
          "fromUserId": 1,
          "fromUserName": "Admin Name",
          "title": "New message from Admin",
          "body": "Hello, how can I help you?",
          "timestamp": "2024-01-01T12:00:00Z",
          "isRead": false,
          "actionUrl": "/chat/conv_123456"
        }
      }
    },
    
    "chatSettings": {
      "global": {
        "maxFileSize": 10485760, // 10MB
        "allowedFileTypes": ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx"],
        "messageRetentionDays": 365,
        "enableFileUpload": true,
        "enableImageUpload": true,
        "enableVoiceMessage": false,
        "enableVideoCall": false,
        "autoDeleteAfterDays": null
      },
      "users": {
        "{userId}": {
          "notifications": {
            "email": true,
            "push": true,
            "sound": true,
            "desktop": true
          },
          "privacy": {
            "showOnlineStatus": true,
            "showLastSeen": true,
            "allowDirectMessages": true
          },
          "preferences": {
            "theme": "light", // "light" | "dark" | "auto"
            "language": "vi",
            "timezone": "Asia/Ho_Chi_Minh",
            "dateFormat": "DD/MM/YYYY",
            "timeFormat": "24h"
          }
        }
      }
    }
  }
}
```

### 3.2 Firebase Security Rules

```javascript
{
  "rules": {
    "chats": {
      "conversations": {
        "$conversationId": {
          ".read": "auth != null && (
            // Admin có thể đọc tất cả
            root.child('users').child(auth.uid).child('role').val() == 'admin' ||
            // User chỉ đọc conversation mình tham gia
            data.child('participants').child('user_' + auth.uid).exists() ||
            // Sub Admin chỉ đọc conversation với users thuộc quyền
            (root.child('users').child(auth.uid).child('role').val() == 'sub_admin' && 
             root.child('conversations').child($conversationId).child('participants').child('user_' + auth.uid).exists())
          )",
          ".write": "auth != null && (
            // Admin có thể tạo/sửa tất cả conversation
            root.child('users').child(auth.uid).child('role').val() == 'admin' ||
            // User/Sub Admin chỉ có thể tạo conversation với người được phép
            data.child('participants').child('user_' + auth.uid).exists()
          )"
        }
      },
      
      "messages": {
        "$conversationId": {
          "$messageId": {
            ".read": "auth != null && (
              // Kiểm tra quyền đọc conversation trước
              root.child('chats/conversations').child($conversationId).child('participants').child('user_' + auth.uid).exists() ||
              root.child('users').child(auth.uid).child('role').val() == 'admin'
            )",
            ".write": "auth != null && (
              // Chỉ cho phép tạo message mới hoặc cập nhật message của mình
              !data.exists() && newData.child('senderId').val() == auth.uid ||
              data.child('senderId').val() == auth.uid ||
              root.child('users').child(auth.uid).child('role').val() == 'admin'
            )"
          }
        }
      },
      
      "userPresence": {
        "$userId": {
          ".read": "auth != null",
          ".write": "auth != null && auth.uid == $userId"
        }
      },
      
      "notifications": {
        "$userId": {
          ".read": "auth != null && auth.uid == $userId",
          ".write": "auth != null && (
            auth.uid == $userId ||
            root.child('users').child(auth.uid).child('role').val() == 'admin'
          )"
        }
      }
    }
  }
}
```

## 4. HƯỚNG DẪN SETUP FIREBASE

### 4.1 Tạo Firebase Project

#### Bước 1: Tạo Project trên Firebase Console
1. Truy cập [Firebase Console](https://console.firebase.google.com/)
2. Click "Add project" hoặc "Tạo dự án"
3. Nhập tên project: `ecom-chat-system`
4. Chọn "Continue"
5. Tắt Google Analytics (không cần thiết cho chat)
6. Click "Create project"

#### Bước 2: Thêm Web App
1. Trong Firebase Console, click vào icon Web (</>) 
2. Nhập App nickname: `ecom-chat-web`
3. Chọn "Also set up Firebase Hosting" (tùy chọn)
4. Click "Register app"
5. Copy Firebase configuration object (sẽ dùng sau)

```javascript
// Firebase Config Example
const firebaseConfig = {
  apiKey: "AIzaSyC...",
  authDomain: "ecom-chat-system.firebaseapp.com",
  databaseURL: "https://ecom-chat-system-default-rtdb.asia-southeast1.firebasedatabase.app",
  projectId: "ecom-chat-system",
  storageBucket: "ecom-chat-system.appspot.com",
  messagingSenderId: "123456789",
  appId: "1:123456789:web:abc123def456"
};
```

### 4.2 Cấu Hình Firebase Services

#### Bước 1: Enable Realtime Database
1. Trong Firebase Console, vào "Realtime Database"
2. Click "Create Database"
3. Chọn location: `asia-southeast1` (Singapore - gần Việt Nam nhất)
4. Chọn "Start in test mode" (sẽ cấu hình security rules sau)
5. Click "Enable"

#### Bước 2: Enable Cloud Storage
1. Vào "Storage" trong Firebase Console
2. Click "Get started"
3. Chọn "Start in test mode"
4. Chọn location: `asia-southeast1`
5. Click "Done"

#### Bước 3: Enable Cloud Messaging (FCM)
1. Vào "Cloud Messaging" trong Firebase Console
2. Click "Get started"
3. Tạo Web Push Certificate:
   - Vào "Settings" > "Cloud Messaging"
   - Trong "Web configuration", click "Generate key pair"
   - Copy VAPID key (sẽ dùng cho push notifications)

### 4.3 Cấu Hình Firebase Authentication (Tùy chọn)

#### Nếu muốn sử dụng Firebase Auth thay vì Laravel Auth:
1. Vào "Authentication" > "Sign-in method"
2. Enable "Email/Password"
3. Enable "Google" (tùy chọn)
4. Trong "Settings" > "Authorized domains", thêm domain của bạn

#### Nếu sử dụng Laravel Auth (Khuyến nghị):
- Không cần enable Firebase Authentication
- Sử dụng Custom Token để authenticate với Firebase
- Laravel sẽ generate custom token cho Firebase

### 4.4 Setup Firebase Admin SDK (Laravel Backend)

#### Bước 1: Tạo Service Account
1. Vào "Settings" > "Service accounts"
2. Click "Generate new private key"
3. Download file JSON (ví dụ: `ecom-chat-firebase-adminsdk.json`)
4. Lưu file này vào `storage/app/firebase/` trong Laravel project

#### Bước 2: Install Firebase Admin SDK cho PHP
```bash
composer require kreait/firebase-php
```

#### Bước 3: Cấu hình Laravel
```php
// config/firebase.php
<?php

return [
    'credentials' => storage_path('app/firebase/ecom-chat-firebase-adminsdk.json'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
    'app_id' => env('FIREBASE_APP_ID'),
    'vapid_key' => env('FIREBASE_VAPID_KEY'),
];
```

```bash
# .env additions
FIREBASE_API_KEY=AIzaSyC...
FIREBASE_AUTH_DOMAIN=ecom-chat-system.firebaseapp.com
FIREBASE_DATABASE_URL=https://ecom-chat-system-default-rtdb.asia-southeast1.firebasedatabase.app
FIREBASE_PROJECT_ID=ecom-chat-system
FIREBASE_STORAGE_BUCKET=ecom-chat-system.appspot.com
FIREBASE_MESSAGING_SENDER_ID=123456789
FIREBASE_APP_ID=1:123456789:web:abc123def456
FIREBASE_VAPID_KEY=BG7s...
```

### 4.5 Cấu Hình Firebase Security Rules

#### Realtime Database Rules
```javascript
{
  "rules": {
    "chats": {
      ".read": "auth != null",
      ".write": "auth != null",
      
      "conversations": {
        "$conversationId": {
          ".validate": "newData.hasChildren(['id', 'participants', 'metadata'])",
          
          // Chỉ participants mới có thể đọc conversation
          ".read": "auth != null && (
            // Admin có thể đọc tất cả
            root.child('users').child(auth.uid).child('role').val() == 'admin' ||
            // User chỉ đọc conversation mình tham gia
            data.child('participants').child('user_' + auth.uid).exists()
          )",
          
          // Chỉ admin hoặc participants mới có thể tạo/sửa
          ".write": "auth != null && (
            root.child('users').child(auth.uid).child('role').val() == 'admin' ||
            data.child('participants').child('user_' + auth.uid).exists() ||
            newData.child('participants').child('user_' + auth.uid).exists()
          )",
          
          "participants": {
            "$participantKey": {
              // Validate participant structure
              ".validate": "newData.hasChildren(['id', 'role', 'name'])",
              
              // Chỉ admin hoặc chính user đó mới có thể thêm/sửa
              ".write": "auth != null && (
                root.child('users').child(auth.uid).child('role').val() == 'admin' ||
                $participantKey == 'user_' + auth.uid
              )"
            }
          },
          
          "metadata": {
            "createdBy": {
              ".validate": "newData.val() == auth.uid || root.child('users').child(auth.uid).child('role').val() == 'admin'"
            }
          }
        }
      },
      
      "messages": {
        "$conversationId": {
          // Chỉ participants của conversation mới đọc được messages
          ".read": "auth != null && (
            root.child('users').child(auth.uid).child('role').val() == 'admin' ||
            root.child('chats/conversations').child($conversationId).child('participants').child('user_' + auth.uid).exists()
          )",
          
          "$messageId": {
            ".validate": "newData.hasChildren(['senderId', 'content', 'timestamp'])",
            
            // Chỉ có thể tạo message mới hoặc sửa message của mình
            ".write": "auth != null && (
              // Tạo message mới
              (!data.exists() && newData.child('senderId').val() == auth.uid) ||
              // Sửa message của mình
              (data.exists() && data.child('senderId').val() == auth.uid) ||
              // Admin có thể sửa tất cả
              root.child('users').child(auth.uid).child('role').val() == 'admin'
            )",
            
            "senderId": {
              ".validate": "newData.val() == auth.uid"
            },
            
            "content": {
              ".validate": "newData.isString() && newData.val().length > 0 && newData.val().length <= 5000"
            },
            
            "timestamp": {
              ".validate": "newData.val() <= now"
            }
          }
        }
      },
      
      "userPresence": {
        "$userId": {
          ".read": "auth != null",
          ".write": "auth != null && auth.uid == $userId",
          
          "status": {
            ".validate": "newData.val() == 'online' || newData.val() == 'offline' || newData.val() == 'away'"
          }
        }
      },
      
      "notifications": {
        "$userId": {
          ".read": "auth != null && auth.uid == $userId",
          ".write": "auth != null && (
            auth.uid == $userId ||
            root.child('users').child(auth.uid).child('role').val() == 'admin'
          )"
        }
      }
    },
    
    // User data for permission checking
    "users": {
      "$userId": {
        ".read": "auth != null",
        ".write": "auth != null && (auth.uid == $userId || root.child('users').child(auth.uid).child('role').val() == 'admin')",
        
        "role": {
          ".validate": "newData.val() == 'admin' || newData.val() == 'sub_admin' || newData.val() == 'user'"
        }
      }
    }
  }
}
```

#### Cloud Storage Rules
```javascript
rules_version = '2';
service firebase.storage {
  match /b/{bucket}/o {
    // Chat files
    match /chat_files/{conversationId}/{fileName} {
      allow read: if request.auth != null && 
        (resource == null || 
         firestore.get(/databases/(default)/documents/users/$(request.auth.uid)).data.role == 'admin' ||
         isParticipantInConversation(conversationId, request.auth.uid));
      
      allow write: if request.auth != null && 
        request.resource.size < 10 * 1024 * 1024 && // 10MB limit
        request.resource.contentType.matches('image/.*|application/pdf|application/msword|application/vnd.openxmlformats-officedocument.wordprocessingml.document') &&
        isParticipantInConversation(conversationId, request.auth.uid);
    }
    
    // Helper function to check if user is participant in conversation
    function isParticipantInConversation(conversationId, userId) {
      return firestore.get(/databases/(default)/documents/conversations/$(conversationId)).data.participants[('user_' + userId)] != null;
    }
  }
}
```

### 4.6 Test Firebase Connection

#### Tạo file test trong Laravel
```php
// routes/web.php
Route::get('/test-firebase', function () {
    try {
        $factory = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
        
        $database = $factory->createDatabase();
        
        // Test write
        $database->getReference('test')->set([
            'message' => 'Hello from Laravel!',
            'timestamp' => time()
        ]);
        
        // Test read
        $snapshot = $database->getReference('test')->getSnapshot();
        
        return response()->json([
            'status' => 'success',
            'data' => $snapshot->getValue()
        ]);
        
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});
```

#### Test từ browser
```javascript
// Test trong browser console
const firebaseConfig = {
    // Your config here
};

firebase.initializeApp(firebaseConfig);
const database = firebase.database();

// Test write
database.ref('test-browser').set({
    message: 'Hello from browser!',
    timestamp: Date.now()
}).then(() => {
    console.log('Write successful');
}).catch((error) => {
    console.error('Write failed:', error);
});

// Test read
database.ref('test-browser').once('value').then((snapshot) => {
    console.log('Data:', snapshot.val());
}).catch((error) => {
    console.error('Read failed:', error);
});
```

### 4.7 Sync Users từ Laravel sang Firebase

#### Tạo Command để sync users
```php
// app/Console/Commands/SyncUsersToFirebase.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Kreait\Firebase\Factory;

class SyncUsersToFirebase extends Command
{
    protected $signature = 'firebase:sync-users';
    protected $description = 'Sync Laravel users to Firebase for permission checking';

    public function handle()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
        
        $database = $factory->createDatabase();
        
        $users = User::where('status', 'active')->get();
        
        foreach ($users as $user) {
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'parent_sub_admin_id' => $user->parent_sub_admin_id,
                'photo' => $user->photo,
                'updated_at' => now()->toISOString()
            ];
            
            $database->getReference("users/{$user->id}")->set($userData);
            
            $this->info("Synced user: {$user->name}");
        }
        
        $this->info("Sync completed! Total users: " . $users->count());
    }
}
```

#### Chạy sync command
```bash
php artisan firebase:sync-users
```

### 4.8 Tạo Custom Authentication Tokens

#### Service để tạo custom tokens
```php
// app/Services/FirebaseAuthService.php
<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use App\Models\User;

class FirebaseAuthService
{
    private $auth;
    
    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'));
        
        $this->auth = $factory->createAuth();
    }
    
    public function createCustomToken(User $user)
    {
        $customClaims = [
            'role' => $user->role,
            'parent_sub_admin_id' => $user->parent_sub_admin_id,
            'email' => $user->email,
            'name' => $user->name
        ];
        
        return $this->auth->createCustomToken($user->id, $customClaims);
    }
    
    public function verifyToken($idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (\Exception $e) {
            return null;
        }
    }
}
```

#### API endpoint để lấy Firebase token
```php
// routes/api.php
Route::middleware('auth:sanctum')->post('/firebase/token', function (Request $request) {
    $firebaseAuth = new \App\Services\FirebaseAuthService();
    $customToken = $firebaseAuth->createCustomToken($request->user());
    
    return response()->json([
        'firebase_token' => $customToken->toString()
    ]);
});
```

### 4.9 Frontend Firebase Authentication

```javascript
// resources/js/chat/firebase-auth.js
class FirebaseAuth {
    constructor() {
        this.auth = firebase.auth();
        this.currentUser = null;
    }
    
    async authenticateWithLaravel() {
        try {
            // Get custom token from Laravel
            const response = await fetch('/api/firebase/token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${this.getLaravelToken()}`
                }
            });
            
            const data = await response.json();
            
            // Sign in with custom token
            const userCredential = await this.auth.signInWithCustomToken(data.firebase_token);
            this.currentUser = userCredential.user;
            
            console.log('Firebase authentication successful');
            return this.currentUser;
            
        } catch (error) {
            console.error('Firebase authentication failed:', error);
            throw error;
        }
    }
    
    getLaravelToken() {
        // Get Laravel Sanctum token from meta tag or localStorage
        return document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
    }
    
    async signOut() {
        await this.auth.signOut();
        this.currentUser = null;
    }
    
    onAuthStateChanged(callback) {
        return this.auth.onAuthStateChanged(callback);
    }
}

// Global instance
window.FirebaseAuth = new FirebaseAuth();
```

### 4.10 Firebase Database Structure Setup

#### Tạo cấu trúc database ban đầu
```javascript
// Script để tạo cấu trúc database ban đầu
// Chạy trong Firebase Console > Database > Data tab

{
  "chats": {
    "conversations": {
      ".info": "Conversations between users"
    },
    "messages": {
      ".info": "Messages in conversations"
    },
    "userPresence": {
      ".info": "Online/offline status of users"
    },
    "notifications": {
      ".info": "Push notifications for users"
    },
    "chatSettings": {
      "global": {
        "maxFileSize": 10485760,
        "allowedFileTypes": ["jpg", "jpeg", "png", "gif", "pdf", "doc", "docx"],
        "messageRetentionDays": 365,
        "enableFileUpload": true,
        "enableImageUpload": true,
        "enableVoiceMessage": false,
        "enableVideoCall": false,
        "autoDeleteAfterDays": null
      }
    }
  },
  "users": {
    ".info": "User data synced from Laravel for permission checking"
  }
}
```

### 4.11 Firebase Indexes (Tối ưu hiệu suất)

#### Tạo indexes trong Firebase Console
1. Vào "Database" > "Rules" > "Indexes"
2. Thêm các indexes sau:

```json
{
  "rules": {
    "chats": {
      "messages": {
        "$conversationId": {
          ".indexOn": ["timestamp", "senderId"]
        }
      },
      "conversations": {
        ".indexOn": ["metadata/updatedAt", "metadata/createdBy"]
      },
      "userPresence": {
        ".indexOn": ["status", "lastSeen"]
      }
    }
  }
}
```

### 4.12 Firebase Cloud Functions (Tùy chọn)

#### Setup Cloud Functions cho advanced features
```bash
# Install Firebase CLI
npm install -g firebase-tools

# Login to Firebase
firebase login

# Initialize functions
firebase init functions
```

#### Example Cloud Function cho push notifications
```javascript
// functions/index.js
const functions = require('firebase-functions');
const admin = require('firebase-admin');

admin.initializeApp();

// Send push notification when new message is created
exports.sendMessageNotification = functions.database
  .ref('/chats/messages/{conversationId}/{messageId}')
  .onCreate(async (snapshot, context) => {
    const message = snapshot.val();
    const conversationId = context.params.conversationId;
    
    // Get conversation participants
    const conversationSnapshot = await admin.database()
      .ref(`/chats/conversations/${conversationId}`)
      .once('value');
    
    const conversation = conversationSnapshot.val();
    if (!conversation) return null;
    
    // Get recipient tokens
    const participants = conversation.participants;
    const senderKey = `user_${message.senderId}`;
    
    const recipientIds = Object.keys(participants)
      .filter(key => key !== senderKey)
      .map(key => participants[key].id);
    
    // Get FCM tokens for recipients
    const tokenPromises = recipientIds.map(userId => 
      admin.database().ref(`/fcmTokens/${userId}`).once('value')
    );
    
    const tokenSnapshots = await Promise.all(tokenPromises);
    const tokens = [];
    
    tokenSnapshots.forEach(snapshot => {
      const userTokens = snapshot.val();
      if (userTokens) {
        Object.values(userTokens).forEach(tokenData => {
          if (tokenData.isActive) {
            tokens.push(tokenData.token);
          }
        });
      }
    });
    
    if (tokens.length === 0) return null;
    
    // Send notification
    const payload = {
      notification: {
        title: `New message from ${message.senderName}`,
        body: message.type === 'text' ? message.content : `Sent a ${message.type}`,
        icon: '/images/chat-icon.png'
      },
      data: {
        conversationId: conversationId,
        messageId: message.id,
        type: 'new_message'
      }
    };
    
    return admin.messaging().sendToDevice(tokens, payload);
  });

// Clean up old messages
exports.cleanupOldMessages = functions.pubsub
  .schedule('0 2 * * *') // Run daily at 2 AM
  .onRun(async (context) => {
    const retentionDays = 365;
    const cutoffTime = Date.now() - (retentionDays * 24 * 60 * 60 * 1000);
    
    const messagesRef = admin.database().ref('/chats/messages');
    const snapshot = await messagesRef.once('value');
    
    const updates = {};
    
    snapshot.forEach(conversationSnapshot => {
      const conversationId = conversationSnapshot.key;
      const messages = conversationSnapshot.val();
      
      Object.keys(messages).forEach(messageId => {
        const message = messages[messageId];
        if (message.timestamp < cutoffTime) {
          updates[`/chats/messages/${conversationId}/${messageId}`] = null;
        }
      });
    });
    
    return admin.database().ref().update(updates);
  });
```

### 4.13 Firebase Performance Monitoring

#### Enable Performance Monitoring
1. Trong Firebase Console, vào "Performance"
2. Click "Get started"
3. Thêm Performance SDK vào web app:

```html
<!-- Add to your main layout -->
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-performance-compat.js"></script>

<script>
// Initialize Performance Monitoring
const perf = firebase.performance();

// Custom traces for chat operations
function trackChatOperation(operationName, callback) {
    const trace = perf.trace(operationName);
    trace.start();
    
    const result = callback();
    
    if (result instanceof Promise) {
        return result.finally(() => trace.stop());
    } else {
        trace.stop();
        return result;
    }
}

// Usage example
trackChatOperation('send_message', () => {
    return ChatService.sendMessage(conversationId, content);
});
</script>
```

### 4.14 Firebase Analytics cho Chat

```javascript
// resources/js/chat/firebase-analytics.js
class ChatAnalytics {
    constructor() {
        this.analytics = firebase.analytics();
    }
    
    trackMessageSent(messageType, conversationId) {
        this.analytics.logEvent('message_sent', {
            message_type: messageType,
            conversation_id: conversationId
        });
    }
    
    trackConversationStarted(participantCount) {
        this.analytics.logEvent('conversation_started', {
            participant_count: participantCount
        });
    }
    
    trackFileUploaded(fileType, fileSize) {
        this.analytics.logEvent('file_uploaded', {
            file_type: fileType,
            file_size: fileSize
        });
    }
    
    trackChatSessionDuration(duration) {
        this.analytics.logEvent('chat_session_duration', {
            duration_seconds: duration
        });
    }
}

window.ChatAnalytics = new ChatAnalytics();
```

### 4.15 Firebase Security Best Practices

#### 1. Environment Variables Security
```bash
# .env.example
FIREBASE_API_KEY=your_api_key_here
FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
FIREBASE_DATABASE_URL=https://your_project.firebaseio.com
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_STORAGE_BUCKET=your_project.appspot.com
FIREBASE_MESSAGING_SENDER_ID=123456789
FIREBASE_APP_ID=your_app_id
FIREBASE_VAPID_KEY=your_vapid_key

# Không commit file service account JSON vào git
# Thêm vào .gitignore:
storage/app/firebase/
*.json
```

#### 2. Rate Limiting trong Security Rules
```javascript
{
  "rules": {
    "chats": {
      "messages": {
        "$conversationId": {
          "$messageId": {
            ".write": "auth != null && 
              newData.child('senderId').val() == auth.uid &&
              // Rate limiting: max 10 messages per minute
              query(
                root.child('chats/messages').child($conversationId),
                orderByChild('senderId'),
                equalTo(auth.uid),
                limitToLast(10)
              ).length < 10"
          }
        }
      }
    }
  }
}
```

#### 3. Data Validation
```javascript
{
  "rules": {
    "chats": {
      "messages": {
        "$conversationId": {
          "$messageId": {
            "content": {
              ".validate": "newData.isString() && 
                newData.val().length > 0 && 
                newData.val().length <= 5000 &&
                // Không chứa script tags
                !newData.val().matches('.*<script.*>.*')"
            },
            "type": {
              ".validate": "newData.val() == 'text' || 
                newData.val() == 'image' || 
                newData.val() == 'file' || 
                newData.val() == 'system'"
            }
          }
        }
      }
    }
  }
}
```

### 4.16 Firebase Backup và Recovery

#### 1. Automated Backup Script
```php
// app/Console/Commands/BackupFirebaseData.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Storage;

class BackupFirebaseData extends Command
{
    protected $signature = 'firebase:backup';
    protected $description = 'Backup Firebase Realtime Database';

    public function handle()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
        
        $database = $factory->createDatabase();
        
        // Backup conversations
        $conversations = $database->getReference('chats/conversations')->getSnapshot()->getValue();
        $this->saveBackup('conversations', $conversations);
        
        // Backup recent messages (last 30 days)
        $cutoffTime = now()->subDays(30)->timestamp * 1000;
        $messages = $database->getReference('chats/messages')->getSnapshot()->getValue();
        
        $recentMessages = [];
        foreach ($messages as $conversationId => $conversationMessages) {
            foreach ($conversationMessages as $messageId => $message) {
                if ($message['timestamp'] > $cutoffTime) {
                    $recentMessages[$conversationId][$messageId] = $message;
                }
            }
        }
        
        $this->saveBackup('messages', $recentMessages);
        
        $this->info('Firebase backup completed successfully');
    }
    
    private function saveBackup($type, $data)
    {
        $filename = "firebase_backup_{$type}_" . now()->format('Y-m-d_H-i-s') . '.json';
        Storage::disk('local')->put("backups/firebase/{$filename}", json_encode($data, JSON_PRETTY_PRINT));
    }
}
```

#### 2. Schedule Backup
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 3 AM
    $schedule->command('firebase:backup')
             ->dailyAt('03:00')
             ->emailOutputOnFailure('admin@example.com');
}
```

### 4.17 Firebase Monitoring và Alerting

#### 1. Setup Monitoring Dashboard
```php
// app/Http/Controllers/Admin/FirebaseMonitorController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Kreait\Firebase\Factory;

class FirebaseMonitorController extends Controller
{
    public function dashboard()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
        
        $database = $factory->createDatabase();
        
        // Get statistics
        $stats = [
            'total_conversations' => $this->getConversationCount($database),
            'active_users' => $this->getActiveUserCount($database),
            'messages_today' => $this->getMessagesToday($database),
            'storage_usage' => $this->getStorageUsage(),
        ];
        
        return view('admin.firebase-monitor', compact('stats'));
    }
    
    private function getConversationCount($database)
    {
        $snapshot = $database->getReference('chats/conversations')->getSnapshot();
        return $snapshot->numChildren();
    }
    
    private function getActiveUserCount($database)
    {
        $snapshot = $database->getReference('chats/userPresence')->getSnapshot();
        $activeCount = 0;
        
        foreach ($snapshot->getValue() as $userId => $presence) {
            if ($presence['status'] === 'online') {
                $activeCount++;
            }
        }
        
        return $activeCount;
    }
    
    private function getMessagesToday($database)
    {
        $todayStart = now()->startOfDay()->timestamp * 1000;
        $snapshot = $database->getReference('chats/messages')->getSnapshot();
        $count = 0;
        
        foreach ($snapshot->getValue() as $conversationMessages) {
            foreach ($conversationMessages as $message) {
                if ($message['timestamp'] >= $todayStart) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}
```

### 4.18 Firebase Troubleshooting Guide

#### 1. Common Firebase Connection Issues

**Problem**: "Firebase: No Firebase App '[DEFAULT]' has been created"
```javascript
// Solution: Ensure Firebase is initialized before use
// Check if Firebase is loaded
if (typeof firebase === 'undefined') {
    console.error('Firebase SDK not loaded');
    // Load Firebase SDK dynamically
    const script = document.createElement('script');
    script.src = 'https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js';
    document.head.appendChild(script);
}

// Initialize Firebase properly
if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
} else {
    firebase.app(); // Use existing app
}
```

**Problem**: "Permission denied" errors
```javascript
// Debug Firebase Auth
firebase.auth().onAuthStateChanged((user) => {
    if (user) {
        console.log('User authenticated:', user.uid);
        // Check custom claims
        user.getIdTokenResult().then((idTokenResult) => {
            console.log('Custom claims:', idTokenResult.claims);
        });
    } else {
        console.log('User not authenticated');
        // Re-authenticate with Laravel
        window.FirebaseAuth.authenticateWithLaravel();
    }
});
```

**Problem**: "Network request failed"
```javascript
// Check Firebase connection
function testFirebaseConnection() {
    const testRef = firebase.database().ref('.info/connected');
    testRef.on('value', (snapshot) => {
        if (snapshot.val() === true) {
            console.log('Firebase connected');
        } else {
            console.log('Firebase disconnected');
            // Implement retry logic
            setTimeout(() => {
                window.location.reload();
            }, 5000);
        }
    });
}

testFirebaseConnection();
```

#### 2. Laravel Firebase Integration Issues

**Problem**: "Service account file not found"
```php
// Check if service account file exists
if (!file_exists(config('firebase.credentials'))) {
    throw new Exception('Firebase service account file not found at: ' . config('firebase.credentials'));
}

// Verify file permissions
if (!is_readable(config('firebase.credentials'))) {
    throw new Exception('Firebase service account file is not readable');
}
```

**Problem**: "Invalid custom token"
```php
// Debug custom token creation
try {
    $firebaseAuth = new \App\Services\FirebaseAuthService();
    $customToken = $firebaseAuth->createCustomToken($user);
    
    // Log token for debugging (remove in production)
    \Log::info('Custom token created', [
        'user_id' => $user->id,
        'token_length' => strlen($customToken->toString())
    ]);
    
    return $customToken;
} catch (\Exception $e) {
    \Log::error('Custom token creation failed', [
        'user_id' => $user->id,
        'error' => $e->getMessage()
    ]);
    throw $e;
}
```

#### 3. Performance Issues

**Problem**: Slow message loading
```javascript
// Implement message pagination
class OptimizedMessageLoader {
    constructor(conversationId) {
        this.conversationId = conversationId;
        this.pageSize = 20;
        this.lastKey = null;
        this.loading = false;
    }
    
    async loadMessages() {
        if (this.loading) return;
        this.loading = true;
        
        try {
            let query = firebase.database()
                .ref(`chats/messages/${this.conversationId}`)
                .orderByKey()
                .limitToLast(this.pageSize);
            
            if (this.lastKey) {
                query = query.endBefore(this.lastKey);
            }
            
            const snapshot = await query.once('value');
            const messages = [];
            
            snapshot.forEach(child => {
                messages.unshift(child.val());
            });
            
            if (messages.length > 0) {
                this.lastKey = messages[0].id;
            }
            
            return messages;
        } finally {
            this.loading = false;
        }
    }
}
```

**Problem**: Too many database reads
```javascript
// Implement local caching
class MessageCache {
    constructor() {
        this.cache = new Map();
        this.maxAge = 5 * 60 * 1000; // 5 minutes
    }
    
    set(key, data) {
        this.cache.set(key, {
            data: data,
            timestamp: Date.now()
        });
    }
    
    get(key) {
        const cached = this.cache.get(key);
        if (!cached) return null;
        
        if (Date.now() - cached.timestamp > this.maxAge) {
            this.cache.delete(key);
            return null;
        }
        
        return cached.data;
    }
}
```

#### 4. Security Rules Debugging

**Problem**: Rules not working as expected
```javascript
// Test security rules in Firebase Console
// Go to Database > Rules > Simulator

// Test read permission
{
  "path": "/chats/conversations/conv_123",
  "method": "read",
  "auth": {
    "uid": "user_123",
    "token": {
      "role": "sub_admin",
      "parent_sub_admin_id": null
    }
  }
}

// Test write permission
{
  "path": "/chats/messages/conv_123/msg_456",
  "method": "write",
  "auth": {
    "uid": "user_123"
  },
  "data": {
    "senderId": "user_123",
    "content": "Hello",
    "timestamp": 1640995200000
  }
}
```

#### 5. File Upload Issues

**Problem**: File upload fails
```javascript
// Debug file upload
async function debugFileUpload(file, conversationId) {
    console.log('File details:', {
        name: file.name,
        size: file.size,
        type: file.type
    });
    
    // Check file size
    const maxSize = 10 * 1024 * 1024; // 10MB
    if (file.size > maxSize) {
        throw new Error('File too large');
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        throw new Error('File type not allowed');
    }
    
    try {
        const storageRef = firebase.storage().ref(`chat_files/${conversationId}/${Date.now()}_${file.name}`);
        
        // Upload with progress tracking
        const uploadTask = storageRef.put(file);
        
        uploadTask.on('state_changed', 
            (snapshot) => {
                const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
                console.log('Upload progress:', progress + '%');
            },
            (error) => {
                console.error('Upload error:', error);
                throw error;
            },
            async () => {
                const downloadURL = await uploadTask.snapshot.ref.getDownloadURL();
                console.log('Upload successful:', downloadURL);
                return downloadURL;
            }
        );
        
    } catch (error) {
        console.error('File upload failed:', error);
        throw error;
    }
}
```

#### 6. Push Notification Issues

**Problem**: Notifications not received
```javascript
// Debug push notifications
async function debugPushNotifications() {
    // Check notification permission
    console.log('Notification permission:', Notification.permission);
    
    if (Notification.permission !== 'granted') {
        const permission = await Notification.requestPermission();
        console.log('Permission requested:', permission);
    }
    
    // Check service worker registration
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.getRegistration();
        console.log('Service worker registered:', !!registration);
        
        if (!registration) {
            await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            console.log('Service worker registered');
        }
    }
    
    // Get FCM token
    try {
        const messaging = firebase.messaging();
        const token = await messaging.getToken({
            vapidKey: 'your-vapid-key'
        });
        console.log('FCM token:', token);
        
        // Test token by sending to Laravel
        await fetch('/api/chat/test-notification', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ token: token })
        });
        
    } catch (error) {
        console.error('FCM token error:', error);
    }
}
```

#### 7. Monitoring và Logging

```javascript
// Enhanced error logging
class FirebaseErrorLogger {
    constructor() {
        this.errors = [];
        this.maxErrors = 100;
    }
    
    log(operation, error, context = {}) {
        const errorLog = {
            timestamp: new Date().toISOString(),
            operation: operation,
            error: {
                message: error.message,
                code: error.code,
                stack: error.stack
            },
            context: context,
            userAgent: navigator.userAgent,
            url: window.location.href
        };
        
        this.errors.push(errorLog);
        
        // Keep only recent errors
        if (this.errors.length > this.maxErrors) {
            this.errors.shift();
        }
        
        // Send to server for analysis
        this.sendToServer(errorLog);
        
        console.error('Firebase Error:', errorLog);
    }
    
    async sendToServer(errorLog) {
        try {
            await fetch('/api/chat/log-error', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(errorLog)
            });
        } catch (e) {
            console.error('Failed to send error log to server:', e);
        }
    }
    
    getErrors() {
        return this.errors;
    }
    
    clearErrors() {
        this.errors = [];
    }
}

// Global error logger
window.FirebaseErrorLogger = new FirebaseErrorLogger();

// Wrap Firebase operations with error logging
function withErrorLogging(operation, fn, context = {}) {
    try {
        const result = fn();
        if (result instanceof Promise) {
            return result.catch(error => {
                window.FirebaseErrorLogger.log(operation, error, context);
                throw error;
            });
        }
        return result;
    } catch (error) {
        window.FirebaseErrorLogger.log(operation, error, context);
        throw error;
    }
}

// Usage example
const sendMessage = (conversationId, content) => {
    return withErrorLogging('send_message', () => {
        return firebase.database()
            .ref(`chats/messages/${conversationId}`)
            .push({
                content: content,
                senderId: currentUser.id,
                timestamp: firebase.database.ServerValue.TIMESTAMP
            });
    }, { conversationId, contentLength: content.length });
};
```
```

## 5. TÍCH HỢP VỚI LARAVEL

### 5.1 Database Migrations

#### Migration cho Firebase Tokens
```php
// database/migrations/2024_01_01_000001_create_firebase_tokens_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('firebase_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('token', 255)->unique();
            $table->enum('device_type', ['web', 'android', 'ios']);
            $table->string('device_name', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index('last_used_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('firebase_tokens');
    }
};
```

#### Migration cho Chat Settings
```php
// database/migrations/2024_01_01_000002_create_chat_user_settings_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification settings
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sound_notifications')->default(true);
            $table->boolean('desktop_notifications')->default(true);
            
            // Privacy settings
            $table->boolean('show_online_status')->default(true);
            $table->boolean('show_last_seen')->default(true);
            $table->boolean('allow_direct_messages')->default(true);
            
            // Preferences
            $table->enum('theme', ['light', 'dark', 'auto'])->default('light');
            $table->string('language', 10)->default('vi');
            $table->string('timezone', 50)->default('Asia/Ho_Chi_Minh');
            $table->enum('date_format', ['DD/MM/YYYY', 'MM/DD/YYYY', 'YYYY-MM-DD'])->default('DD/MM/YYYY');
            $table->enum('time_format', ['12h', '24h'])->default('24h');
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_user_settings');
    }
};
```

#### Migration cho Chat Statistics
```php
// database/migrations/2024_01_01_000003_create_chat_statistics_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            
            $table->integer('messages_sent')->default(0);
            $table->integer('messages_received')->default(0);
            $table->integer('conversations_started')->default(0);
            $table->integer('files_uploaded')->default(0);
            $table->integer('total_chat_time')->default(0); // seconds
            $table->integer('online_duration')->default(0); // seconds
            
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
            $table->index('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_statistics');
    }
};
```

#### Migration cho Chat Rate Limiting
```php
// database/migrations/2024_01_01_000004_create_chat_rate_limits_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_rate_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action', 50); // 'send_message', 'upload_file', etc.
            $table->integer('count')->default(0);
            $table->timestamp('window_start');
            $table->timestamps();
            
            $table->unique(['user_id', 'action', 'window_start']);
            $table->index('window_start');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_rate_limits');
    }
};
```

### 5.2 Laravel Models

#### FirebaseToken Model
```php
// app/Models/FirebaseToken.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FirebaseToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'device_name',
        'is_active',
        'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsUsed()
    {
        $this->update(['last_used_at' => now()]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query, $days = 30)
    {
        return $query->where('last_used_at', '<', now()->subDays($days));
    }
}
```

#### ChatUserSettings Model
```php
// app/Models/ChatUserSettings.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatUserSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'sound_notifications',
        'desktop_notifications',
        'show_online_status',
        'show_last_seen',
        'allow_direct_messages',
        'theme',
        'language',
        'timezone',
        'date_format',
        'time_format'
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sound_notifications' => 'boolean',
        'desktop_notifications' => 'boolean',
        'show_online_status' => 'boolean',
        'show_last_seen' => 'boolean',
        'allow_direct_messages' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultSettings()
    {
        return [
            'email_notifications' => true,
            'push_notifications' => true,
            'sound_notifications' => true,
            'desktop_notifications' => true,
            'show_online_status' => true,
            'show_last_seen' => true,
            'allow_direct_messages' => true,
            'theme' => 'light',
            'language' => 'vi',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'date_format' => 'DD/MM/YYYY',
            'time_format' => '24h'
        ];
    }
}
```

### 5.3 Request Validation

#### Chat Request Validation
```php
// app/Http/Requests/Chat/CreateConversationRequest.php
<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateConversationRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'participants' => 'required|array|min:1|max:10',
            'participants.*' => [
                'required',
                'integer',
                'exists:users,id',
                'different:' . auth()->id(),
                function ($attribute, $value, $fail) {
                    if (!auth()->user()->canChatWith(\App\Models\User::find($value))) {
                        $fail('You are not allowed to chat with this user.');
                    }
                }
            ],
            'type' => ['required', Rule::in(['direct', 'group', 'support'])],
            'title' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500'
        ];
    }

    public function messages()
    {
        return [
            'participants.required' => 'At least one participant is required.',
            'participants.*.exists' => 'Selected user does not exist.',
            'participants.*.different' => 'You cannot add yourself as a participant.',
        ];
    }
}
```

#### Send Message Request
```php
// app/Http/Requests/Chat/SendMessageRequest.php
<?php

namespace App\Http\Requests\Chat;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'content' => 'required|string|max:5000',
            'type' => ['required', Rule::in(['text', 'image', 'file', 'system'])],
            'reply_to' => 'nullable|string|max:50',
            'metadata' => 'nullable|array',
            'metadata.file_url' => 'nullable|url',
            'metadata.file_name' => 'nullable|string|max:255',
            'metadata.file_size' => 'nullable|integer|max:10485760', // 10MB
            'metadata.file_type' => 'nullable|string|max:50'
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'Message content is required.',
            'content.max' => 'Message content cannot exceed 5000 characters.',
            'metadata.file_size.max' => 'File size cannot exceed 10MB.'
        ];
    }
}
```

### 5.4 Cấu Trúc API Endpoints

```php
// Chat Management Routes
Route::group(['prefix' => 'api/chat', 'middleware' => ['auth:sanctum']], function () {
    
    // Conversations
    Route::get('/conversations', 'ChatController@getConversations');
    Route::post('/conversations', 'ChatController@createConversation');
    Route::get('/conversations/{id}', 'ChatController@getConversation');
    Route::put('/conversations/{id}', 'ChatController@updateConversation');
    Route::delete('/conversations/{id}', 'ChatController@deleteConversation');
    
    // Messages
    Route::get('/conversations/{id}/messages', 'ChatController@getMessages');
    Route::post('/conversations/{id}/messages', 'ChatController@sendMessage');
    Route::put('/messages/{id}', 'ChatController@updateMessage');
    Route::delete('/messages/{id}', 'ChatController@deleteMessage');
    Route::post('/messages/{id}/read', 'ChatController@markAsRead');
    
    // File Upload
    Route::post('/upload', 'ChatController@uploadFile');
    Route::post('/upload/image', 'ChatController@uploadImage');
    
    // User Management
    Route::get('/users/available', 'ChatController@getAvailableUsers');
    Route::get('/users/online', 'ChatController@getOnlineUsers');
    Route::post('/users/presence', 'ChatController@updatePresence');
    
    // Notifications
    Route::get('/notifications', 'ChatController@getNotifications');
    Route::post('/notifications/{id}/read', 'ChatController@markNotificationAsRead');
    Route::post('/notifications/read-all', 'ChatController@markAllNotificationsAsRead');
    
    // Settings
    Route::get('/settings', 'ChatController@getSettings');
    Route::put('/settings', 'ChatController@updateSettings');
    
    // Admin only routes
    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/admin/conversations', 'ChatController@getAllConversations');
        Route::get('/admin/users', 'ChatController@getAllChatUsers');
        Route::get('/admin/statistics', 'ChatController@getChatStatistics');
        Route::post('/admin/broadcast', 'ChatController@broadcastMessage');
    });
    
    // Sub Admin routes
    Route::group(['middleware' => ['role:sub_admin']], function () {
        Route::get('/sub-admin/conversations', 'ChatController@getSubAdminConversations');
        Route::get('/sub-admin/users', 'ChatController@getSubAdminUsers');
        Route::get('/sub-admin/statistics', 'ChatController@getSubAdminStatistics');
    });
});
```

### 4.2 Database Tables Bổ Sung

```sql
-- Bảng lưu Firebase tokens cho push notifications
CREATE TABLE firebase_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    device_type ENUM('web', 'android', 'ios') NOT NULL,
    device_name VARCHAR(100) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_token (user_id, token),
    INDEX idx_user_active (user_id, is_active)
);

-- Bảng lưu chat settings của users
CREATE TABLE chat_user_settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    
    -- Notification settings
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    sound_notifications BOOLEAN DEFAULT TRUE,
    desktop_notifications BOOLEAN DEFAULT TRUE,
    
    -- Privacy settings
    show_online_status BOOLEAN DEFAULT TRUE,
    show_last_seen BOOLEAN DEFAULT TRUE,
    allow_direct_messages BOOLEAN DEFAULT TRUE,
    
    -- Preferences
    theme VARCHAR(20) DEFAULT 'light',
    language VARCHAR(10) DEFAULT 'vi',
    timezone VARCHAR(50) DEFAULT 'Asia/Ho_Chi_Minh',
    
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_settings (user_id)
);

-- Bảng thống kê chat (optional - có thể dùng Firebase Analytics)
CREATE TABLE chat_statistics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    
    messages_sent INT DEFAULT 0,
    messages_received INT DEFAULT 0,
    conversations_started INT DEFAULT 0,
    files_uploaded INT DEFAULT 0,
    total_chat_time INT DEFAULT 0, -- seconds
    
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_date (user_id, date),
    INDEX idx_date (date)
);
```

### 4.3 Laravel Models Cần Cập Nhật

```php
// User Model - thêm relationships
class User extends Authenticatable
{
    // Existing code...
    
    // Chat relationships
    public function firebaseTokens()
    {
        return $this->hasMany(FirebaseToken::class);
    }
    
    public function chatSettings()
    {
        return $this->hasOne(ChatUserSettings::class);
    }
    
    public function chatStatistics()
    {
        return $this->hasMany(ChatStatistics::class);
    }
    
    // Sub Admin relationships for chat
    public function managedUsers()
    {
        return $this->hasMany(User::class, 'parent_sub_admin_id');
    }
    
    public function subAdminManager()
    {
        return $this->belongsTo(User::class, 'parent_sub_admin_id');
    }
    
    // Chat helper methods
    public function canChatWith(User $user)
    {
        // Admin có thể chat với tất cả
        if ($this->role === 'admin') {
            return true;
        }
        
        // User có thể chat với admin và sub admin quản lý mình
        if ($this->role === 'user') {
            return $user->role === 'admin' || 
                   ($user->role === 'sub_admin' && $this->parent_sub_admin_id === $user->id);
        }
        
        // Sub Admin có thể chat với admin và users thuộc quyền
        if ($this->role === 'sub_admin') {
            return $user->role === 'admin' || 
                   ($user->role === 'user' && $user->parent_sub_admin_id === $this->id);
        }
        
        return false;
    }
    
    public function getAvailableChatUsers()
    {
        if ($this->role === 'admin') {
            return User::where('id', '!=', $this->id)
                      ->where('status', 'active')
                      ->get();
        }
        
        if ($this->role === 'sub_admin') {
            return User::where(function($query) {
                $query->where('role', 'admin')
                      ->orWhere('parent_sub_admin_id', $this->id);
            })
            ->where('id', '!=', $this->id)
            ->where('status', 'active')
            ->get();
        }
        
        if ($this->role === 'user') {
            $query = User::where('role', 'admin');
            
            if ($this->parent_sub_admin_id) {
                $query->orWhere('id', $this->parent_sub_admin_id);
            }
            
            return $query->where('status', 'active')->get();
        }
        
        return collect();
    }
} 
```

## 5. FRONTEND IMPLEMENTATION

### 5.1 Cấu Trúc Blade Templates và JavaScript

```
resources/views/chat/
├── index.blade.php             // Main chat page layout
├── partials/
│   ├── conversation-list.blade.php    // Conversations sidebar
│   ├── chat-window.blade.php          // Main chat interface
│   ├── message-input.blade.php        // Message input form
│   ├── user-list.blade.php            // Available users modal
│   ├── file-upload.blade.php          // File upload modal
│   └── chat-settings.blade.php        // Settings modal

resources/js/chat/
├── firebase-config.js          // Firebase configuration
├── chat-service.js             // Main chat service class
├── message-handler.js          // Message handling logic
├── conversation-manager.js     // Conversation management
├── file-upload-handler.js      // File upload functionality
├── notification-manager.js     // Push notifications
├── presence-manager.js         // Online/offline status
├── ui-manager.js               // DOM manipulation utilities
└── chat-app.js                 // Main application entry point

public/js/
└── chat-bundle.js              // Compiled chat JavaScript
```

### 5.2 Firebase Configuration

```javascript
// resources/js/chat/firebase-config.js
// Firebase configuration sử dụng CDN hoặc npm
const firebaseConfig = {
    apiKey: "{{ config('firebase.api_key') }}",
    authDomain: "{{ config('firebase.auth_domain') }}",
    databaseURL: "{{ config('firebase.database_url') }}",
    projectId: "{{ config('firebase.project_id') }}",
    storageBucket: "{{ config('firebase.storage_bucket') }}",
    messagingSenderId: "{{ config('firebase.messaging_sender_id') }}",
    appId: "{{ config('firebase.app_id') }}"
};

// Initialize Firebase
const app = firebase.initializeApp(firebaseConfig);
const database = firebase.database();
const storage = firebase.storage();
const messaging = firebase.messaging();

// Export for global use
window.firebaseApp = app;
window.firebaseDatabase = database;
window.firebaseStorage = storage;
window.firebaseMessaging = messaging;
```

### 5.3 Main Blade Template

```blade
{{-- resources/views/chat/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Chat System')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endpush

@section('content')
<div class="chat-container" id="chatApp">
    <div class="chat-sidebar">
        @include('chat.partials.conversation-list')
    </div>
    
    <div class="chat-main">
        @include('chat.partials.chat-window')
    </div>
    
    <div class="chat-users-panel" id="usersPanel" style="display: none;">
        @include('chat.partials.user-list')
    </div>
</div>

{{-- Modals --}}
@include('chat.partials.file-upload')
@include('chat.partials.chat-settings')

{{-- Pass user data to JavaScript --}}
<script>
    window.currentUser = @json(auth()->user());
    window.chatConfig = {
        maxFileSize: {{ config('chat.max_file_size', 10485760) }},
        allowedFileTypes: @json(config('chat.allowed_file_types', ['jpg', 'jpeg', 'png', 'gif', 'pdf'])),
        apiBaseUrl: '{{ url('/api/chat') }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>
@endpush

@push('scripts')
{{-- Firebase SDK --}}
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-database-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-storage-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js"></script>

{{-- Chat JavaScript --}}
<script src="{{ asset('js/chat/firebase-config.js') }}"></script>
<script src="{{ asset('js/chat/chat-service.js') }}"></script>
<script src="{{ asset('js/chat/ui-manager.js') }}"></script>
<script src="{{ asset('js/chat/chat-app.js') }}"></script>
@endpush
@endsection
```

### 5.4 Chat Service Class (Vanilla JavaScript)

```javascript
// resources/js/chat/chat-service.js
class ChatService {
    constructor() {
        this.currentUser = null;
        this.listeners = new Map();
        this.database = null;
        this.storage = null;
        this.messaging = null;
    }
    
    // Initialize chat service
    init(user) {
        this.currentUser = user;
        this.database = window.firebaseDatabase;
        this.storage = window.firebaseStorage;
        this.messaging = window.firebaseMessaging;
        
        this.updatePresence('online');
        this.setupPresenceListener();
        this.setupNotifications();
    }
    
    // Conversation methods
    async createConversation(participants, metadata = {}) {
        const conversationRef = this.database.ref('chats/conversations').push();
        const conversationData = {
            id: conversationRef.key,
            type: 'direct',
            participants: this.formatParticipants(participants),
            metadata: {
                createdAt: firebase.database.ServerValue.TIMESTAMP,
                createdBy: this.currentUser.id,
                status: 'active',
                ...metadata
            },
            unreadCount: {}
        };
        
        await conversationRef.set(conversationData);
        return conversationRef.key;
    }
    
    // Message methods
    async sendMessage(conversationId, content, type = 'text', metadata = {}) {
        const messageRef = this.database.ref(`chats/messages/${conversationId}`).push();
        const messageData = {
            id: messageRef.key,
            conversationId,
            senderId: this.currentUser.id,
            senderName: this.currentUser.name,
            senderRole: this.currentUser.role,
            senderAvatar: this.currentUser.photo,
            content,
            type,
            timestamp: firebase.database.ServerValue.TIMESTAMP,
            status: 'sent',
            readBy: {},
            metadata: {
                isEdited: false,
                isDeleted: false,
                ...metadata
            }
        };
        
        await messageRef.set(messageData);
        await this.updateConversationLastMessage(conversationId, messageData);
        await this.updateUnreadCount(conversationId);
        
        // Send push notification
        this.sendPushNotification(conversationId, messageData);
        
        return messageRef.key;
    }
    
    // Real-time listeners
    listenToConversations(callback) {
        const conversationsRef = this.database.ref('chats/conversations');
        
        const listener = conversationsRef.on('value', (snapshot) => {
            const conversations = [];
            snapshot.forEach((child) => {
                const conversation = child.val();
                if (this.canAccessConversation(conversation)) {
                    conversations.push(conversation);
                }
            });
            callback(conversations);
        });
        
        this.listeners.set('conversations', { ref: conversationsRef, type: 'value' });
        return listener;
    }
    
    listenToMessages(conversationId, callback) {
        const messagesRef = this.database.ref(`chats/messages/${conversationId}`)
                                        .orderByChild('timestamp')
                                        .limitToLast(50);
        
        const listener = messagesRef.on('value', (snapshot) => {
            const messages = [];
            snapshot.forEach((child) => {
                messages.push(child.val());
            });
            callback(messages);
        });
        
        this.listeners.set(`messages_${conversationId}`, { 
            ref: messagesRef, 
            type: 'value' 
        });
        return listener;
    }
    
    // File upload
    async uploadFile(file, conversationId) {
        const fileName = `chat_files/${conversationId}/${Date.now()}_${file.name}`;
        const storageRef = this.storage.ref(fileName);
        
        try {
            const snapshot = await storageRef.put(file);
            const downloadURL = await snapshot.ref.getDownloadURL();
            
            return {
                url: downloadURL,
                name: file.name,
                size: file.size,
                type: file.type
            };
        } catch (error) {
            console.error('File upload failed:', error);
            throw error;
        }
    }
    
    // Presence management
    async updatePresence(status) {
        if (!this.currentUser) return;
        
        const presenceRef = this.database.ref(`chats/userPresence/${this.currentUser.id}`);
        await presenceRef.set({
            status: status,
            lastSeen: firebase.database.ServerValue.TIMESTAMP,
            deviceInfo: {
                platform: 'web',
                userAgent: navigator.userAgent
            }
        });
    }
    
    setupPresenceListener() {
        if (!this.currentUser) return;
        
        const presenceRef = this.database.ref(`chats/userPresence/${this.currentUser.id}`);
        
        // Set offline when disconnected
        presenceRef.onDisconnect().set({
            status: 'offline',
            lastSeen: firebase.database.ServerValue.TIMESTAMP
        });
        
        // Update presence every 30 seconds
        setInterval(() => {
            this.updatePresence('online');
        }, 30000);
    }
    
    // Push notifications
    async setupNotifications() {
        if (!this.messaging) return;
        
        try {
            const permission = await Notification.requestPermission();
            if (permission === 'granted') {
                const token = await this.messaging.getToken({
                    vapidKey: '{{ config("firebase.vapid_key") }}'
                });
                
                // Save token to Laravel backend
                await this.saveFirebaseToken(token);
                
                // Listen for foreground messages
                this.messaging.onMessage((payload) => {
                    this.showNotification(payload);
                });
            }
        } catch (error) {
            console.error('Notification setup failed:', error);
        }
    }
    
    async saveFirebaseToken(token) {
        try {
            await fetch('/api/chat/firebase-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.chatConfig.csrfToken,
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({
                    token: token,
                    device_type: 'web'
                })
            });
        } catch (error) {
            console.error('Failed to save Firebase token:', error);
        }
    }
    
    // Permission checking
    canAccessConversation(conversation) {
        const userKey = `user_${this.currentUser.id}`;
        
        // Admin có thể truy cập tất cả
        if (this.currentUser.role === 'admin') {
            return true;
        }
        
        // Kiểm tra user có trong participants không
        return conversation.participants && conversation.participants[userKey];
    }
    
    // Utility methods
    formatParticipants(users) {
        const participants = {};
        users.forEach(user => {
            participants[`user_${user.id}`] = {
                id: user.id,
                role: user.role,
                name: user.name,
                avatar: user.photo,
                parentSubAdminId: user.parent_sub_admin_id,
                joinedAt: firebase.database.ServerValue.TIMESTAMP
            };
        });
        return participants;
    }
    
    async updateConversationLastMessage(conversationId, messageData) {
        const conversationRef = this.database.ref(`chats/conversations/${conversationId}/lastMessage`);
        await conversationRef.set({
            id: messageData.id,
            senderId: messageData.senderId,
            senderName: messageData.senderName,
            content: messageData.content,
            type: messageData.type,
            timestamp: messageData.timestamp
        });
    }
    
    async updateUnreadCount(conversationId) {
        // Implementation for updating unread count
        // This would increment unread count for other participants
    }
    
    getAuthToken() {
        // Get auth token from Laravel session or localStorage
        return document.querySelector('meta[name="api-token"]')?.getAttribute('content') || '';
    }
    
    showNotification(payload) {
        if (Notification.permission === 'granted') {
            new Notification(payload.notification.title, {
                body: payload.notification.body,
                icon: '/images/chat-icon.png',
                tag: 'chat-notification'
            });
        }
    }
    
    // Cleanup
    destroy() {
        this.updatePresence('offline');
        
        this.listeners.forEach((listener, key) => {
            listener.ref.off(listener.type);
        });
        this.listeners.clear();
    }
}

// Create global instance
window.ChatService = new ChatService();
```

### 5.5 UI Manager (DOM Manipulation)

```javascript
// resources/js/chat/ui-manager.js
class UIManager {
    constructor() {
        this.currentConversationId = null;
        this.messageContainer = null;
        this.conversationList = null;
        this.messageInput = null;
        this.fileInput = null;
    }
    
    init() {
        this.messageContainer = document.getElementById('messageContainer');
        this.conversationList = document.getElementById('conversationList');
        this.messageInput = document.getElementById('messageInput');
        this.fileInput = document.getElementById('fileInput');
        
        this.setupEventListeners();
        this.setupFileUpload();
    }
    
    setupEventListeners() {
        // Send message on Enter
        if (this.messageInput) {
            this.messageInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    this.sendMessage();
                }
            });
        }
        
        // Send button click
        const sendButton = document.getElementById('sendButton');
        if (sendButton) {
            sendButton.addEventListener('click', () => this.sendMessage());
        }
        
        // File upload button
        const fileButton = document.getElementById('fileButton');
        if (fileButton) {
            fileButton.addEventListener('click', () => this.fileInput?.click());
        }
        
        // Conversation clicks
        if (this.conversationList) {
            this.conversationList.addEventListener('click', (e) => {
                const conversationItem = e.target.closest('.conversation-item');
                if (conversationItem) {
                    const conversationId = conversationItem.dataset.conversationId;
                    this.selectConversation(conversationId);
                }
            });
        }
    }
    
    setupFileUpload() {
        if (this.fileInput) {
            this.fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.handleFileUpload(file);
                }
            });
        }
    }
    
    async sendMessage() {
        const content = this.messageInput.value.trim();
        if (!content || !this.currentConversationId) return;
        
        try {
            this.messageInput.value = '';
            this.showTypingIndicator(false);
            
            await window.ChatService.sendMessage(
                this.currentConversationId, 
                content, 
                'text'
            );
            
        } catch (error) {
            console.error('Failed to send message:', error);
            this.showError('Failed to send message');
        }
    }
    
    async handleFileUpload(file) {
        if (!this.currentConversationId) return;
        
        // Validate file
        const maxSize = window.chatConfig.maxFileSize;
        const allowedTypes = window.chatConfig.allowedFileTypes;
        
        if (file.size > maxSize) {
            this.showError('File size too large');
            return;
        }
        
        const fileExtension = file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExtension)) {
            this.showError('File type not allowed');
            return;
        }
        
        try {
            this.showUploadProgress(true);
            
            const fileData = await window.ChatService.uploadFile(file, this.currentConversationId);
            
            await window.ChatService.sendMessage(
                this.currentConversationId,
                fileData.name,
                file.type.startsWith('image/') ? 'image' : 'file',
                {
                    fileUrl: fileData.url,
                    fileName: fileData.name,
                    fileSize: fileData.size,
                    fileType: fileData.type
                }
            );
            
            this.showUploadProgress(false);
            
        } catch (error) {
            console.error('File upload failed:', error);
            this.showError('File upload failed');
            this.showUploadProgress(false);
        }
    }
    
    selectConversation(conversationId) {
        this.currentConversationId = conversationId;
        
        // Update UI
        document.querySelectorAll('.conversation-item').forEach(item => {
            item.classList.remove('active');
        });
        
        const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('active');
        }
        
        // Load messages
        this.loadMessages(conversationId);
        
        // Mark as read
        this.markConversationAsRead(conversationId);
    }
    
    loadMessages(conversationId) {
        // Clear current messages
        if (this.messageContainer) {
            this.messageContainer.innerHTML = '<div class="loading">Loading messages...</div>';
        }
        
        // Listen to messages
        window.ChatService.listenToMessages(conversationId, (messages) => {
            this.renderMessages(messages);
        });
    }
    
    renderMessages(messages) {
        if (!this.messageContainer) return;
        
        this.messageContainer.innerHTML = '';
        
        messages.forEach(message => {
            const messageElement = this.createMessageElement(message);
            this.messageContainer.appendChild(messageElement);
        });
        
        // Scroll to bottom
        this.scrollToBottom();
    }
    
    createMessageElement(message) {
        const div = document.createElement('div');
        div.className = `message ${message.senderId === window.currentUser.id ? 'own' : 'other'}`;
        
        const time = new Date(message.timestamp).toLocaleTimeString();
        
        let content = '';
        if (message.type === 'text') {
            content = `<div class="message-content">${this.escapeHtml(message.content)}</div>`;
        } else if (message.type === 'image') {
            content = `
                <div class="message-content">
                    <img src="${message.metadata.fileUrl}" alt="${message.metadata.fileName}" 
                         class="message-image" onclick="this.openImageModal('${message.metadata.fileUrl}')">
                </div>
            `;
        } else if (message.type === 'file') {
            content = `
                <div class="message-content">
                    <div class="file-message">
                        <i class="fas fa-file"></i>
                        <a href="${message.metadata.fileUrl}" target="_blank">${message.metadata.fileName}</a>
                        <span class="file-size">(${this.formatFileSize(message.metadata.fileSize)})</span>
                    </div>
                </div>
            `;
        }
        
        div.innerHTML = `
            <div class="message-header">
                <span class="sender-name">${message.senderName}</span>
                <span class="message-time">${time}</span>
            </div>
            ${content}
        `;
        
        return div;
    }
    
    renderConversations(conversations) {
        if (!this.conversationList) return;
        
        this.conversationList.innerHTML = '';
        
        conversations.forEach(conversation => {
            const conversationElement = this.createConversationElement(conversation);
            this.conversationList.appendChild(conversationElement);
        });
    }
    
    createConversationElement(conversation) {
        const div = document.createElement('div');
        div.className = 'conversation-item';
        div.dataset.conversationId = conversation.id;
        
        // Get other participant info
        const otherParticipant = this.getOtherParticipant(conversation.participants);
        const lastMessage = conversation.lastMessage;
        const unreadCount = conversation.unreadCount?.[`user_${window.currentUser.id}`] || 0;
        
        div.innerHTML = `
            <div class="conversation-avatar">
                <img src="${otherParticipant.avatar || '/images/default-avatar.png'}" alt="${otherParticipant.name}">
                <span class="online-status ${this.getOnlineStatus(otherParticipant.id)}"></span>
            </div>
            <div class="conversation-info">
                <div class="conversation-name">${otherParticipant.name}</div>
                <div class="conversation-last-message">
                    ${lastMessage ? this.truncateText(lastMessage.content, 50) : 'No messages yet'}
                </div>
            </div>
            <div class="conversation-meta">
                <div class="conversation-time">
                    ${lastMessage ? this.formatTime(lastMessage.timestamp) : ''}
                </div>
                ${unreadCount > 0 ? `<div class="unread-badge">${unreadCount}</div>` : ''}
            </div>
        `;
        
        return div;
    }
    
    // Utility methods
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    truncateText(text, length) {
        return text.length > length ? text.substring(0, length) + '...' : text;
    }
    
    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'now';
        if (diff < 3600000) return Math.floor(diff / 60000) + 'm';
        if (diff < 86400000) return Math.floor(diff / 3600000) + 'h';
        return date.toLocaleDateString();
    }
    
    getOtherParticipant(participants) {
        for (const key in participants) {
            if (participants[key].id !== window.currentUser.id) {
                return participants[key];
            }
        }
        return null;
    }
    
    getOnlineStatus(userId) {
        // This would be updated by presence listener
        return 'offline'; // placeholder
    }
    
    scrollToBottom() {
        if (this.messageContainer) {
            this.messageContainer.scrollTop = this.messageContainer.scrollHeight;
        }
    }
    
    showError(message) {
        // Show error notification
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-notification';
        errorDiv.textContent = message;
        document.body.appendChild(errorDiv);
        
        setTimeout(() => {
            errorDiv.remove();
        }, 3000);
    }
    
    showUploadProgress(show) {
        const progressElement = document.getElementById('uploadProgress');
        if (progressElement) {
            progressElement.style.display = show ? 'block' : 'none';
        }
    }
    
    showTypingIndicator(show) {
        const typingElement = document.getElementById('typingIndicator');
        if (typingElement) {
            typingElement.style.display = show ? 'block' : 'none';
        }
    }
    
    markConversationAsRead(conversationId) {
        // Mark conversation as read
        // This would update Firebase and remove unread badge
    }
}

// Create global instance
window.UIManager = new UIManager();
```

### 5.6 Main Chat Application

```javascript
// resources/js/chat/chat-app.js
class ChatApp {
    constructor() {
        this.isInitialized = false;
    }
    
    async init() {
        if (this.isInitialized) return;
        
        try {
            // Initialize services
            window.ChatService.init(window.currentUser);
            window.UIManager.init();
            
            // Load initial data
            await this.loadConversations();
            
            // Setup real-time listeners
            this.setupRealtimeListeners();
            
            this.isInitialized = true;
            console.log('Chat app initialized successfully');
            
        } catch (error) {
            console.error('Failed to initialize chat app:', error);
        }
    }
    
    async loadConversations() {
        window.ChatService.listenToConversations((conversations) => {
            window.UIManager.renderConversations(conversations);
        });
    }
    
    setupRealtimeListeners() {
        // Listen for presence updates
        const presenceRef = window.firebaseDatabase.ref('chats/userPresence');
        presenceRef.on('value', (snapshot) => {
            this.updateOnlineStatus(snapshot.val());
        });
        
        // Listen for notifications
        const notificationsRef = window.firebaseDatabase.ref(`chats/notifications/${window.currentUser.id}`);
        notificationsRef.on('child_added', (snapshot) => {
            const notification = snapshot.val();
            this.handleNotification(notification);
        });
    }
    
    updateOnlineStatus(presenceData) {
        // Update online status indicators in UI
        for (const userId in presenceData) {
            const status = presenceData[userId].status;
            const statusElements = document.querySelectorAll(`[data-user-id="${userId}"] .online-status`);
            statusElements.forEach(element => {
                element.className = `online-status ${status}`;
            });
        }
    }
    
    handleNotification(notification) {
        if (notification.type === 'new_message') {
            // Update conversation list
            this.updateConversationBadge(notification.conversationId);
            
            // Show browser notification if not in focus
            if (document.hidden) {
                this.showBrowserNotification(notification);
            }
        }
    }
    
    updateConversationBadge(conversationId) {
        const conversationElement = document.querySelector(`[data-conversation-id="${conversationId}"]`);
        if (conversationElement) {
            const badge = conversationElement.querySelector('.unread-badge');
            if (badge) {
                const count = parseInt(badge.textContent) + 1;
                badge.textContent = count;
            } else {
                const metaDiv = conversationElement.querySelector('.conversation-meta');
                const newBadge = document.createElement('div');
                newBadge.className = 'unread-badge';
                newBadge.textContent = '1';
                metaDiv.appendChild(newBadge);
            }
        }
    }
    
    showBrowserNotification(notification) {
        if (Notification.permission === 'granted') {
            new Notification(notification.title, {
                body: notification.body,
                icon: '/images/chat-icon.png',
                tag: 'chat-notification',
                data: { conversationId: notification.conversationId }
            });
        }
    }
    
    destroy() {
        window.ChatService.destroy();
        this.isInitialized = false;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.ChatApp = new ChatApp();
    window.ChatApp.init();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.ChatApp) {
        window.ChatApp.destroy();
    }
});
```

### 5.7 CSS Styling cho Chat Interface

```css
/* public/css/chat.css */

/* Chat Container */
.chat-container {
    display: flex;
    height: 100vh;
    background: #f5f5f5;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Sidebar */
.chat-sidebar {
    width: 320px;
    background: #fff;
    border-right: 1px solid #e1e5e9;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e1e5e9;
    background: #fff;
}

.sidebar-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
}

.search-box {
    margin-top: 15px;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 10px 40px 10px 15px;
    border: 1px solid #e1e5e9;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s;
}

.search-box input:focus {
    border-color: #007bff;
}

.search-box .search-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

/* Conversation List */
.conversation-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px 0;
}

.conversation-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    cursor: pointer;
    transition: background-color 0.2s;
    border-bottom: 1px solid #f8f9fa;
}

.conversation-item:hover {
    background-color: #f8f9fa;
}

.conversation-item.active {
    background-color: #e3f2fd;
    border-right: 3px solid #007bff;
}

.conversation-avatar {
    position: relative;
    margin-right: 15px;
}

.conversation-avatar img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.online-status {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.online-status.online {
    background-color: #28a745;
}

.online-status.offline {
    background-color: #6c757d;
}

.online-status.away {
    background-color: #ffc107;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    font-weight: 600;
    font-size: 14px;
    color: #1a1a1a;
    margin-bottom: 4px;
}

.conversation-last-message {
    font-size: 13px;
    color: #6c757d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.conversation-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    margin-left: 10px;
}

.conversation-time {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 5px;
}

.unread-badge {
    background-color: #007bff;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: 600;
    min-width: 18px;
    text-align: center;
}

/* Main Chat Area */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #fff;
}

.chat-header {
    padding: 20px;
    border-bottom: 1px solid #e1e5e9;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-header-info {
    display: flex;
    align-items: center;
}

.chat-header-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
    object-fit: cover;
}

.chat-header-details h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1a1a1a;
}

.chat-header-details .status {
    font-size: 12px;
    color: #6c757d;
    margin-top: 2px;
}

.chat-header-actions {
    display: flex;
    gap: 10px;
}

.btn-icon {
    width: 36px;
    height: 36px;
    border: none;
    background: #f8f9fa;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-icon:hover {
    background-color: #e9ecef;
}

/* Messages Container */
.messages-container {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
}

.message {
    display: flex;
    margin-bottom: 20px;
    max-width: 70%;
}

.message.own {
    margin-left: auto;
    flex-direction: row-reverse;
}

.message-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    margin: 0 10px;
    object-fit: cover;
}

.message.own .message-avatar {
    margin: 0 0 0 10px;
}

.message-bubble {
    background: #fff;
    border-radius: 18px;
    padding: 12px 16px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    position: relative;
}

.message.own .message-bubble {
    background: #007bff;
    color: white;
}

.message-header {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
    font-size: 12px;
}

.sender-name {
    font-weight: 600;
    margin-right: 8px;
}

.message.own .sender-name {
    color: rgba(255, 255, 255, 0.9);
}

.message-time {
    color: #6c757d;
    font-size: 11px;
}

.message.own .message-time {
    color: rgba(255, 255, 255, 0.7);
}

.message-content {
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
}

.message-image {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.message-image:hover {
    transform: scale(1.02);
}

.file-message {
    display: flex;
    align-items: center;
    padding: 10px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 8px;
    margin-top: 5px;
}

.message.own .file-message {
    background: rgba(255, 255, 255, 0.2);
}

.file-message i {
    margin-right: 10px;
    font-size: 18px;
}

.file-message a {
    color: inherit;
    text-decoration: none;
    font-weight: 500;
}

.file-size {
    font-size: 12px;
    opacity: 0.7;
    margin-left: 5px;
}

/* Message Input */
.message-input-container {
    padding: 20px;
    background: #fff;
    border-top: 1px solid #e1e5e9;
}

.message-input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    background: #f8f9fa;
    border-radius: 25px;
    padding: 8px;
}

.message-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 8px 15px;
    font-size: 14px;
    resize: none;
    outline: none;
    max-height: 100px;
    min-height: 20px;
}

.input-actions {
    display: flex;
    gap: 5px;
}

.btn-input {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6c757d;
    transition: all 0.2s;
}

.btn-input:hover {
    background: #e9ecef;
    color: #007bff;
}

.btn-send {
    background: #007bff;
    color: white;
}

.btn-send:hover {
    background: #0056b3;
}

.btn-send:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

/* Responsive Design */
@media (max-width: 768px) {
    .chat-container {
        flex-direction: column;
    }
    
    .chat-sidebar {
        width: 100%;
        height: 50vh;
    }
    
    .message {
        max-width: 85%;
    }
}

/* Dark Theme */
.chat-container.dark {
    background: #1a1a1a;
    color: #fff;
}

.dark .chat-sidebar,
.dark .chat-main,
.dark .chat-header,
.dark .message-input-container {
    background: #2d2d2d;
    border-color: #404040;
}

.dark .message-bubble {
    background: #404040;
    color: #fff;
}
```

### 5.8 Service Worker cho PWA Support

```javascript
// public/firebase-messaging-sw.js
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

// Firebase configuration
firebase.initializeApp({
    apiKey: "your-api-key",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.appspot.com",
    messagingSenderId: "123456789",
    appId: "your-app-id"
});

const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage((payload) => {
    console.log('Received background message:', payload);

    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/chat-icon.png',
        badge: '/images/badge-icon.png',
        tag: 'chat-notification',
        data: payload.data,
        actions: [
            {
                action: 'reply',
                title: 'Quick Reply',
                icon: '/images/reply-icon.png'
            },
            {
                action: 'view',
                title: 'View Chat',
                icon: '/images/view-icon.png'
            }
        ],
        requireInteraction: true,
        vibrate: [200, 100, 200]
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    const action = event.action;
    const data = event.notification.data;
    
    if (action === 'reply') {
        event.waitUntil(
            clients.openWindow(`/chat?reply=${data.conversationId}`)
        );
    } else if (action === 'view' || !action) {
        event.waitUntil(
            clients.openWindow(`/chat?conversation=${data.conversationId}`)
        );
    }
});

// Cache management for offline support
const CACHE_NAME = 'chat-cache-v1';
const urlsToCache = [
    '/chat',
    '/css/chat.css',
    '/js/chat/chat-app.js',
    '/images/default-avatar.png',
    '/images/chat-icon.png'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') return;
    
    event.respondWith(
        caches.match(event.request)
            .then((response) => {
                return response || fetch(event.request);
            })
    );
});

// Background sync for offline messages
self.addEventListener('sync', (event) => {
    if (event.tag === 'background-sync-messages') {
        event.waitUntil(syncMessages());
    }
});

async function syncMessages() {
    try {
        const pendingMessages = await getPendingMessages();
        
        for (const message of pendingMessages) {
            try {
                await sendMessage(message);
                await removePendingMessage(message.id);
            } catch (error) {
                console.error('Failed to sync message:', error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}
```

### 5.9 Rate Limiting Implementation

```php
// app/Http/Middleware/ChatRateLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ChatRateLimit;
use Carbon\Carbon;

class ChatRateLimit
{
    public function handle(Request $request, Closure $next, $action = 'send_message', $limit = 60, $window = 60)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $windowStart = Carbon::now()->startOfMinute();
        
        $rateLimit = ChatRateLimit::firstOrCreate([
            'user_id' => $user->id,
            'action' => $action,
            'window_start' => $windowStart
        ], ['count' => 0]);

        if ($rateLimit->count >= $limit) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $windowStart->addSeconds($window)->diffInSeconds(now())
            ], 429);
        }

        $rateLimit->increment('count');

        return $next($request);
    }
}
```

## 6. PUSH NOTIFICATIONS

### 6.1 Firebase Cloud Messaging Setup

```javascript
// firebase-messaging-sw.js (Service Worker)
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "your-api-key",
    authDomain: "your-project.firebaseapp.com",
    projectId: "your-project-id",
    storageBucket: "your-project.appspot.com",
    messagingSenderId: "123456789",
    appId: "your-app-id"
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: '/images/chat-icon.png',
        badge: '/images/badge-icon.png',
        tag: 'chat-notification',
        data: payload.data,
        actions: [
            {
                action: 'reply',
                title: 'Reply',
                icon: '/images/reply-icon.png'
            },
            {
                action: 'view',
                title: 'View Chat',
                icon: '/images/view-icon.png'
            }
        ]
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
```

### 6.2 Laravel Push Notification Service

```php
// app/Services/FirebasePushService.php
<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use App\Models\FirebaseToken;

class FirebasePushService
{
    private $messaging;
    
    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
            
        $this->messaging = $factory->createMessaging();
    }
    
    public function sendChatNotification($userId, $title, $body, $data = [])
    {
        $tokens = FirebaseToken::where('user_id', $userId)
                              ->where('is_active', true)
                              ->pluck('token')
                              ->toArray();
        
        if (empty($tokens)) {
            return false;
        }
        
        $notification = Notification::create($title, $body);
        
        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);
        
        try {
            $this->messaging->sendMulticast($message, $tokens);
            return true;
        } catch (\Exception $e) {
            \Log::error('Firebase push notification failed: ' . $e->getMessage());
            return false;
        }
    }
    
    public function sendToMultipleUsers($userIds, $title, $body, $data = [])
    {
        foreach ($userIds as $userId) {
            $this->sendChatNotification($userId, $title, $body, $data);
        }
    }
}
```

## 7. SECURITY VÀ PERFORMANCE

### 7.1 Security Best Practices

```javascript
// Chat Security Rules
{
  "rules": {
    "chats": {
      // Chỉ authenticated users mới truy cập được
      ".read": "auth != null",
      ".write": "auth != null",
      
      "conversations": {
        "$conversationId": {
          // Validate conversation data structure
          ".validate": "newData.hasChildren(['id', 'participants', 'metadata'])",
          
          "participants": {
            "$participantKey": {
              // Chỉ admin hoặc chính user đó mới có thể thêm/sửa participant
              ".write": "auth != null && (
                root.child('users').child(auth.uid).child('role').val() == 'admin' ||
                $participantKey == 'user_' + auth.uid
              )"
            }
          },
          
          "metadata": {
            // Chỉ creator hoặc admin mới sửa được metadata
            ".write": "auth != null && (
              data.child('createdBy').val() == auth.uid ||
              root.child('users').child(auth.uid).child('role').val() == 'admin'
            )"
          }
        }
      },
      
      "messages": {
        "$conversationId": {
          "$messageId": {
            // Validate message structure
            ".validate": "newData.hasChildren(['senderId', 'content', 'timestamp'])",
            
            // Chỉ sender hoặc admin mới có thể sửa/xóa message
            ".write": "auth != null && (
              !data.exists() && newData.child('senderId').val() == auth.uid ||
              data.child('senderId').val() == auth.uid ||
              root.child('users').child(auth.uid).child('role').val() == 'admin'
            )",
            
            "content": {
              // Giới hạn độ dài message
              ".validate": "newData.isString() && newData.val().length <= 5000"
            },
            
            "senderId": {
              // Sender ID phải match với auth user
              ".validate": "newData.val() == auth.uid"
            }
          }
        }
      }
    }
  }
}
```

### 7.2 Performance Optimization (Vanilla JavaScript)

```javascript
// resources/js/chat/performance-optimizations.js

// 1. Message Pagination
class MessagePagination {
    constructor(conversationId, pageSize = 20) {
        this.conversationId = conversationId;
        this.pageSize = pageSize;
        this.lastMessageKey = null;
        this.messages = [];
        this.database = window.firebaseDatabase;
    }
    
    async loadMessages() {
        let query = this.database.ref(`chats/messages/${this.conversationId}`)
                                 .orderByKey()
                                 .limitToLast(this.pageSize);
        
        if (this.lastMessageKey) {
            query = query.endBefore(this.lastMessageKey);
        }
        
        try {
            const snapshot = await query.once('value');
            const newMessages = [];
            
            snapshot.forEach(child => {
                newMessages.unshift(child.val());
            });
            
            if (newMessages.length > 0) {
                this.lastMessageKey = newMessages[0].id;
                this.messages = [...newMessages, ...this.messages];
            }
            
            return newMessages;
        } catch (error) {
            console.error('Failed to load messages:', error);
            return [];
        }
    }
    
    hasMore() {
        return this.lastMessageKey !== null;
    }
}

// 2. Connection Manager
class ConnectionManager {
    constructor() {
        this.connections = new Map();
        this.reconnectAttempts = new Map();
        this.maxReconnectAttempts = 5;
        this.database = window.firebaseDatabase;
    }
    
    connect(conversationId, callback) {
        if (this.connections.has(conversationId)) {
            return this.connections.get(conversationId);
        }
        
        const messagesRef = this.database.ref(`chats/messages/${conversationId}`)
                                        .orderByChild('timestamp')
                                        .limitToLast(50);
        
        const connection = {
            ref: messagesRef,
            callback: callback,
            isConnected: false,
            listener: null
        };
        
        connection.listener = messagesRef.on('value', 
            (snapshot) => {
                connection.isConnected = true;
                this.reconnectAttempts.set(conversationId, 0);
                
                const messages = [];
                snapshot.forEach(child => {
                    messages.push(child.val());
                });
                
                callback(messages);
            },
            (error) => {
                connection.isConnected = false;
                console.error('Connection error:', error);
                this.handleConnectionError(conversationId, callback);
            }
        );
        
        this.connections.set(conversationId, connection);
        return connection;
    }
    
    disconnect(conversationId) {
        const connection = this.connections.get(conversationId);
        if (connection) {
            connection.ref.off('value', connection.listener);
            this.connections.delete(conversationId);
            this.reconnectAttempts.delete(conversationId);
        }
    }
    
    handleConnectionError(conversationId, callback) {
        const attempts = this.reconnectAttempts.get(conversationId) || 0;
        
        if (attempts < this.maxReconnectAttempts) {
            const delay = Math.pow(2, attempts) * 1000; // Exponential backoff
            
            setTimeout(() => {
                this.reconnectAttempts.set(conversationId, attempts + 1);
                this.connect(conversationId, callback);
            }, delay);
        } else {
            console.error(`Max reconnection attempts reached for conversation ${conversationId}`);
        }
    }
    
    disconnectAll() {
        this.connections.forEach((connection, conversationId) => {
            this.disconnect(conversationId);
        });
    }
}

// 3. Message Cache
class MessageCache {
    constructor(maxSize = 1000) {
        this.cache = new Map();
        this.maxSize = maxSize;
        this.accessOrder = [];
    }
    
    set(key, value) {
        // Remove oldest if at capacity
        if (this.cache.size >= this.maxSize && !this.cache.has(key)) {
            const oldestKey = this.accessOrder.shift();
            this.cache.delete(oldestKey);
        }
        
        // Update access order
        if (this.cache.has(key)) {
            const index = this.accessOrder.indexOf(key);
            this.accessOrder.splice(index, 1);
        }
        
        this.cache.set(key, value);
        this.accessOrder.push(key);
    }
    
    get(key) {
        if (this.cache.has(key)) {
            // Update access order
            const index = this.accessOrder.indexOf(key);
            this.accessOrder.splice(index, 1);
            this.accessOrder.push(key);
            
            return this.cache.get(key);
        }
        return null;
    }
    
    has(key) {
        return this.cache.has(key);
    }
    
    clear() {
        this.cache.clear();
        this.accessOrder = [];
    }
    
    size() {
        return this.cache.size;
    }
}

// 4. DOM Virtual Scrolling for Large Message Lists
class VirtualScrollManager {
    constructor(container, itemHeight = 60) {
        this.container = container;
        this.itemHeight = itemHeight;
        this.visibleItems = Math.ceil(container.clientHeight / itemHeight) + 5;
        this.scrollTop = 0;
        this.totalItems = 0;
        this.items = [];
        
        this.setupScrollListener();
    }
    
    setupScrollListener() {
        this.container.addEventListener('scroll', () => {
            this.scrollTop = this.container.scrollTop;
            this.render();
        });
    }
    
    setItems(items) {
        this.items = items;
        this.totalItems = items.length;
        this.render();
    }
    
    render() {
        const startIndex = Math.floor(this.scrollTop / this.itemHeight);
        const endIndex = Math.min(startIndex + this.visibleItems, this.totalItems);
        
        // Clear container
        this.container.innerHTML = '';
        
        // Create spacer for items above viewport
        if (startIndex > 0) {
            const topSpacer = document.createElement('div');
            topSpacer.style.height = `${startIndex * this.itemHeight}px`;
            this.container.appendChild(topSpacer);
        }
        
        // Render visible items
        for (let i = startIndex; i < endIndex; i++) {
            const item = this.items[i];
            const element = this.createMessageElement(item);
            this.container.appendChild(element);
        }
        
        // Create spacer for items below viewport
        if (endIndex < this.totalItems) {
            const bottomSpacer = document.createElement('div');
            bottomSpacer.style.height = `${(this.totalItems - endIndex) * this.itemHeight}px`;
            this.container.appendChild(bottomSpacer);
        }
    }
    
    createMessageElement(message) {
        // This would use the same logic as UIManager.createMessageElement
        return window.UIManager.createMessageElement(message);
    }
}

// 5. Debounced Input Handler
class DebouncedInputHandler {
    constructor(callback, delay = 300) {
        this.callback = callback;
        this.delay = delay;
        this.timeoutId = null;
    }
    
    handle(value) {
        clearTimeout(this.timeoutId);
        this.timeoutId = setTimeout(() => {
            this.callback(value);
        }, this.delay);
    }
    
    cancel() {
        clearTimeout(this.timeoutId);
    }
}

// 6. Image Lazy Loading
class ImageLazyLoader {
    constructor() {
        this.observer = null;
        this.setupIntersectionObserver();
    }
    
    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.loadImage(entry.target);
                        this.observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px'
            });
        }
    }
    
    observe(img) {
        if (this.observer) {
            this.observer.observe(img);
        } else {
            // Fallback for browsers without IntersectionObserver
            this.loadImage(img);
        }
    }
    
    loadImage(img) {
        const src = img.dataset.src;
        if (src) {
            img.src = src;
            img.removeAttribute('data-src');
            img.classList.add('loaded');
        }
    }
}

// Global instances
window.MessageCache = new MessageCache();
window.ConnectionManager = new ConnectionManager();
window.ImageLazyLoader = new ImageLazyLoader();

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    window.ConnectionManager?.disconnectAll();
    window.MessageCache?.clear();
});
```

## 8. TESTING STRATEGY

### 8.1 Unit Tests

```php
// tests/Unit/ChatPermissionTest.php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatPermissionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_admin_can_chat_with_all_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        
        $this->assertTrue($admin->canChatWith($user));
        $this->assertTrue($admin->canChatWith($subAdmin));
    }
    
    public function test_sub_admin_can_only_chat_with_managed_users()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $managedUser = User::factory()->create([
            'role' => 'user',
            'parent_sub_admin_id' => $subAdmin->id
        ]);
        $otherUser = User::factory()->create(['role' => 'user']);
        
        $this->assertTrue($subAdmin->canChatWith($managedUser));
        $this->assertFalse($subAdmin->canChatWith($otherUser));
    }
    
    public function test_user_can_chat_with_admin_and_sub_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $user = User::factory()->create([
            'role' => 'user',
            'parent_sub_admin_id' => $subAdmin->id
        ]);
        $otherUser = User::factory()->create(['role' => 'user']);
        
        $this->assertTrue($user->canChatWith($admin));
        $this->assertTrue($user->canChatWith($subAdmin));
        $this->assertFalse($user->canChatWith($otherUser));
    }
}
```

### 8.2 Integration Tests

```php
// tests/Feature/ChatApiTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_get_available_chat_users()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        
        Sanctum::actingAs($user);
        
        $response = $this->getJson('/api/chat/users/available');
        
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data') // Chỉ admin
                ->assertJsonFragment(['id' => $admin->id]);
    }
    
    public function test_sub_admin_can_create_conversation_with_managed_user()
    {
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $user = User::factory()->create([
            'role' => 'user',
            'parent_sub_admin_id' => $subAdmin->id
        ]);
        
        Sanctum::actingAs($subAdmin);
        
        $response = $this->postJson('/api/chat/conversations', [
            'participants' => [$user->id],
            'type' => 'direct'
        ]);
        
        $response->assertStatus(201)
                ->assertJsonStructure(['data' => ['id', 'participants']]);
    }
    
    public function test_sub_admin_cannot_chat_with_other_sub_admin_users()
    {
        $subAdmin1 = User::factory()->create(['role' => 'sub_admin']);
        $subAdmin2 = User::factory()->create(['role' => 'sub_admin']);
        $user = User::factory()->create([
            'role' => 'user',
            'parent_sub_admin_id' => $subAdmin2->id
        ]);
        
        Sanctum::actingAs($subAdmin1);
        
        $response = $this->postJson('/api/chat/conversations', [
            'participants' => [$user->id],
            'type' => 'direct'
        ]);
        
        $response->assertStatus(403);
    }
}
```

## 9. DEPLOYMENT VÀ MONITORING

### 9.1 Environment Configuration

```bash
# .env additions for Firebase
FIREBASE_API_KEY=your_api_key
FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
FIREBASE_DATABASE_URL=https://your_project.firebaseio.com
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_STORAGE_BUCKET=your_project.appspot.com
FIREBASE_MESSAGING_SENDER_ID=123456789
FIREBASE_APP_ID=your_app_id
FIREBASE_CREDENTIALS_PATH=/path/to/service-account.json

# Chat specific settings
CHAT_MAX_FILE_SIZE=10485760
CHAT_ALLOWED_FILE_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
CHAT_MESSAGE_RETENTION_DAYS=365
CHAT_MAX_MESSAGES_PER_CONVERSATION=10000
```

### 9.2 Monitoring và Analytics

```javascript
// Chat Analytics Service
class ChatAnalytics {
    constructor() {
        this.events = [];
        this.batchSize = 10;
        this.flushInterval = 30000; // 30 seconds
        
        setInterval(() => {
            this.flush();
        }, this.flushInterval);
    }
    
    track(event, data = {}) {
        this.events.push({
            event,
            data: {
                ...data,
                timestamp: Date.now(),
                userId: this.getCurrentUserId(),
                sessionId: this.getSessionId()
            }
        });
        
        if (this.events.length >= this.batchSize) {
            this.flush();
        }
    }
    
    async flush() {
        if (this.events.length === 0) return;
        
        const eventsToSend = [...this.events];
        this.events = [];
        
        try {
            await fetch('/api/chat/analytics', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                },
                body: JSON.stringify({ events: eventsToSend })
            });
        } catch (error) {
            console.error('Failed to send analytics:', error);
            // Re-add events to queue for retry
            this.events.unshift(...eventsToSend);
        }
    }
    
    // Track specific chat events
    trackMessageSent(conversationId, messageType) {
        this.track('message_sent', {
            conversationId,
            messageType
        });
    }
    
    trackConversationStarted(conversationId, participants) {
        this.track('conversation_started', {
            conversationId,
            participantCount: participants.length
        });
    }
    
    trackFileUploaded(fileType, fileSize) {
        this.track('file_uploaded', {
            fileType,
            fileSize
        });
    }
}
```

### 9.3 Performance Monitoring

```php
// app/Services/ChatPerformanceMonitor.php
<?php

namespace App\Services;

class ChatPerformanceMonitor
{
    public function trackMessageDelivery($messageId, $startTime)
    {
        $deliveryTime = microtime(true) - $startTime;
        
        \Log::info('Message delivery time', [
            'message_id' => $messageId,
            'delivery_time_ms' => $deliveryTime * 1000
        ]);
        
        // Send to monitoring service (e.g., New Relic, DataDog)
        if ($deliveryTime > 1.0) { // > 1 second
            \Log::warning('Slow message delivery', [
                'message_id' => $messageId,
                'delivery_time_ms' => $deliveryTime * 1000
            ]);
        }
    }
    
    public function trackDatabaseQuery($query, $executionTime)
    {
        if ($executionTime > 0.1) { // > 100ms
            \Log::warning('Slow database query in chat', [
                'query' => $query,
                'execution_time_ms' => $executionTime * 1000
            ]);
        }
    }
    
    public function trackFirebaseConnection($status, $responseTime = null)
    {
        \Log::info('Firebase connection status', [
            'status' => $status,
            'response_time_ms' => $responseTime ? $responseTime * 1000 : null
        ]);
    }
}
```

## 10. MAINTENANCE VÀ SCALING

### 10.1 Database Maintenance

```php
// app/Console/Commands/ChatMaintenance.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FirebaseChatService;

class ChatMaintenance extends Command
{
    protected $signature = 'chat:maintenance {--cleanup-old-messages} {--update-statistics} {--cleanup-inactive-tokens}';
    protected $description = 'Perform chat system maintenance tasks';
    
    public function handle()
    {
        if ($this->option('cleanup-old-messages')) {
            $this->cleanupOldMessages();
        }
        
        if ($this->option('update-statistics')) {
            $this->updateStatistics();
        }
        
        if ($this->option('cleanup-inactive-tokens')) {
            $this->cleanupInactiveTokens();
        }
    }
    
    private function cleanupOldMessages()
    {
        $retentionDays = config('chat.message_retention_days', 365);
        $cutoffDate = now()->subDays($retentionDays);
        
        // Firebase cleanup would be done via Firebase Admin SDK
        $this->info("Cleaning up messages older than {$retentionDays} days");
        
        // Implementation depends on your Firebase structure
        // This is a placeholder for the actual cleanup logic
    }
    
    private function updateStatistics()
    {
        // Update chat statistics for all users
        $this->info('Updating chat statistics...');
        
        // Implementation for updating statistics
    }
    
    private function cleanupInactiveTokens()
    {
        $deleted = \App\Models\FirebaseToken::where('last_used_at', '<', now()->subDays(30))
                                          ->delete();
        
        $this->info("Cleaned up {$deleted} inactive Firebase tokens");
    }
}
```

### 10.2 Scaling Considerations

```yaml
# docker-compose.yml for scaling
version: '3.8'
services:
  app:
    build: .
    ports:
      - "8000:8000"
    environment:
      - REDIS_HOST=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - redis
      - mysql
    
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"
    
  queue-worker:
    build: .
    command: php artisan queue:work --sleep=3 --tries=3
    depends_on:
      - redis
      - mysql
    environment:
      - QUEUE_CONNECTION=redis
    
  scheduler:
    build: .
    command: php artisan schedule:work
    depends_on:
      - mysql
      - redis
```

## 11. TROUBLESHOOTING

### 11.1 Common Issues và Solutions

```markdown
## Vấn đề thường gặp

### 1. Firebase Connection Issues
**Triệu chứng**: Không thể kết nối Firebase, messages không real-time
**Nguyên nhân**: 
- Sai cấu hình Firebase
- Network issues
- Firebase rules quá strict

**Giải pháp**:
- Kiểm tra Firebase config trong .env
- Test Firebase rules trong Firebase Console
- Kiểm tra network connectivity
- Enable Firebase debug mode

### 2. Permission Denied Errors
**Triệu chứng**: User không thể gửi/nhận messages
**Nguyên nhân**:
- Firebase security rules sai
- User role không đúng
- Parent-child relationship sai

**Giải pháp**:
- Review Firebase security rules
- Kiểm tra user roles trong database
- Verify parent_sub_admin_id relationships

### 3. Message Delivery Issues
**Triệu chứng**: Messages gửi chậm hoặc không đến
**Nguyên nhân**:
- Firebase quota exceeded
- Network latency
- Client-side connection issues

**Giải pháp**:
- Monitor Firebase usage
- Implement message queuing
- Add retry mechanisms
- Optimize message size

### 4. Push Notification Problems
**Triệu chứng**: Không nhận được notifications
**Nguyên nhân**:
- FCM token expired
- Service worker issues
- Permission not granted

**Giải pháp**:
- Refresh FCM tokens regularly
- Check service worker registration
- Request notification permissions properly
```

### 11.2 Debug Tools

```javascript
// Chat Debug Utility
class ChatDebugger {
    constructor() {
        this.debugMode = process.env.NODE_ENV === 'development';
        this.logs = [];
    }
    
    log(level, message, data = {}) {
        if (!this.debugMode) return;
        
        const logEntry = {
            timestamp: new Date().toISOString(),
            level,
            message,
            data
        };
        
        this.logs.push(logEntry);
        console.log(`[CHAT ${level.toUpperCase()}]`, message, data);
        
        // Keep only last 100 logs
        if (this.logs.length > 100) {
            this.logs.shift();
        }
    }
    
    info(message, data) {
        this.log('info', message, data);
    }
    
    warn(message, data) {
        this.log('warn', message, data);
    }
    
    error(message, data) {
        this.log('error', message, data);
    }
    
    exportLogs() {
        return JSON.stringify(this.logs, null, 2);
    }
    
    clearLogs() {
        this.logs = [];
    }
}

export default new ChatDebugger();
```

## 12. ROADMAP VÀ FUTURE ENHANCEMENTS

### 12.1 Phase 1 - Core Implementation (Month 1-2)
- [ ] Basic chat infrastructure
- [ ] User permission system
- [ ] Real-time messaging
- [ ] File upload functionality
- [ ] Push notifications

### 12.2 Phase 2 - Advanced Features (Month 3-4)
- [ ] Message reactions and replies
- [ ] Voice messages
- [ ] Video calling integration
- [ ] Advanced file sharing
- [ ] Chat analytics dashboard

### 12.3 Phase 3 - Enterprise Features (Month 5-6)
- [ ] Chat bots integration
- [ ] Advanced reporting
- [ ] Multi-language support
- [ ] Chat templates
- [ ] Integration with CRM systems

### 12.4 Future Considerations
- [ ] Mobile app integration
- [ ] AI-powered chat suggestions
- [ ] Advanced security features
- [ ] Performance optimizations
- [ ] Scalability improvements

---

## KẾT LUẬN

Tài liệu này cung cấp một roadmap chi tiết để triển khai hệ thống chat Firebase tích hợp với hệ thống Sub Admin hiện tại. Hệ thống được thiết kế để:

1. **Bảo mật**: Đảm bảo Sub Admin chỉ chat với users thuộc quyền
2. **Scalable**: Có thể mở rộng khi số lượng users tăng
3. **Real-time**: Messaging và notifications real-time
4. **User-friendly**: Interface dễ sử dụng cho tất cả roles
5. **Maintainable**: Code structure rõ ràng, dễ maintain

Việc triển khai nên được thực hiện theo từng phase để đảm bảo chất lượng và stability của hệ thống.

---

## PHẦN BỔ SUNG - CẢI TIẾN VÀ HOÀN THIỆN

### A. Configuration Files Bổ Sung

#### Chat Configuration
```php
// config/chat.php
<?php

return [
    // File upload settings
    'max_file_size' => env('CHAT_MAX_FILE_SIZE', 10485760), // 10MB
    'allowed_file_types' => explode(',', env('CHAT_ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,txt')),
    'upload_path' => env('CHAT_UPLOAD_PATH', 'chat_files'),
    
    // Message settings
    'max_message_length' => env('CHAT_MAX_MESSAGE_LENGTH', 5000),
    'message_retention_days' => env('CHAT_MESSAGE_RETENTION_DAYS', 365),
    'max_messages_per_conversation' => env('CHAT_MAX_MESSAGES_PER_CONVERSATION', 10000),
    
    // Rate limiting
    'rate_limits' => [
        'send_message' => [
            'limit' => env('CHAT_MESSAGE_RATE_LIMIT', 60),
            'window' => env('CHAT_MESSAGE_RATE_WINDOW', 60), // seconds
        ],
        'upload_file' => [
            'limit' => env('CHAT_FILE_RATE_LIMIT', 10),
            'window' => env('CHAT_FILE_RATE_WINDOW', 300), // 5 minutes
        ],
        'create_conversation' => [
            'limit' => env('CHAT_CONVERSATION_RATE_LIMIT', 5),
            'window' => env('CHAT_CONVERSATION_RATE_WINDOW', 3600), // 1 hour
        ]
    ],
    
    // Notification settings
    'notifications' => [
        'enabled' => env('CHAT_NOTIFICATIONS_ENABLED', true),
        'email_enabled' => env('CHAT_EMAIL_NOTIFICATIONS', true),
        'push_enabled' => env('CHAT_PUSH_NOTIFICATIONS', true),
        'sound_enabled' => env('CHAT_SOUND_NOTIFICATIONS', true),
    ],
    
    // Performance settings
    'cache_ttl' => env('CHAT_CACHE_TTL', 3600), // 1 hour
    'pagination_size' => env('CHAT_PAGINATION_SIZE', 50),
    'presence_timeout' => env('CHAT_PRESENCE_TIMEOUT', 300), // 5 minutes
    
    // Security settings
    'enable_message_encryption' => env('CHAT_ENABLE_ENCRYPTION', false),
    'enable_audit_log' => env('CHAT_ENABLE_AUDIT_LOG', true),
    'max_conversation_participants' => env('CHAT_MAX_PARTICIPANTS', 50),
    
    // Feature flags
    'features' => [
        'file_upload' => env('CHAT_FEATURE_FILE_UPLOAD', true),
        'image_upload' => env('CHAT_FEATURE_IMAGE_UPLOAD', true),
        'voice_messages' => env('CHAT_FEATURE_VOICE_MESSAGES', false),
        'video_calls' => env('CHAT_FEATURE_VIDEO_CALLS', false),
        'message_reactions' => env('CHAT_FEATURE_MESSAGE_REACTIONS', true),
        'message_replies' => env('CHAT_FEATURE_MESSAGE_REPLIES', true),
        'typing_indicators' => env('CHAT_FEATURE_TYPING_INDICATORS', true),
        'read_receipts' => env('CHAT_FEATURE_READ_RECEIPTS', true),
    ]
];
```

### B. Error Handling và Exception Classes

#### Custom Exception Classes
```php
// app/Exceptions/Chat/ChatException.php
<?php

namespace App\Exceptions\Chat;

use Exception;

class ChatException extends Exception
{
    protected $errorCode;
    protected $context;

    public function __construct($message = "", $errorCode = 'CHAT_ERROR', $context = [], $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorCode = $errorCode;
        $this->context = $context;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function toArray()
    {
        return [
            'error' => $this->errorCode,
            'message' => $this->getMessage(),
            'context' => $this->context
        ];
    }
}

// app/Exceptions/Chat/PermissionDeniedException.php
<?php

namespace App\Exceptions\Chat;

class PermissionDeniedException extends ChatException
{
    public function __construct($message = "Permission denied", $context = [])
    {
        parent::__construct($message, 'PERMISSION_DENIED', $context, 403);
    }
}

// app/Exceptions/Chat/ConversationNotFoundException.php
<?php

namespace App\Exceptions\Chat;

class ConversationNotFoundException extends ChatException
{
    public function __construct($conversationId, $context = [])
    {
        $message = "Conversation not found: {$conversationId}";
        parent::__construct($message, 'CONVERSATION_NOT_FOUND', array_merge($context, ['conversation_id' => $conversationId]), 404);
    }
}

// app/Exceptions/Chat/RateLimitExceededException.php
<?php

namespace App\Exceptions\Chat;

class RateLimitExceededException extends ChatException
{
    public function __construct($action, $retryAfter, $context = [])
    {
        $message = "Rate limit exceeded for action: {$action}";
        parent::__construct($message, 'RATE_LIMIT_EXCEEDED', array_merge($context, [
            'action' => $action,
            'retry_after' => $retryAfter
        ]), 429);
    }
}
```

#### Global Exception Handler
```php
// app/Exceptions/Handler.php - thêm vào method render()
public function render($request, Throwable $exception)
{
    // Handle Chat exceptions
    if ($exception instanceof \App\Exceptions\Chat\ChatException) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => $exception->getErrorCode(),
                'message' => $exception->getMessage(),
                'context' => $exception->getContext()
            ], $exception->getCode() ?: 400);
        }
        
        // For web requests, redirect with error message
        return redirect()->back()->withErrors([
            'chat_error' => $exception->getMessage()
        ]);
    }

    // Handle Firebase exceptions
    if ($exception instanceof \Kreait\Firebase\Exception\FirebaseException) {
        \Log::error('Firebase error in chat system', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'FIREBASE_ERROR',
                'message' => 'Chat service temporarily unavailable'
            ], 503);
        }
    }

    return parent::render($request, $exception);
}
```

### C. Logging và Monitoring

#### Chat Activity Logger
```php
// app/Services/ChatActivityLogger.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Models\User;

class ChatActivityLogger
{
    public function logMessageSent(User $user, $conversationId, $messageId, $messageType = 'text')
    {
        Log::channel('chat')->info('Message sent', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'conversation_id' => $conversationId,
            'message_id' => $messageId,
            'message_type' => $messageType,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logConversationCreated(User $user, $conversationId, $participants)
    {
        Log::channel('chat')->info('Conversation created', [
            'creator_id' => $user->id,
            'creator_role' => $user->role,
            'conversation_id' => $conversationId,
            'participant_count' => count($participants),
            'participants' => $participants,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logFileUploaded(User $user, $conversationId, $fileName, $fileSize, $fileType)
    {
        Log::channel('chat')->info('File uploaded', [
            'user_id' => $user->id,
            'conversation_id' => $conversationId,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logPermissionDenied(User $user, $action, $resource, $reason = null)
    {
        Log::channel('chat')->warning('Permission denied', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'action' => $action,
            'resource' => $resource,
            'reason' => $reason,
            'timestamp' => now()->toISOString()
        ]);
    }

    public function logError($error, $context = [])
    {
        Log::channel('chat')->error('Chat system error', array_merge([
            'error' => $error,
            'timestamp' => now()->toISOString()
        ], $context));
    }
}
```

#### Logging Configuration
```php
// config/logging.php - thêm channel mới
'channels' => [
    // ... existing channels
    
    'chat' => [
        'driver' => 'daily',
        'path' => storage_path('logs/chat.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
        'replace_placeholders' => true,
    ],
    
    'chat_performance' => [
        'driver' => 'daily',
        'path' => storage_path('logs/chat-performance.log'),
        'level' => 'info',
        'days' => 7,
    ],
],
```

### D. Health Check và Monitoring

#### Chat Health Check
```php
// app/Http/Controllers/HealthCheckController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseAuthService;
use Kreait\Firebase\Factory;

class HealthCheckController extends Controller
{
    public function chatSystem()
    {
        $checks = [];
        $overallStatus = 'healthy';

        // Check Firebase connection
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials'))
                ->withDatabaseUri(config('firebase.database_url'));
            
            $database = $factory->createDatabase();
            $testRef = $database->getReference('.info/connected');
            $snapshot = $testRef->getSnapshot();
            
            $checks['firebase_connection'] = [
                'status' => 'healthy',
                'response_time' => $this->measureResponseTime(function() use ($testRef) {
                    return $testRef->getSnapshot();
                })
            ];
        } catch (\Exception $e) {
            $checks['firebase_connection'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $overallStatus = 'unhealthy';
        }

        // Check database connection
        try {
            \DB::connection()->getPdo();
            $checks['database_connection'] = [
                'status' => 'healthy',
                'response_time' => $this->measureResponseTime(function() {
                    return \DB::select('SELECT 1');
                })
            ];
        } catch (\Exception $e) {
            $checks['database_connection'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $overallStatus = 'unhealthy';
        }

        // Check Redis connection (if used)
        if (config('cache.default') === 'redis') {
            try {
                \Cache::store('redis')->put('health_check', 'ok', 10);
                $checks['redis_connection'] = [
                    'status' => 'healthy',
                    'response_time' => $this->measureResponseTime(function() {
                        return \Cache::store('redis')->get('health_check');
                    })
                ];
            } catch (\Exception $e) {
                $checks['redis_connection'] = [
                    'status' => 'unhealthy',
                    'error' => $e->getMessage()
                ];
                $overallStatus = 'unhealthy';
            }
        }

        // Check storage
        try {
            $testFile = 'health_check_' . time() . '.txt';
            \Storage::put($testFile, 'health check');
            \Storage::delete($testFile);
            
            $checks['storage'] = ['status' => 'healthy'];
        } catch (\Exception $e) {
            $checks['storage'] = [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
            $overallStatus = 'unhealthy';
        }

        return response()->json([
            'status' => $overallStatus,
            'timestamp' => now()->toISOString(),
            'checks' => $checks
        ], $overallStatus === 'healthy' ? 200 : 503);
    }

    private function measureResponseTime(callable $callback)
    {
        $start = microtime(true);
        $callback();
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2) . 'ms';
    }
}
```

### E. Deployment Checklist

#### Pre-deployment Checklist
```markdown
## Chat System Deployment Checklist

### Environment Setup
- [ ] Firebase project created and configured
- [ ] Firebase service account key generated and stored securely
- [ ] Environment variables configured (.env)
- [ ] Database migrations run
- [ ] Firebase security rules deployed
- [ ] Firebase indexes created

### Security
- [ ] Firebase security rules tested
- [ ] Rate limiting configured
- [ ] File upload restrictions in place
- [ ] CORS settings configured
- [ ] SSL/TLS certificates installed

### Performance
- [ ] Database indexes created
- [ ] Redis cache configured (if applicable)
- [ ] CDN configured for static assets
- [ ] Image optimization enabled
- [ ] Gzip compression enabled

### Monitoring
- [ ] Logging configured
- [ ] Error tracking setup (Sentry, Bugsnag, etc.)
- [ ] Performance monitoring enabled
- [ ] Health check endpoints configured
- [ ] Alerts configured for critical errors

### Testing
- [ ] Unit tests passing
- [ ] Integration tests passing
- [ ] Load testing completed
- [ ] Security testing completed
- [ ] Cross-browser testing completed

### Documentation
- [ ] API documentation updated
- [ ] User documentation created
- [ ] Admin documentation created
- [ ] Troubleshooting guide updated
```

### F. Performance Optimization Tips

#### Database Optimization
```sql
-- Recommended indexes for better performance
CREATE INDEX idx_users_role_status ON users(role, status);
CREATE INDEX idx_users_parent_sub_admin ON users(parent_sub_admin_id) WHERE parent_sub_admin_id IS NOT NULL;
CREATE INDEX idx_firebase_tokens_user_active ON firebase_tokens(user_id, is_active);
CREATE INDEX idx_firebase_tokens_last_used ON firebase_tokens(last_used_at);
CREATE INDEX idx_chat_statistics_user_date ON chat_statistics(user_id, date);
CREATE INDEX idx_chat_rate_limits_window ON chat_rate_limits(window_start);
```

#### Caching Strategy
```php
// app/Services/ChatCacheService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

class ChatCacheService
{
    const CACHE_TTL = 3600; // 1 hour
    
    public function cacheUserPermissions(User $user)
    {
        $key = "chat_permissions_{$user->id}";
        
        return Cache::remember($key, self::CACHE_TTL, function() use ($user) {
            return [
                'can_chat_with' => $user->getAvailableChatUsers()->pluck('id')->toArray(),
                'role' => $user->role,
                'parent_sub_admin_id' => $user->parent_sub_admin_id
            ];
        });
    }
    
    public function invalidateUserPermissions(User $user)
    {
        Cache::forget("chat_permissions_{$user->id}");
    }
    
    public function cacheConversationParticipants($conversationId, $participants)
    {
        $key = "conversation_participants_{$conversationId}";
        Cache::put($key, $participants, self::CACHE_TTL);
    }
    
    public function getCachedConversationParticipants($conversationId)
    {
        $key = "conversation_participants_{$conversationId}";
        return Cache::get($key);
    }
}
```

Với những bổ sung này, tài liệu Firebase Chat System đã được hoàn thiện với:

1. **Database migrations và models đầy đủ**
2. **Request validation chi tiết**
3. **CSS styling responsive và đẹp mắt**
4. **Service Worker cho PWA support**
5. **Rate limiting implementation**
6. **Error handling và custom exceptions**
7. **Logging và monitoring system**
8. **Health check endpoints**
9. **Performance optimization**
10. **Deployment checklist**

Tài liệu này giờ đây đã sẵn sàng cho việc triển khai thực tế một hệ thống chat Firebase hoàn chỉnh và professional.