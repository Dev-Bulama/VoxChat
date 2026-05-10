<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateUserPresence extends Command
{
    protected $signature = 'voxchat:update-presence';
    protected $description = 'Mark users as offline if last seen more than 10 minutes ago';

    public function handle(): void
    {
        $updated = User::where('is_online', true)
            ->where('last_seen_at', '<', now()->subMinutes(10))
            ->update(['is_online' => false]);

        $this->info("Marked {$updated} users as offline.");
    }
}
