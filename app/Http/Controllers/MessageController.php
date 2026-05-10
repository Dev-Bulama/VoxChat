<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Events\MessageDeleted;
use App\Events\MessageReacted;
use App\Events\TypingStarted;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function send(Request $request, Chat $chat)
    {
        abort_unless(
            $chat->participants()->where('user_id', Auth::id())->whereNull('left_at')->exists(),
            403
        );

        $request->validate([
            'type'       => 'required|in:text,image,video,audio,voice_note,file,location,gif',
            'body'       => 'nullable|string|max:4096',
            'media'      => 'nullable|file|max:102400',
            'reply_to_id'=> 'nullable|exists:messages,id',
            'metadata'   => 'nullable|array',
        ]);

        $mediaData = [];
        if ($request->hasFile('media')) {
            $mediaData = $this->mediaService->processUpload($request->file('media'), $request->type);
        }

        $message = Message::create([
            'chat_id'      => $chat->id,
            'sender_id'    => Auth::id(),
            'reply_to_id'  => $request->reply_to_id,
            'type'         => $request->type,
            'body'         => $request->body,
            'metadata'     => $request->metadata,
            ...$mediaData,
        ]);

        $chat->update(['last_message_id' => $message->id]);

        $message->load(['sender', 'replyTo.sender', 'reactions']);

        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => $message,
            'html'    => view('chat.partials.message', ['message' => $message, 'user' => Auth::user()])->render(),
        ]);
    }

    public function edit(Request $request, Message $message)
    {
        abort_unless($message->sender_id === Auth::id(), 403);
        abort_unless($message->type === 'text', 422, 'Only text messages can be edited.');
        abort_if($message->created_at->diffInMinutes(now()) > 15, 422, 'Message edit window has expired.');

        $request->validate(['body' => 'required|string|max:4096']);

        $message->update([
            'body'      => $request->body,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        broadcast(new \App\Events\MessageEdited($message))->toOthers();

        return response()->json(['message' => $message]);
    }

    public function delete(Request $request, Message $message)
    {
        $user = Auth::user();
        abort_unless(
            $message->sender_id === $user->id || $message->chat->participants()->where('user_id', $user->id)->where('role', 'admin')->exists(),
            403
        );

        $deleteType = $request->input('type', 'for_me');

        if ($deleteType === 'for_everyone' && $message->sender_id === $user->id) {
            abort_if($message->created_at->diffInMinutes(now()) > 60, 422, 'Delete for everyone window has expired.');
            $message->update(['is_deleted' => true, 'deleted_type' => 'for_everyone', 'body' => null]);
            broadcast(new MessageDeleted($message))->toOthers();
        } else {
            $message->delete();
        }

        return response()->json(['success' => true]);
    }

    public function react(Request $request, Message $message)
    {
        abort_unless(
            $message->chat->participants()->where('user_id', Auth::id())->whereNull('left_at')->exists(),
            403
        );

        $request->validate(['emoji' => 'required|string|max:20']);

        $existing = MessageReaction::where('message_id', $message->id)->where('user_id', Auth::id())->first();

        if ($existing && $existing->emoji === $request->emoji) {
            $existing->delete();
            $action = 'removed';
        } else {
            MessageReaction::updateOrCreate(
                ['message_id' => $message->id, 'user_id' => Auth::id()],
                ['emoji' => $request->emoji]
            );
            $action = 'added';
        }

        $message->load('reactions.user');
        broadcast(new MessageReacted($message, $request->emoji, $action))->toOthers();

        return response()->json(['reactions' => $message->reactions, 'action' => $action]);
    }

    public function typing(Chat $chat)
    {
        abort_unless(
            $chat->participants()->where('user_id', Auth::id())->whereNull('left_at')->exists(),
            403
        );

        broadcast(new TypingStarted(Auth::user(), $chat))->toOthers();

        return response()->json(['success' => true]);
    }

    public function markRead(Chat $chat)
    {
        $user = Auth::user();
        $pivot = $chat->participants()->where('user_id', $user->id)->first()?->pivot;

        if ($pivot) {
            \DB::table('chat_participants')
                ->where('chat_id', $chat->id)
                ->where('user_id', $user->id)
                ->update(['last_read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function pin(Message $message)
    {
        abort_unless(
            $message->chat->participants()->where('user_id', Auth::id())->whereIn('role', ['admin', 'owner'])->exists(),
            403
        );

        $message->update(['is_pinned' => !$message->is_pinned]);
        return response()->json(['is_pinned' => $message->is_pinned]);
    }

    public function forward(Request $request, Message $message)
    {
        $request->validate(['chat_ids' => 'required|array|max:5', 'chat_ids.*' => 'exists:chats,id']);

        $user = Auth::user();
        foreach ($request->chat_ids as $chatId) {
            $chat = Chat::find($chatId);
            if (!$chat || !$chat->participants()->where('user_id', $user->id)->whereNull('left_at')->exists()) continue;

            $forwarded = Message::create([
                'chat_id'      => $chatId,
                'sender_id'    => $user->id,
                'type'         => $message->type,
                'body'         => $message->body,
                'media_url'    => $message->media_url,
                'media_thumbnail' => $message->media_thumbnail,
                'media_mime_type' => $message->media_mime_type,
                'media_duration'  => $message->media_duration,
                'metadata'     => $message->metadata,
                'is_forwarded' => true,
            ]);

            $chat->update(['last_message_id' => $forwarded->id]);
            $forwarded->load('sender');
            broadcast(new MessageSent($forwarded))->toOthers();
        }

        return response()->json(['success' => true]);
    }
}
