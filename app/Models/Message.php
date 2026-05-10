<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chat_id', 'sender_id', 'reply_to_id', 'type', 'body',
        'media_url', 'media_thumbnail', 'media_size', 'media_mime_type', 'media_duration',
        'metadata', 'is_edited', 'edited_at', 'is_deleted', 'deleted_type',
        'is_forwarded', 'is_pinned', 'is_starred', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata'    => 'array',
            'is_edited'   => 'boolean',
            'is_deleted'  => 'boolean',
            'is_forwarded'=> 'boolean',
            'is_pinned'   => 'boolean',
            'is_starred'  => 'boolean',
            'edited_at'   => 'datetime',
            'expires_at'  => 'datetime',
        ];
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MessageReceipt::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(MessageReaction::class);
    }

    public function getMediaUrlFullAttribute(): ?string
    {
        if (!$this->media_url) return null;
        return str_starts_with($this->media_url, 'http')
            ? $this->media_url
            : asset('storage/' . $this->media_url);
    }

    public function getBodyPreviewAttribute(): string
    {
        if ($this->is_deleted) return '🚫 This message was deleted';

        return match ($this->type) {
            'image'      => '📷 Photo',
            'video'      => '🎥 Video',
            'audio'      => '🎵 Audio',
            'voice_note' => '🎤 Voice message',
            'file'       => '📎 ' . ($this->metadata['filename'] ?? 'File'),
            'location'   => '📍 Location',
            'contact'    => '👤 Contact',
            'gif'        => '🎞️ GIF',
            'sticker'    => '😀 Sticker',
            'call_log'   => '📞 ' . ($this->metadata['call_type'] ?? 'Call'),
            default      => $this->body ?? '',
        };
    }

    public function getStatusForUser(int $userId): string
    {
        $receipt = $this->receipts->firstWhere('user_id', $userId);
        return $receipt?->status ?? 'sent';
    }

    public function scopeVisible($query)
    {
        return $query->where('is_deleted', false)->whereNull('deleted_at');
    }
}
