<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug', 'name', 'description', 'price_monthly', 'price_yearly', 'currency',
        'features', 'limits', 'stripe_monthly_price_id', 'stripe_yearly_price_id',
        'color', 'badge_icon', 'is_popular', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features'   => 'array',
            'limits'     => 'array',
            'is_popular' => 'boolean',
            'is_active'  => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function getActiveMembersCountAttribute(): int
    {
        return $this->subscriptions()->where('status', 'active')->count();
    }
}
