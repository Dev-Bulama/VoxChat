<?php

namespace App\Events;

use App\Models\Call;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallMediaUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call, public User $user, public array $mediaState) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->call->chat_id)];
    }

    public function broadcastAs(): string { return 'call.media.updated'; }

    public function broadcastWith(): array
    {
        return [
            'call_id'    => $this->call->id,
            'user_id'    => $this->user->id,
            'media_state'=> $this->mediaState,
        ];
    }
}
