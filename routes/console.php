<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up expired stories
Schedule::command('voxchat:cleanup-stories')->hourly();

// Reset daily AI usage counters
Schedule::command('voxchat:reset-ai-usage')->dailyAt('00:00');

// Set users offline after 5 minutes of inactivity
Schedule::command('voxchat:update-presence')->everyFiveMinutes();

// Process subscription renewals
Schedule::command('voxchat:process-renewals')->daily();
