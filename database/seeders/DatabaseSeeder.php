<?php

namespace Database\Seeders;

use App\Models\AiProviderSetting;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SubscriptionPlanSeeder::class,
            SystemSettingsSeeder::class,
            AiProviderSeeder::class,
        ]);

        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@voxchat.app'],
            [
                'name'              => 'VoxChat Admin',
                'username'          => 'admin',
                'email'             => 'admin@voxchat.app',
                'password'          => Hash::make('admin123!'),
                'is_admin'          => true,
                'is_verified'       => true,
                'email_verified_at' => now(),
                'subscription_plan' => 'business',
            ]
        );

        // Demo users
        if (app()->environment('local')) {
            User::factory()->count(20)->create();
        }
    }
}
