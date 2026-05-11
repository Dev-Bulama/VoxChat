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
        // Broadcast on chat channel AND each callee's personal channel.
        // The callee may not have the chat open, so we must hit their private channel
        // to trigger the incoming-call modal wherever they are in the app.
        $channels = [new PrivateChannel('chat.' . $this->call->chat_id)];

        foreach ($this->call->participants as $participant) {
            if ($participant->user_id !== $this->call->initiated_by) {
                $channels[] = new PrivateChannel('user.' . $participant->user_id);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string { return 'call.initiated'; }

    public function broadcastWith(): array
    {
        return [
            'call_id' => $this->call->id,
            'room_id' => $this->call->room_id,
            'type'    => $this->call->type,
            // Key is "caller" to match the incoming-call modal and polling response
            'caller'  => [
                'id'         => $this->call->initiator->id,
                'name'       => $this->call->initiator->name,
                'avatar_url' => $this->call->initiator->avatar_url,
            ],
        ];
    }
}
