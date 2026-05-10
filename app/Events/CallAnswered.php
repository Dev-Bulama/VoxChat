<?php

namespace App\Events;

use App\Models\Call;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call, public User $user) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->call->chat_id)];
    }

    public function broadcastAs(): string { return 'call.answered'; }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->call->id,
            'user_id' => $this->user->id,
        ];
    }
}
