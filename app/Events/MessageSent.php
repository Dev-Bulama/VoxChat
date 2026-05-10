<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('chat.' . $this->message->chat_id)];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->message->id,
            'chat_id'        => $this->message->chat_id,
            'sender_id'      => $this->message->sender_id,
            'type'           => $this->message->type,
            'body'           => $this->message->body,
            'body_preview'   => $this->message->body_preview,
            'media_url'      => $this->message->media_url_full,
            'media_thumbnail'=> $this->message->media_thumbnail,
            'media_duration' => $this->message->media_duration,
            'reply_to_id'    => $this->message->reply_to_id,
            'is_forwarded'   => $this->message->is_forwarded,
            'created_at'     => $this->message->created_at->toISOString(),
            'sender'         => [
                'id'         => $this->message->sender->id,
                'name'       => $this->message->sender->name,
                'username'   => $this->message->sender->username,
                'avatar_url' => $this->message->sender->avatar_url,
            ],
        ];
    }
}
