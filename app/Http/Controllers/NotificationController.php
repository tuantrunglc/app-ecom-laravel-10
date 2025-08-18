<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function index(){
        return view('backend.notification.index');
    }

    public function show(Request $request){
        $notification = Auth()->user()->notifications()->where('id',$request->id)->first();
        if($notification){
            $notification->markAsRead();
            $data = $notification->data ?? [];

            // Prefer explicit links
            $targetUrl = $data['actionURL'] ?? ($data['link'] ?? null);

            // Build chat URL if not provided
            if (!$targetUrl) {
                $convId = $data['conversation_id'] ?? ($data['conversationId'] ?? null);
                if ($convId) {
                    $targetUrl = url('/chat?conversationId=' . $convId);
                } else {
                    // Fallback to chat system if it looks like a chat notification
                    $type = strtolower($data['type'] ?? '');
                    if (strpos($type, 'chat') !== false || strpos(($data['title'] ?? ''), 'message') !== false) {
                        $targetUrl = url('/chat');
                    }
                }
            }

            // Final fallback: notifications index
            if (!$targetUrl) {
                return redirect()->route('all.notification');
            }

            return redirect($targetUrl);
        }
        return redirect()->route('all.notification');
    }

    public function delete($id){
        $notification = Notification::find($id);
        if($notification){
            $status = $notification->delete();
            if($status){
                request()->session()->flash('success','Notification successfully deleted');
                return back();
            }
            else{
                request()->session()->flash('error','Error please try again');
                return back();
            }
        }
        else{
            request()->session()->flash('error','Notification not found');
            return back();
        }
    }

    /**
     * Get notifications for current user (API endpoint)
     */
    public function getNotifications(Request $request)
    {
        $limit = $request->get('limit', 20);
        $notifications = $this->firebaseService->getNotificationsForUser(Auth::id(), $limit);
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $this->firebaseService->getUnreadCount(Auth::id())
        ]);
    }

    /**
     * Get notifications for current user from database (Frontend endpoint)
     */
    public function getUserNotifications(Request $request)
    {
        try {
            $limit = $request->get('limit', 20);
            
            $notifications = Auth::user()
                ->notifications()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
            
            $unreadCount = Auth::user()
                ->unreadNotifications()
                ->count();
            
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get user notifications error:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get notifications',
                'notifications' => [],
                'unread_count' => 0
            ]);
        }
    }

    /**
     * Mark notification as read (API endpoint)
     */
    public function markAsRead(Request $request)
    {
        try {
            $notificationId = $request->get('notification_id');
            
            if (!$notificationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification ID is required'
                ], 400);
            }

            // Try to mark as read in database first (for frontend users)
            $notification = Auth::user()
                ->notifications()
                ->where('id', $notificationId)
                ->whereNull('read_at')
                ->first();
            
            if ($notification) {
                $notification->markAsRead();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Notification marked as read'
                ]);
            }
            
            // If not found in database, try Firebase (for admin/API users)
            $result = $this->firebaseService->markAsRead($notificationId, Auth::id());
            
            return response()->json([
                'success' => $result,
                'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Mark notification as read error:', [
                'user_id' => Auth::id(),
                'notification_id' => $notificationId ?? null,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ]);
        }
    }

    /**
     * Mark all notifications as read (API endpoint)
     */
    public function markAllAsRead()
    {
        try {
            // Mark all unread notifications as read in database
            $unreadNotifications = Auth::user()
                ->unreadNotifications();
            
            $count = $unreadNotifications->count();
            $unreadNotifications->update(['read_at' => now()]);
            
            // Also try Firebase for admin users
            $firebaseCount = $this->firebaseService->markAllAsRead(Auth::id());
            $totalCount = $count + $firebaseCount;
            
            return response()->json([
                'success' => true,
                'message' => "Marked {$totalCount} notifications as read",
                'count' => $totalCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Mark all notifications as read error:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark all notifications as read'
            ]);
        }
    }

    /**
     * Get unread notification count (API endpoint)
     */
    public function getUnreadCount()
    {
        try {
            // Get unread count from database
            $databaseCount = Auth::user()
                ->unreadNotifications()
                ->count();
            
            // Get Firebase count for admin users
            $firebaseCount = $this->firebaseService->getUnreadCount(Auth::id());
            
            $totalCount = $databaseCount + $firebaseCount;
            
            return response()->json([
                'success' => true,
                'count' => $totalCount
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Get unread count error:', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => true,
                'count' => 0
            ]);
        }
    }

    /**
     * Get Firebase config for frontend
     */
    public function getFirebaseConfig()
    {
        return response()->json([
            'success' => true,
            'config' => [
                'apiKey' => config('firebase.api_key'),
                'authDomain' => config('firebase.auth_domain'),
                'databaseURL' => config('firebase.database_url'),
                'projectId' => config('firebase.project_id'),
                'storageBucket' => config('firebase.storage_bucket'),
                'messagingSenderId' => config('firebase.messaging_sender_id'),
                'appId' => config('firebase.app_id'),
            ]
        ]);
    }
}
