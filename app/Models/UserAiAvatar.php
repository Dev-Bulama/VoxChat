<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAiAvatar extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'name', 'portrait_url', 'face_url', 'avatar_url',
        'provider', 'provider_avatar_id', 'provider_metadata',
        'is_active', 'is_processed', 'processing_status',
    ];

    protected function casts(): array
    {
        return [
            'provider_metadata' => 'array',
            'is_active'         => 'boolean',
            'is_processed'      => 'boolean',
        ];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getPortraitUrlFullAttribute(): ?string
    {
        if (!$this->portrait_url) return null;
        return str_starts_with($this->portrait_url, 'http')
            ? $this->portrait_url
            : asset('storage/' . $this->portrait_url);
    }
}
