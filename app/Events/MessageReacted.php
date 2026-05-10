<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public string $emoji,
        public string $action
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->message->chat_id)];
    }

    public function broadcastAs(): string { return 'message.reacted'; }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'emoji'      => $this->emoji,
            'action'     => $this->action,
            'reactions'  => $this->message->reactions,
        ];
    }
}
