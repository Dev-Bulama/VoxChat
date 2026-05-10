<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Call extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_id', 'chat_id', 'initiated_by', 'type', 'status',
        'provider', 'provider_room_id', 'duration', 'started_at', 'ended_at',
        'is_group_call', 'is_recorded', 'recording_url',
        'ai_face_enabled', 'ai_provider', 'quality_stats',
    ];

    protected function casts(): array
    {
        return [
            'started_at'      => 'datetime',
            'ended_at'        => 'datetime',
            'is_group_call'   => 'boolean',
            'is_recorded'     => 'boolean',
            'ai_face_enabled' => 'boolean',
            'quality_stats'   => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Call $call) {
            if (!$call->room_id) {
                $call->room_id = 'room_' . Str::random(24);
            }
        });
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CallParticipant::class);
    }

    public function joinedParticipants(): HasMany
    {
        return $this->hasMany(CallParticipant::class)->where('status', 'joined');
    }

    public function getDurationFormattedAttribute(): string
    {
        if (!$this->duration) return '0:00';
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['ringing', 'ongoing']);
    }
}
