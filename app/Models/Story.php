<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'type', 'content', 'media_url', 'thumbnail_url', 'duration',
        'background_color', 'text_color', 'font_style', 'link',
        'music_url', 'music_title', 'music_artist',
        'privacy', 'allowed_viewers', 'excluded_viewers', 'views_count', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'allowed_viewers'  => 'array',
            'excluded_viewers' => 'array',
            'expires_at'       => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(StoryView::class);
    }

    public function getMediaUrlFullAttribute(): ?string
    {
        if (!$this->media_url) return null;
        return str_starts_with($this->media_url, 'http')
            ? $this->media_url
            : asset('storage/' . $this->media_url);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasViewedBy(int $userId): bool
    {
        return $this->views()->where('viewer_id', $userId)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('privacy', 'everyone')
              ->orWhere('user_id', $userId);
        });
    }
}
