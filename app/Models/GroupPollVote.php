<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupPollVote extends Model
{
    protected $fillable = ['poll_id', 'user_id', 'selected_options'];

    protected function casts(): array
    {
        return ['selected_options' => 'array'];
    }

    public function poll(): BelongsTo { return $this->belongsTo(GroupPoll::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
