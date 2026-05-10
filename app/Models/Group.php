<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'chat_id', 'created_by', 'name', 'slug', 'description',
        'avatar', 'cover_photo', 'invite_link', 'is_public', 'max_members',
        'only_admins_can_send', 'only_admins_can_edit_info', 'only_admins_can_add_members',
        'approval_required', 'voice_calls_enabled', 'video_calls_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_public'                    => 'boolean',
            'only_admins_can_send'         => 'boolean',
            'only_admins_can_edit_info'    => 'boolean',
            'only_admins_can_add_members'  => 'boolean',
            'approval_required'            => 'boolean',
            'voice_calls_enabled'          => 'boolean',
            'video_calls_enabled'          => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Group $group) {
            if (!$group->slug) {
                $group->slug = Str::slug($group->name) . '-' . Str::random(6);
            }
            if (!$group->invite_link) {
                $group->invite_link = Str::random(20);
            }
        });
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(GroupPoll::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=8b5cf6&color=fff&size=128';
    }

    public function getMemberCountAttribute(): int
    {
        return $this->chat->activeParticipants()->count();
    }

    public function regenerateInviteLink(): string
    {
        $this->invite_link = Str::random(20);
        $this->save();
        return $this->invite_link;
    }
}
