<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->call->chat_id)];
    }

    public function broadcastAs(): string { return 'call.initiated'; }

    public function broadcastWith(): array
    {
        return [
            'call_id'     => $this->call->id,
            'room_id'     => $this->call->room_id,
            'type'        => $this->call->type,
            'initiator'   => [
                'id'         => $this->call->initiator->id,
                'name'       => $this->call->initiator->name,
                'avatar_url' => $this->call->initiator->avatar_url,
            ],
        ];
    }
}
