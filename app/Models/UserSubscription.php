<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id', 'plan_id', 'billing_cycle', 'status', 'payment_gateway',
        'gateway_subscription_id', 'gateway_customer_id',
        'trial_ends_at', 'current_period_start', 'current_period_end',
        'auto_renew', 'cancelled_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at'         => 'datetime',
            'current_period_start'  => 'datetime',
            'current_period_end'    => 'datetime',
            'cancelled_at'          => 'datetime',
            'ends_at'               => 'datetime',
            'auto_renew'            => 'boolean',
        ];
    }

    public function user(): BelongsTo         { return $this->belongsTo(User::class); }
    public function plan(): BelongsTo         { return $this->belongsTo(SubscriptionPlan::class); }
    public function transactions(): HasMany   { return $this->hasMany(PaymentTransaction::class, 'subscription_id'); }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->current_period_end->isFuture();
    }
}
