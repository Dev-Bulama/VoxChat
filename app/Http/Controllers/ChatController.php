<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(private ChatService $chatService) {}

    public function index()
    {
        $user = Auth::user();

        $chats = Chat::query()
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id)->whereNull('left_at'))
            ->with([
                'participants',
                'lastMessage.sender',
                'group',
            ])
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('messages')
                    ->whereColumn('messages.chat_id', 'chats.id')
                    ->latest()
                    ->limit(1);
            })
            ->get()
            ->map(function (Chat $chat) use ($user) {
                $chat->display_name   = $chat->getDisplayNameFor($user->id);
                $chat->display_avatar = $chat->getDisplayAvatarFor($user->id);
                $chat->unread_count   = $chat->getUnreadCountFor($user->id);
                $chat->is_pinned      = $chat->participants->firstWhere('id', $user->id)?->pivot->is_pinned ?? false;
                $chat->is_archived    = $chat->participants->firstWhere('id', $user->id)?->pivot->is_archived ?? false;
                return $chat;
            });

        $stories = \App\Models\Story::active()
            ->with('user')
            ->whereHas('user', fn($q) => $q->where('id', '!=', $user->id))
            ->get()
            ->groupBy('user_id');

        return view('chat.index', compact('chats', 'stories'));
    }

    public function show(Chat $chat)
    {
        $user = Auth::user();

        abort_unless(
            $chat->participants()->where('user_id', $user->id)->whereNull('left_at')->exists(),
            403,
            'You are not a participant of this chat.'
        );

        $messages = $chat->messages()
            ->with(['sender', 'replyTo.sender', 'reactions', 'receipts'])
            ->visible()
            ->orderBy('created_at')
            ->paginate(50);

        // Mark messages as read
        $this->chatService->markChatAsRead($chat, $user->id);

        $other = $chat->type === 'private' ? $chat->getOtherParticipant($user->id) : null;
        $group = $chat->type === 'group' ? $chat->group : null;

        return view('chat.show', compact('chat', 'messages', 'other', 'group'));
    }

    public function startPrivateChat(Request $request)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $targetUser = User::findOrFail($request->user_id);
        $currentUser = Auth::user();

        abort_if($targetUser->id === $currentUser->id, 400, 'Cannot chat with yourself.');

        $chat = $this->chatService->findOrCreatePrivateChat($currentUser->id, $targetUser->id);

        return redirect()->route('chats.show', $chat);
    }

    public function showCreateGroup()
    {
        return view('chat.create-group');
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'members'    => 'required|array|min:1|max:99',
            'members.*'  => 'exists:users,id',
            'avatar'     => 'nullable|image|max:5120',
        ]);

        $user  = Auth::user();
        $chat  = $this->chatService->createGroupChat($user->id, $request->name, $request->members, $request->file('avatar'));

        return redirect()->route('chats.show', $chat)->with('success', 'Group created successfully!');
    }

    public function search(Request $request)
    {
        $request->validate(['q' => 'required|string|min:1|max:100']);

        $user  = Auth::user();
        $query = $request->q;

        $users = User::active()
            ->where('id', '!=', $user->id)
            ->where(fn($q) =>
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%")
            )
            ->limit(10)
            ->get(['id', 'name', 'username', 'avatar', 'is_online', 'last_seen_at'])
            ->map(fn($u) => array_merge($u->toArray(), ['avatar_url' => $u->avatar_url]));

        $messages = Message::visible()
            ->whereHas('chat.participants', fn($q) => $q->where('user_id', $user->id))
            ->where('type', 'text')
            ->where('body', 'like', "%{$query}%")
            ->with(['chat', 'sender'])
            ->limit(10)
            ->get();

        return response()->json(compact('users', 'messages'));
    }

    public function archive(Chat $chat)
    {
        $this->authorize('participate', $chat);
        $this->chatService->toggleArchive($chat, Auth::id());
        return back()->with('success', 'Chat archived.');
    }

    public function pin(Chat $chat)
    {
        $this->authorize('participate', $chat);
        $this->chatService->togglePin($chat, Auth::id());
        return back()->with('success', 'Chat pinned.');
    }

    public function mute(Chat $chat, Request $request)
    {
        $this->authorize('participate', $chat);
        $request->validate(['duration' => 'nullable|integer|in:8,168,0']); // 8h, 1 week, always
        $this->chatService->mute($chat, Auth::id(), $request->duration);
        return back()->with('success', 'Chat muted.');
    }

    public function delete(Chat $chat)
    {
        $this->authorize('participate', $chat);
        $this->chatService->deleteChatForUser($chat, Auth::id());
        return redirect()->route('chats.index')->with('success', 'Chat deleted.');
    }
}
