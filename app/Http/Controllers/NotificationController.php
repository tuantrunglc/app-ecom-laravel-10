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
            return redirect($notification->data['actionURL']);
        }
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
     * Mark notification as read (API endpoint)
     */
    public function markAsRead(Request $request)
    {
        $notificationId = $request->get('notification_id');
        
        if (!$notificationId) {
            return response()->json([
                'success' => false,
                'message' => 'Notification ID is required'
            ], 400);
        }

        $result = $this->firebaseService->markAsRead($notificationId, Auth::id());
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Notification marked as read' : 'Failed to mark notification as read'
        ]);
    }

    /**
     * Mark all notifications as read (API endpoint)
     */
    public function markAllAsRead()
    {
        $count = $this->firebaseService->markAllAsRead(Auth::id());
        
        return response()->json([
            'success' => true,
            'message' => "Marked {$count} notifications as read",
            'count' => $count
        ]);
    }

    /**
     * Get unread notification count (API endpoint)
     */
    public function getUnreadCount()
    {
        $count = $this->firebaseService->getUnreadCount(Auth::id());
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
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
