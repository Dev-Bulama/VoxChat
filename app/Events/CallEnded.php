<?php

namespace App\Events;

use App\Models\Call;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Call $call, public string $reason = 'ended') {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->call->chat_id)];
    }

    public function broadcastAs(): string { return 'call.ended'; }

    public function broadcastWith(): array
    {
        return [
            'call_id'  => $this->call->id,
            'reason'   => $this->reason,
            'duration' => $this->call->duration,
        ];
    }
}
