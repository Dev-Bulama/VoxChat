<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupPoll extends Model
{
    protected $fillable = [
        'group_id', 'chat_id', 'message_id', 'created_by',
        'question', 'options', 'is_multiple_choice', 'is_anonymous', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'options'          => 'array',
            'is_multiple_choice' => 'boolean',
            'is_anonymous'     => 'boolean',
            'expires_at'       => 'datetime',
        ];
    }

    public function group(): BelongsTo   { return $this->belongsTo(Group::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function votes(): HasMany     { return $this->hasMany(GroupPollVote::class, 'poll_id'); }
}
