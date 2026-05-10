<?php

namespace App\Console\Commands;

use App\Models\Story;
use Illuminate\Console\Command;

class CleanupStories extends Command
{
    protected $signature = 'voxchat:cleanup-stories';
    protected $description = 'Delete expired stories older than 24 hours';

    public function handle(): void
    {
        $deleted = Story::where('expires_at', '<', now())->delete();
        $this->info("Cleaned up {$deleted} expired stories.");
    }
}
