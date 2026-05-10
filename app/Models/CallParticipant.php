<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CallParticipant extends Model
{
    protected $fillable = [
        'call_id', 'user_id', 'status', 'is_muted', 'is_video_off',
        'is_screen_sharing', 'ai_face_enabled', 'ai_avatar_url', 'ai_provider',
        'joined_at', 'left_at',
    ];

    protected function casts(): array
    {
        return [
            'is_muted'          => 'boolean',
            'is_video_off'      => 'boolean',
            'is_screen_sharing' => 'boolean',
            'ai_face_enabled'   => 'boolean',
            'joined_at'         => 'datetime',
            'left_at'           => 'datetime',
        ];
    }

    public function call(): BelongsTo { return $this->belongsTo(Call::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
