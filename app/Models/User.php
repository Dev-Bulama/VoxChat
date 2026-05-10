<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'password',
        'avatar', 'cover_photo', 'bio', 'status_message',
        'gender', 'birthday', 'country', 'timezone',
        'subscription_plan', 'subscription_expires_at',
        'is_verified', 'is_admin', 'is_banned', 'ban_reason', 'banned_at',
        'last_seen_at', 'is_online',
        'last_seen_privacy', 'profile_photo_privacy', 'about_privacy', 'status_privacy',
        'read_receipts_enabled', 'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes',
        'message_notifications', 'call_notifications', 'story_notifications',
        'group_notifications', 'email_notifications',
        'theme', 'language', 'font_size',
        'website', 'social_provider', 'social_id', 'social_token',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret',
        'two_factor_recovery_codes', 'social_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'phone_verified_at'          => 'datetime',
            'subscription_expires_at'    => 'datetime',
            'banned_at'                  => 'datetime',
            'last_seen_at'               => 'datetime',
            'birthday'                   => 'date',
            'is_verified'                => 'boolean',
            'is_admin'                   => 'boolean',
            'is_banned'                  => 'boolean',
            'is_online'                  => 'boolean',
            'read_receipts_enabled'      => 'boolean',
            'two_factor_enabled'         => 'boolean',
            'two_factor_recovery_codes'  => 'array',
            'message_notifications'      => 'boolean',
            'call_notifications'         => 'boolean',
            'story_notifications'        => 'boolean',
            'group_notifications'        => 'boolean',
            'email_notifications'        => 'boolean',
            'password'                   => 'hashed',
        ];
    }

    // ---- Relationships ----

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function chats(): BelongsToMany
    {
        return $this->belongsToMany(Chat::class, 'chat_participants')
            ->withPivot(['role', 'is_muted', 'is_archived', 'is_pinned', 'last_read_at', 'notifications_enabled'])
            ->withTimestamps();
    }

    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_id')
            ->withPivot(['nickname', 'is_blocked', 'is_muted', 'muted_until'])
            ->withTimestamps();
    }

    public function blockedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'contacts', 'contact_id', 'user_id')
            ->wherePivot('is_blocked', true);
    }

    public function stories(): HasMany
    {
        return $this->hasMany(Story::class);
    }

    public function activeStories(): HasMany
    {
        return $this->hasMany(Story::class)->where('expires_at', '>', now());
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'initiated_by');
    }

    public function subscription(): HasMany
    {
        return $this->hasMany(UserSubscription::class)->where('status', 'active');
    }

    public function aiAvatars(): HasMany
    {
        return $this->hasMany(UserAiAvatar::class);
    }

    public function activeAvatar()
    {
        return $this->hasOne(UserAiAvatar::class)->where('is_active', true);
    }

    // ---- Computed attributes ----

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return str_starts_with($this->avatar, 'http')
                ? $this->avatar
                : asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff&size=128';
    }

    public function getCoverPhotoUrlAttribute(): ?string
    {
        if ($this->cover_photo) {
            return str_starts_with($this->cover_photo, 'http')
                ? $this->cover_photo
                : asset('storage/' . $this->cover_photo);
        }
        return null;
    }

    public function getLastSeenFormattedAttribute(): string
    {
        if ($this->is_online) return 'online';
        if (!$this->last_seen_at) return 'recently';

        $diff = now()->diffInMinutes($this->last_seen_at);
        if ($diff < 1) return 'just now';
        if ($diff < 60) return "last seen {$diff}m ago";
        if ($diff < 1440) return 'last seen ' . $this->last_seen_at->format('H:i');
        return 'last seen ' . $this->last_seen_at->format('d M');
    }

    public function getIsPremiumAttribute(): bool
    {
        return in_array($this->subscription_plan, ['premium', 'business'])
            && ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture());
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    // ---- Scopes ----

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_banned', false)->whereNull('deleted_at');
    }
}
