<?php

namespace App\Events;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TypingStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public Chat $chat) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->chat->id)];
    }

    public function broadcastAs(): string { return 'typing.started'; }

    public function broadcastWith(): array
    {
        return [
            'user_id'  => $this->user->id,
            'username' => $this->user->username,
            'name'     => $this->user->name,
        ];
    }
}
