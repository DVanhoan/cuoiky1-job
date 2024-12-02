<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $conversations = $user->conversations()
            ->with([
                'latestMessage',
                'members.user'
            ])
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherParticipant = $conversation->getOtherParticipantAttribute();

                return [
                    'id' => $conversation->id,
                    'last_message' => $conversation->latestMessage ? $conversation->latestMessage->content : null,
                    'last_message_time' => $conversation->latestMessage ? $conversation->latestMessage->created_at->format('H:i A') : null,
                    'isSender' => $conversation->latestMessage && $conversation->latestMessage->sender_id === $user->id,
                    'unread' => $conversation->messages
                            ->where('statuses.status', 'unread')
                            ->where('statuses.user_id', '!=', $user->id)
                            ->count() > 0,
                    'other_participant' => $otherParticipant
                        ? [
                            'id' => $otherParticipant->id,
                            'name' => $otherParticipant->name,
                            'avatar' => $otherParticipant->avatar,
                        ]
                        : null,
                ];
            });


        $recentConversation = $user->conversations()
            ->with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc(function ($query) {
                $query->selectRaw('MAX(messages.updated_at)')->from('messages');
            })
            ->first();
        $recentMessages = $recentConversation
            ? $recentConversation->messages()->with('sender')->get()->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->sender->name,
                    'content' => $message->content,
                    'created_at' => $message->created_at->format('H:i A'),
                    'isSender' => $message->sender_id === auth()->id(),
                ];
            })
            : [];

        return response()->json([
            'conversations' => $conversations,
            'recentConversationId' => $recentConversation ? $recentConversation->id : null,
            'recentMessages' => $recentMessages,
            'user' => $user,
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:private,group',
            'name' => 'nullable|string|max:255',
            'members' => 'required|array|min:2',
            'members.*' => 'exists:users,id',
        ]);


        $conversation = Conversation::create([
            'type' => $validated['type'],
            'name' => $validated['type'] === 'group' ? $validated['name'] : null,
        ]);


        $conversation->members()->createMany(
            collect($validated['members'])->map(function ($userId) {
                return ['user_id' => $userId];
            })
        );

        return response()->json([
            'message' => 'Conversation created successfully',
            'conversation' => $conversation,
        ]);
    }


    public function show($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $conversation = $user->conversations()
            ->with(['messages.sender', 'members.user'])
            ->find($id);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation not found'], 404);
        }

        return response()->json([
            'id' => $conversation->id,
            'type' => $conversation->type,
            'name' => $conversation->name,
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'isSender' => $message->sender_id === auth()->id(),
                    'sender' => $message->sender->name,
                    'content' => $message->content,
                    'created_at' => $message->created_at->format('H:i A'),
                ];
            }),
            'members' => $conversation->members->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'avatar' => $member->user->avatar,
                ];
            }),
        ]);
    }
}
