<?php

namespace App\Services;

use App\User;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FirebaseNotificationService
{
    protected $firebaseConfig;

    public function __construct()
    {
        $this->firebaseConfig = [
            'apiKey' => config('firebase.api_key'),
            'authDomain' => config('firebase.auth_domain'),
            'databaseURL' => config('firebase.database_url'),
            'projectId' => config('firebase.project_id'),
            'storageBucket' => config('firebase.storage_bucket'),
            'messagingSenderId' => config('firebase.messaging_sender_id'),
            'appId' => config('firebase.app_id'),
        ];
    }

    /**
     * Send order notification to appropriate recipients
     */
    public function sendOrderNotification(Order $order)
    {
        try {
            $user = $order->user;
            if (!$user) {
                Log::error('Order user not found', ['order_id' => $order->id]);
                return false;
            }

            // Prepare notification data
            $notificationData = [
                'id' => Str::uuid()->toString(),
                'type' => 'order_created',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'total_amount' => $order->total_amount,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'created_at' => $order->created_at->timestamp,
                'read' => false,
                'title' => 'New Order Created',
                'message' => "Order #{$order->order_number} created by {$user->name} - $" . number_format($order->total_amount, 2),
                'fas' => 'fa-shopping-cart',
                'actionURL' => route('order.show', $order->id)
            ];

            // Determine recipients
            $recipients = $this->getNotificationRecipients($user);
            
            // Send to each recipient
            foreach ($recipients as $recipient) {
                $this->sendToFirebase($recipient, $notificationData);
                
                // Also save to database as backup
                $this->saveToDatabase($recipient, $notificationData);
            }

            Log::info('Order notification sent successfully', [
                'order_id' => $order->id,
                'recipients_count' => count($recipients)
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send order notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Determine who should receive notifications for this user's order
     */
    protected function getNotificationRecipients(User $user)
    {
        $recipients = [];

        // Always notify all admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        foreach ($admins as $admin) {
            $recipients[] = [
                'user' => $admin,
                'type' => 'admin'
            ];
        }

        // If user has a parent sub-admin, notify them too
        if ($user->parent_sub_admin_id) {
            $subAdmin = User::where('id', $user->parent_sub_admin_id)
                           ->where('role', 'sub_admin')
                           ->where('status', 'active')
                           ->first();
            
            if ($subAdmin) {
                $recipients[] = [
                    'user' => $subAdmin,
                    'type' => 'sub_admin'
                ];
            }
        }

        return $recipients;
    }

    /**
     * Send notification to Firebase (This would be implemented with Firebase SDK)
     * For now, we'll prepare the data structure
     */
    protected function sendToFirebase($recipient, $notificationData)
    {
        // In a real implementation, you would use Firebase SDK to write to Realtime Database
        // For now, we'll log the structure that would be sent
        
        $firebaseData = [
            'path' => "notifications/{$recipient['type']}/{$recipient['user']->id}/{$notificationData['id']}",
            'data' => $notificationData
        ];

        Log::info('Firebase notification prepared', $firebaseData);

        // TODO: Implement actual Firebase SDK call
        // $firebase->getReference($firebaseData['path'])->set($firebaseData['data']);
        
        return true;
    }

    /**
     * Save notification to database as backup
     */
    protected function saveToDatabase($recipient, $notificationData)
    {
        try {
            \DB::table('notifications')->insert([
                'id' => $notificationData['id'],
                'type' => 'App\Notifications\OrderCreatedNotification',
                'notifiable_type' => 'App\User',
                'notifiable_id' => $recipient['user']->id,
                'data' => json_encode($notificationData),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save notification to database', [
                'recipient_id' => $recipient['user']->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get notifications for a specific user
     */
    public function getNotificationsForUser($userId, $limit = 50)
    {
        try {
            $notifications = \DB::table('notifications')
                ->where('notifiable_type', 'App\User')
                ->where('notifiable_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return $notifications->map(function ($notification) {
                $data = json_decode($notification->data, true);
                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'unknown',
                    'title' => $data['title'] ?? 'Notification',
                    'message' => $data['message'] ?? '',
                    'data' => $data,
                    'read' => $notification->read_at !== null,
                    'created_at' => $notification->created_at
                ];
            });

        } catch (\Exception $e) {
            Log::error('Failed to get notifications for user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        try {
            $updated = \DB::table('notifications')
                ->where('id', $notificationId)
                ->where('notifiable_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            if ($updated) {
                Log::info('Notification marked as read', [
                    'notification_id' => $notificationId,
                    'user_id' => $userId
                ]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to mark notification as read', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get unread notification count for user
     */
    public function getUnreadCount($userId)
    {
        try {
            return \DB::table('notifications')
                ->where('notifiable_type', 'App\User')
                ->where('notifiable_id', $userId)
                ->whereNull('read_at')
                ->count();
        } catch (\Exception $e) {
            Log::error('Failed to get unread count', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId)
    {
        try {
            $updated = \DB::table('notifications')
                ->where('notifiable_type', 'App\User')
                ->where('notifiable_id', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            Log::info('All notifications marked as read', [
                'user_id' => $userId,
                'count' => $updated
            ]);

            return $updated;
        } catch (\Exception $e) {
            Log::error('Failed to mark all notifications as read', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Send insufficient wallet balance notification to user
     */
    public function sendInsufficientWalletNotification(User $user, $currentBalance, $totalAmount, $shortfall)
    {
        try {
            // Prepare notification data
            $notificationData = [
                'id' => Str::uuid()->toString(),
                'type' => 'insufficient_wallet_balance',
                'user_id' => $user->id,
                'current_balance' => $currentBalance,
                'required_amount' => $totalAmount,
                'shortfall' => $shortfall,
                'created_at' => now()->timestamp,
                'read' => false,
                'title' => 'Order Failed - Insufficient Wallet Balance'. "An order was attempted for your account but failed due to insufficient wallet balance. Your current balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalAmount, 2) . ". Please add $" . number_format($shortfall, 2) . " to your wallet to complete future orders.",
                'message' => "An order was attempted for your account but failed due to insufficient wallet balance. Your current balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalAmount, 2) . ". Please add $" . number_format($shortfall, 2) . " to your wallet to complete future orders.",
                'fas' => 'fas fa-exclamation-triangle',
                'actionURL' => route('wallet.index')
            ];

            // Send to Firebase
            $recipient = [
                'user' => $user,
                'type' => 'user'
            ];
            
            $this->sendToFirebase($recipient, $notificationData);
            
            // Also save to database as backup
            $this->saveToDatabase($recipient, $notificationData);

            Log::info('Insufficient wallet balance notification sent successfully', [
                'user_id' => $user->id,
                'current_balance' => $currentBalance,
                'required_amount' => $totalAmount,
                'shortfall' => $shortfall
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to send insufficient wallet balance notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clean up old notifications (older than 30 days)
     */
    public function cleanupOldNotifications()
    {
        try {
            $deleted = \DB::table('notifications')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();

            Log::info('Old notifications cleaned up', ['count' => $deleted]);
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup old notifications', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
}