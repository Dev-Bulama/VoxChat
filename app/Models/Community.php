<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Community extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by', 'name', 'slug', 'description', 'avatar', 'cover_photo',
        'invite_link', 'is_public', 'members_count',
    ];

    protected function casts(): array
    {
        return ['is_public' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Community $community) {
            if (!$community->slug) {
                $community->slug = Str::slug($community->name) . '-' . Str::random(6);
            }
            if (!$community->invite_link) {
                $community->invite_link = Str::random(20);
            }
        });
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'community_members')
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) return asset('storage/' . $this->avatar);
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=10b981&color=fff&size=128';
    }
}
