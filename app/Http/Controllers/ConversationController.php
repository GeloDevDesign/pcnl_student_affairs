<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Display conversation list + available admins (1 page only)
     */
 public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Get all conversations of the current user
        $conversations = Conversation::where('admin_id', $user->id)
            ->orWhere('student_id', $user->id)
            ->with(['admin', 'student', 'latestMessage'])
            ->get()
            ->map(function ($conversation) use ($user) {
                
                $otherUser = $conversation->getOtherParticipant($user->id);

                // Check for soft-deleted user and exclude the conversation
                if (is_null($otherUser)) {
                    return null;
                }

                $isStudent = $otherUser->role === 'student';
                
                // Determine the profile photo path: null if student, actual path if admin
                $profilePhotoPath = $isStudent ? null : $otherUser->profile_photo_path;

                // Map the conversation data
                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        // If other user is a student, show as Anonymous
                        'name' => $isStudent ? 'Anonymous' : trim($otherUser->first_name.' '.$otherUser->last_name),
                        'email' => $otherUser->email,
                        'role' => $otherUser->role,
                        // 🔑 CHANGE 1: Conditionally exclude profile photo path for students
                        'profile_photo_path' => $profilePhotoPath, 
                    ],
                    'latest_message' => $conversation->latestMessage ? [
                        'body' => $conversation->latestMessage->body,
                        'created_at' => $conversation->latestMessage->created_at,
                        'is_mine' => $conversation->latestMessage->sender_id === $user->id,
                    ] : null,
                    'unread_count' => $conversation->unreadMessagesCount($user->id),
                    'updated_at' => $conversation->updated_at,
                ];
            })
            ->filter()
            ->sortByDesc('updated_at')
            ->values();

        // 2. For students: get admins they haven't started a conversation with
        $availableAdmins = [];
        if ($user->role === 'student') {
            $existingAdminIds = Conversation::where('student_id', $user->id)
                ->pluck('admin_id')
                ->toArray();

            $availableAdmins = User::where('role', 'admin')
                ->whereNotIn('id', $existingAdminIds)
                ->select('id', 'first_name', 'last_name', 'email')
                ->get()
                ->map(function ($admin) {
                    return [
                        'id' => $admin->id,
                        'name' => trim($admin->first_name.' '.$admin->last_name),
                        'email' => $admin->email,
                    ];
                });
        }

        // 3. Load active conversation messages if ID is provided
        $activeConversation = null;
        $messages = [];

        if ($request->has('conversation_id')) {
            $conversationId = $request->input('conversation_id');
            
            $conversation = Conversation::with(['admin', 'student'])->find($conversationId);

            if ($conversation &&
                ($conversation->admin_id === $user->id || $conversation->student_id === $user->id)) {

                $otherUser = $conversation->getOtherParticipant($user->id);

                // Check: Ensure the other user is not null (i.e., not soft-deleted)
                if ($otherUser) {
                    // Mark messages as read
                    $conversation->messages()
                        ->where('sender_id', '!=', $user->id)
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);
                        
                    $isStudent = $otherUser->role === 'student';

                    $activeConversation = [
                        'id' => $conversation->id,
                        'other_user' => [
                            'id' => $otherUser->id,
                            // If other user is a student, show as Anonymous
                            'name' => $isStudent ? 'Anonymous' : trim($otherUser->first_name.' '.$otherUser->last_name),
                            'email' => $otherUser->email,
                            'role' => $otherUser->role,
                            // 🔑 CHANGE 2: Conditionally exclude profile photo path for students
                            'profile_photo_path' => $isStudent ? null : $otherUser->profile_photo_path,
                        ],
                    ];

                    $messages = $conversation->messages()
                        ->with('sender')
                        ->orderBy('created_at', 'asc')
                        ->get()
                        ->map(function ($message) use ($user) {
                            $senderExists = !is_null($message->sender);
                            $isSenderStudent = $senderExists && $message->sender->role === 'student';

                            return [
                                'id' => $message->id,
                                'body' => $message->body,
                                'is_mine' => $message->sender_id === $user->id,
                                'sender' => [
                                    'id' => $senderExists ? $message->sender->id : null,
                                    
                                    // Handle sender's name
                                    'name' => $senderExists
                                        ? ($isSenderStudent ? 'Anonymous' : trim($message->sender->first_name.' '.$message->sender->last_name))
                                        : '[Deleted User]',
                                    
                                    'role' => $senderExists ? $message->sender->role : 'deleted',
                                    
                                    // 🔑 Safety Check and Conditional exclusion: null if student, actual path otherwise
                                    'profile_photo_path' => $senderExists && !$isSenderStudent ? $message->sender->profile_photo_path : null,
                                ],
                                'created_at' => $message->created_at,
                                'read_at' => $message->read_at,
                            ];
                        });
                }
            }
        }

        // 4. Render the Inertia view
        return Inertia::render('concerns/index', [
            'conversations' => $conversations,
            'availableAdmins' => $availableAdmins,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'pageTitle' => 'Concerns & Messages',
        ]);
    }


    /**
     * Create new conversation (students only)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        if ($user->role !== 'student') {
            return back()->withErrors(['error' => 'Only students can start conversations.']);
        }

        // Check if conversation already exists
        $conversation = Conversation::where('admin_id', $validated['admin_id'])
            ->where('student_id', $user->id)
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'admin_id' => $validated['admin_id'],
                'student_id' => $user->id,
            ]);
        }

        // Create first message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['message'],
        ]);

        // Notify admin
        $receiver = User::find($validated['admin_id']);
        $receiver->notify(new MessageReceivedNotification($message, $user));
    }

    /**
     * Send message in a conversation
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $user = auth()->user();

        if ($conversation->admin_id !== $user->id && $conversation->student_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Determine who receives this message
        $receiverId = $conversation->admin_id == $user->id
            ? $conversation->student_id
            : $conversation->admin_id;

        $receiver = User::find($receiverId);

        // Send notification
        $receiver->notify(new MessageReceivedNotification($message, $user));

        // Update timestamp to move conversation to top
        $conversation->touch();

        return redirect()->route('concerns.index', ['conversation_id' => $conversation->id]);
    }

    /**
     * Delete a conversation
     */
    public function destroy(Conversation $conversation)
    {
        $user = auth()->user();

        if ($conversation->admin_id !== $user->id && $conversation->student_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return redirect()->route('concerns.index')->with('success', 'Conversation deleted.');
    }
}
