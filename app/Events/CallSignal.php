<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallSignal implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Call   $call,
        public int    $fromUserId,
        public string $type,
        public mixed  $payload
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('call.' . $this->call->room_id)];
    }

    public function broadcastAs(): string { return 'call.signal'; }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->fromUserId,
            'type'    => $this->type,
            'payload' => $this->payload,
        ];
    }
}
