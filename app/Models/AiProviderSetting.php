<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProviderSetting extends Model
{
    protected $fillable = [
        'provider', 'name', 'is_enabled', 'is_default',
        'api_config', 'monthly_limit', 'monthly_usage',
        'daily_limit', 'daily_usage', 'usage_reset_at',
        'capabilities', 'priority', 'fallback_provider',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled'   => 'boolean',
            'is_default'   => 'boolean',
            'api_config'   => 'array',
            'capabilities' => 'array',
            'usage_reset_at' => 'datetime',
        ];
    }
}
