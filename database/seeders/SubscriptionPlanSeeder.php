<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug'          => 'free',
                'name'          => 'Free',
                'description'   => 'Perfect for getting started',
                'price_monthly' => 0,
                'price_yearly'  => 0,
                'currency'      => 'USD',
                'features'      => [
                    '1 GB storage',
                    'Up to 5 groups',
                    '50 members per group',
                    '100 voice call minutes/month',
                    '30 video call minutes/month',
                    'Basic messaging features',
                ],
                'limits'        => [
                    'storage_gb'        => 1,
                    'max_groups'        => 5,
                    'max_group_members' => 50,
                    'ai_calls'          => 0,
                    'voice_minutes'     => 100,
                    'video_minutes'     => 30,
                ],
                'color'         => '#6b7280',
                'is_popular'    => false,
                'is_active'     => true,
                'sort_order'    => 1,
            ],
            [
                'slug'          => 'premium',
                'name'          => 'Premium',
                'description'   => 'For power users and creators',
                'price_monthly' => 9.99,
                'price_yearly'  => 95.88,
                'currency'      => 'USD',
                'features'      => [
                    '25 GB storage',
                    'Up to 50 groups',
                    '500 members per group',
                    '1000 voice call minutes/month',
                    '500 video call minutes/month',
                    '300 AI face replacement minutes',
                    'Priority support',
                    'Verified badge',
                    'Custom status',
                    'Read receipts control',
                ],
                'limits'        => [
                    'storage_gb'        => 25,
                    'max_groups'        => 50,
                    'max_group_members' => 500,
                    'ai_calls'          => 300,
                    'voice_minutes'     => 1000,
                    'video_minutes'     => 500,
                ],
                'color'         => '#6366f1',
                'is_popular'    => true,
                'is_active'     => true,
                'sort_order'    => 2,
            ],
            [
                'slug'          => 'business',
                'name'          => 'Business',
                'description'   => 'For teams and businesses',
                'price_monthly' => 29.99,
                'price_yearly'  => 287.88,
                'currency'      => 'USD',
                'features'      => [
                    '100 GB storage',
                    'Unlimited groups',
                    'Unlimited group members',
                    'Unlimited voice & video calls',
                    'Unlimited AI face replacement',
                    'API access',
                    'Advanced analytics',
                    'Custom branding',
                    'Dedicated support',
                    'Admin dashboard access',
                ],
                'limits'        => [
                    'storage_gb'        => 100,
                    'max_groups'        => -1,
                    'max_group_members' => -1,
                    'ai_calls'          => -1,
                    'voice_minutes'     => -1,
                    'video_minutes'     => -1,
                ],
                'color'         => '#8b5cf6',
                'is_popular'    => false,
                'is_active'     => true,
                'sort_order'    => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
