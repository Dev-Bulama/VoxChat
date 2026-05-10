<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['type', 'created_by', 'last_message_id'];

    protected function casts(): array
    {
        return [];
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants')
            ->withPivot(['role', 'is_muted', 'is_archived', 'is_pinned', 'last_read_at', 'notifications_enabled', 'left_at'])
            ->withTimestamps();
    }

    public function activeParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivotNull('left_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    public function group(): HasOne
    {
        return $this->hasOne(Group::class);
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getOtherParticipant(int $userId): ?User
    {
        return $this->participants->firstWhere('id', '!=', $userId);
    }

    public function getUnreadCountFor(int $userId): int
    {
        $participant = $this->participants->firstWhere('id', $userId);
        if (!$participant) return 0;

        $lastReadAt = $participant->pivot->last_read_at;
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->when($lastReadAt, fn($q) => $q->where('created_at', '>', $lastReadAt))
            ->count();
    }

    public function getDisplayNameFor(int $userId): string
    {
        if ($this->type === 'group') {
            return $this->group?->name ?? 'Group';
        }
        $other = $this->getOtherParticipant($userId);
        return $other?->name ?? 'Unknown';
    }

    public function getDisplayAvatarFor(int $userId): string
    {
        if ($this->type === 'group') {
            return $this->group?->avatar
                ? asset('storage/' . $this->group->avatar)
                : asset('images/group-placeholder.png');
        }
        $other = $this->getOtherParticipant($userId);
        return $other?->avatar_url ?? asset('images/user-placeholder.png');
    }
}
