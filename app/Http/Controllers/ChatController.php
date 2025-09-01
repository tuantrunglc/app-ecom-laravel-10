<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $availableUsers = $user->getAvailableChatUsers();
        
        // Load parentSubAdmin relationship for display
        $availableUsers->load('parentSubAdmin');
        
        $conversations = $user->conversations()->with('participants')->get();
        
        // Generate a simple token for frontend (you can make this more secure)
        $customToken = base64_encode(json_encode([
            'uid' => $user->id,
            'role' => $user->role,
            'parent_sub_admin_id' => $user->parent_sub_admin_id,
            'exp' => time() + 3600 // 1 hour expiry
        ]));

        // Return different views based on user role
        switch ($user->role) {
            case 'admin':
                return view('chat.admin.index', compact('user', 'availableUsers', 'conversations', 'customToken'));
            case 'sub_admin':
                return view('chat.sub_admin.index', compact('user', 'availableUsers', 'conversations', 'customToken'));
            case 'user':
                return view('chat.user.index', compact('user', 'availableUsers', 'conversations', 'customToken'));
            default:
                return view('chat.index', compact('user', 'availableUsers', 'conversations', 'customToken'));
        }
    }

    public function createConversation(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:users,id'
        ]);

        $currentUser = auth()->user();
        $participant = User::findOrFail($request->participant_id);

        // Kiểm tra quyền chat theo business rules
        if (!$currentUser->canChatWith($participant)) {
            $errorMessage = $this->getChatPermissionErrorMessage($currentUser, $participant);
            return redirect()->back()->with('error', $errorMessage);
        }

        // Kiểm tra conversation đã tồn tại chưa
        $existingConversation = $currentUser->conversations()
            ->whereHas('participants', function($query) use ($participant) {
                $query->where('user_id', $participant->id);
            })
            ->where('type', 'direct')
            ->first();

        if ($existingConversation) {
            return redirect()->route('chat.conversation', $existingConversation->id);
        }

        // Tạo conversation mới
        $conversationId = 'conv_' . \Illuminate\Support\Str::random(10);
        
        $conversation = Conversation::create([
            'id' => $conversationId,
            'type' => 'direct',
            'created_by' => $currentUser->id
        ]);

        $conversation->participants()->attach([$currentUser->id, $participant->id]);

        // Firebase conversation will be created by JavaScript on frontend

        return redirect()->route('chat.conversation', $conversationId);
    }

    public function showConversation($conversationId)
    {
        $user = auth()->user();
        $conversation = Conversation::with('participants')->findOrFail($conversationId);

        // Kiểm tra user có trong conversation không
        if (!$conversation->participants->contains($user)) {
            abort(403, 'Unauthorized');
        }

        $otherParticipant = $conversation->getOtherParticipant($user->id);
        
        // Generate a simple token for frontend
        $customToken = base64_encode(json_encode([
            'uid' => $user->id,
            'role' => $user->role,
            'parent_sub_admin_id' => $user->parent_sub_admin_id,
            'exp' => time() + 3600
        ]));

        // Return different conversation views based on user role
        switch ($user->role) {
            case 'admin':
                return view('chat.admin.conversation', compact('user', 'conversation', 'otherParticipant', 'customToken'));
            case 'sub_admin':
                return view('chat.sub_admin.conversation', compact('user', 'conversation', 'otherParticipant', 'customToken'));
            case 'user':
                return view('chat.user.conversation', compact('user', 'conversation', 'otherParticipant', 'customToken'));
            default:
                return view('chat.conversation', compact('user', 'conversation', 'otherParticipant', 'customToken'));
        }
    }

    public function uploadImage(Request $request)
    {
        // Validate input similar to BannerController's image rules
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'conversation_id' => 'required|exists:conversations,id'
        ]);

        $user = auth()->user();
        $conversation = Conversation::findOrFail($request->conversation_id);

        // Ensure the authenticated user is a participant in the conversation
        if (!$conversation->participants->contains($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($request->hasFile('image')) {
            try {
                $image = $request->file('image');
                $extension = $image->getClientOriginalExtension();
                $imageName = 'chat_' . time() . '.' . $extension;

                // Store under a separate folder for chat images: public/photos/chat
                $photosPath = public_path('photos/chat');
                if (!file_exists($photosPath)) {
                    // Create directory if it doesn't exist
                    mkdir($photosPath, 0777, true);
                }

                // Move uploaded file to public/photos/chat
                $image->move($photosPath, $imageName);

                // Return paths under /photos/chat to keep separate from banner images
                $relativePath = '/photos/chat/' . $imageName;
                $imageUrl = asset(ltrim($relativePath, '/'));

                return response()->json([
                    'success' => true,
                    'imageUrl' => $imageUrl,
                    'imagePath' => $relativePath,
                    'imageName' => $imageName
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'No image uploaded'], 400);
    }

    private function getChatPermissionErrorMessage($currentUser, $participant)
    {
        if ($currentUser->role === 'sub_admin') {
            if ($participant->role === 'user') {
                return 'Bạn chỉ có thể chat với Users thuộc quyền quản lý của mình.';
            }
        }
        
        if ($currentUser->role === 'user') {
            if ($participant->role === 'sub_admin') {
                return 'Bạn chỉ có thể chat với Sub Admin quản lý mình.';
            }
        }
        
        return 'Bạn không có quyền chat với người dùng này.';
    }

    public function getConversations()
    {
        $user = auth()->user();
        $conversations = $user->conversations()->with('participants')->get();
        
        $conversationsData = $conversations->map(function($conversation) use ($user) {
            $otherParticipant = $conversation->getOtherParticipant($user->id);
            return [
                'id' => $conversation->id,
                'otherParticipant' => [
                    'id' => $otherParticipant->id,
                    'name' => $otherParticipant->name,
                    'role' => $otherParticipant->role,
                    'avatar' => $otherParticipant->photo,
                    'parentSubAdminId' => $otherParticipant->parent_sub_admin_id
                ],
                'created_at' => $conversation->created_at->toISOString(),
                'updated_at' => $conversation->updated_at->toISOString()
            ];
        });

        return response()->json($conversationsData);
    }
}