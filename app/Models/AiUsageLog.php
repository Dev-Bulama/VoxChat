<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id', 'call_id', 'provider', 'feature',
        'duration_seconds', 'cost', 'status', 'error_message', 'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function call(): BelongsTo { return $this->belongsTo(Call::class); }
}
