<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ConversationController extends Controller
{
    /**
     * Display conversation list + available admins (1 page only)
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get all conversations of current user
        $conversations = Conversation::where('admin_id', $user->id)
            ->orWhere('student_id', $user->id)
            ->with(['admin', 'student', 'latestMessage'])
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherUser = $conversation->getOtherParticipant($user->id);
                return [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => trim($otherUser->first_name . ' ' . $otherUser->last_name),
                        'email' => $otherUser->email,
                        'role' => $otherUser->role,
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
            ->sortByDesc('updated_at')
            ->values();

        // For students: get admins they haven't started a conversation with
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
                        'name' => trim($admin->first_name . ' ' . $admin->last_name),
                        'email' => $admin->email,
                    ];
                });
        }

        // If conversation_id is provided, load that conversation's messages
        $activeConversation = null;
        $messages = [];
        
        if ($request->has('conversation_id')) {
            $conversationId = $request->conversation_id;
            $conversation = Conversation::find($conversationId);
            
            if ($conversation && 
                ($conversation->admin_id === $user->id || $conversation->student_id === $user->id)) {
                
                // Mark messages as read
                $conversation->messages()
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
                
                $otherUser = $conversation->getOtherParticipant($user->id);
                
                $activeConversation = [
                    'id' => $conversation->id,
                    'other_user' => [
                        'id' => $otherUser->id,
                        'name' => trim($otherUser->first_name . ' ' . $otherUser->last_name),
                        'email' => $otherUser->email,
                        'role' => $otherUser->role,
                    ],
                ];
                
                $messages = $conversation->messages()
                    ->with('sender')
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($message) use ($user) {
                        return [
                            'id' => $message->id,
                            'body' => $message->body,
                            'is_mine' => $message->sender_id === $user->id,
                            'sender' => [
                                'id' => $message->sender->id,
                                'name' => trim($message->sender->first_name . ' ' . $message->sender->last_name),
                                'role' => $message->sender->role,
                            ],
                            'created_at' => $message->created_at,
                            'read_at' => $message->read_at,
                        ];
                    });
            }
        }

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

        if (!$conversation) {
            $conversation = Conversation::create([
                'admin_id' => $validated['admin_id'],
                'student_id' => $user->id,
            ]);
        }

        // Create first message
        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['message'],
        ]);

        return redirect()->route('concerns.index', ['conversation_id' => $conversation->id]);
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

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
        ]);

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