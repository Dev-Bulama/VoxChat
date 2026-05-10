<?php

use App\Models\Chat;
use Illuminate\Support\Facades\Broadcast;

// Private chat channel
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return Chat::find($chatId)?->participants()
        ->where('user_id', $user->id)
        ->whereNull('left_at')
        ->exists();
});

// User presence channel (online status)
Broadcast::channel('presence.global', function ($user) {
    return ['id' => $user->id, 'name' => $user->name, 'avatar' => $user->avatar_url];
});

// User private notification channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Call room channel
Broadcast::channel('call.{roomId}', function ($user, $roomId) {
    return \App\Models\Call::where('room_id', $roomId)
        ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
        ->exists();
});
