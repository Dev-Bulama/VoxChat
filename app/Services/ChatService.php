<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function findOrCreatePrivateChat(int $userId1, int $userId2): Chat
    {
        // Look for an existing private chat between these two users
        $existing = Chat::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId1)->whereNull('left_at'))
            ->whereHas('participants', fn($q) => $q->where('user_id', $userId2)->whereNull('left_at'))
            ->first();

        if ($existing) return $existing;

        return DB::transaction(function () use ($userId1, $userId2) {
            $chat = Chat::create(['type' => 'private', 'created_by' => $userId1]);

            $chat->participants()->attach([$userId1 => ['role' => 'member'], $userId2 => ['role' => 'member']]);

            return $chat;
        });
    }

    public function createGroupChat(int $creatorId, string $name, array $memberIds, ?UploadedFile $avatar = null): Chat
    {
        return DB::transaction(function () use ($creatorId, $name, $memberIds, $avatar) {
            $chat = Chat::create(['type' => 'group', 'created_by' => $creatorId]);

            $avatarPath = null;
            if ($avatar) {
                $avatarPath = $avatar->store('groups', 'public');
            }

            $group = Group::create([
                'chat_id'    => $chat->id,
                'created_by' => $creatorId,
                'name'       => $name,
                'slug'       => Str::slug($name) . '-' . Str::random(6),
                'avatar'     => $avatarPath,
            ]);

            $participants = [$creatorId => ['role' => 'owner']];
            foreach ($memberIds as $memberId) {
                if ($memberId !== $creatorId) {
                    $participants[$memberId] = ['role' => 'member'];
                }
            }

            $chat->participants()->attach($participants);

            return $chat;
        });
    }

    public function markChatAsRead(Chat $chat, int $userId): void
    {
        DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now(), 'last_read_message_id' => $chat->last_message_id]);
    }

    public function toggleArchive(Chat $chat, int $userId): void
    {
        $pivot = DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->first();

        DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->update(['is_archived' => !$pivot->is_archived]);
    }

    public function togglePin(Chat $chat, int $userId): void
    {
        $pivot = DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->first();

        DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->update(['is_pinned' => !$pivot->is_pinned]);
    }

    public function mute(Chat $chat, int $userId, ?int $hours = null): void
    {
        $mutedUntil = $hours ? now()->addHours($hours) : null;

        DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->update([
                'is_muted'    => true,
                'muted_until' => $mutedUntil,
            ]);
    }

    public function deleteChatForUser(Chat $chat, int $userId): void
    {
        DB::table('chat_participants')
            ->where('chat_id', $chat->id)
            ->where('user_id', $userId)
            ->update(['left_at' => now()]);
    }
}
