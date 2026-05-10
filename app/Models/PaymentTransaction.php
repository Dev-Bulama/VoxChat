<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'user_id', 'subscription_id', 'gateway', 'transaction_id',
        'gateway_transaction_id', 'amount', 'currency', 'status',
        'payment_method', 'metadata', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'paid_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo         { return $this->belongsTo(User::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(UserSubscription::class); }
}
