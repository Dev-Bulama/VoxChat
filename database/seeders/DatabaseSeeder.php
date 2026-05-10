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

        // Named test users (always created — useful for testing on any environment)
        $testUsers = [
            [
                'name'              => 'Alice Johnson',
                'username'          => 'alice',
                'email'             => 'alice@voxchat.app',
                'password'          => Hash::make('password'),
                'subscription_plan' => 'premium',
                'bio'               => 'Premium user for testing.',
                'status_message'    => 'Hey there!',
                'is_verified'       => true,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Bob Smith',
                'username'          => 'bob',
                'email'             => 'bob@voxchat.app',
                'password'          => Hash::make('password'),
                'subscription_plan' => 'free',
                'bio'               => 'Free user for testing.',
                'status_message'    => 'Available',
                'is_verified'       => false,
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Carol White',
                'username'          => 'carol',
                'email'             => 'carol@voxchat.app',
                'password'          => Hash::make('password'),
                'subscription_plan' => 'business',
                'bio'               => 'Business user for testing.',
                'status_message'    => 'In a meeting',
                'is_verified'       => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($testUsers as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }

        // Extra random users in local env
        if (app()->environment('local')) {
            User::factory()->count(20)->create();
        }
    }
}
