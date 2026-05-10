<?php

namespace App\Console\Commands;

use App\Models\UserSubscription;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'voxchat:process-renewals';
    protected $description = 'Expire subscriptions past their end date';

    public function handle(): void
    {
        $expired = UserSubscription::where('status', 'active')
            ->where('auto_renew', false)
            ->where('current_period_end', '<', now())
            ->get();

        foreach ($expired as $subscription) {
            $subscription->update(['status' => 'expired']);
            $subscription->user()->update([
                'subscription_plan'    => 'free',
                'subscription_expires_at' => null,
            ]);
        }

        $this->info("Processed {$expired->count()} expired subscriptions.");
    }
}
