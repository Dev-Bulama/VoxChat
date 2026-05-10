<?php

namespace App\Console\Commands;

use App\Models\AiProviderSetting;
use Illuminate\Console\Command;

class ResetAiUsage extends Command
{
    protected $signature = 'voxchat:reset-ai-usage';
    protected $description = 'Reset daily AI usage counters';

    public function handle(): void
    {
        AiProviderSetting::query()->update(['daily_usage' => 0]);
        $this->info('Daily AI usage counters reset.');
    }
}
