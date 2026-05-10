<?php

namespace Database\Seeders;

use App\Models\AiProviderSetting;
use Illuminate\Database\Seeder;

class AiProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            ['provider' => 'did',    'name' => 'D-ID',    'priority' => 1, 'is_default' => true,  'capabilities' => ['face_replacement', 'lip_sync', 'head_movement', 'expressions']],
            ['provider' => 'heygen', 'name' => 'HeyGen',  'priority' => 2, 'is_default' => false, 'capabilities' => ['face_replacement', 'lip_sync', 'voice_cloning']],
            ['provider' => 'tavus',  'name' => 'Tavus',   'priority' => 3, 'is_default' => false, 'capabilities' => ['face_replacement', 'lip_sync']],
            ['provider' => 'simli',  'name' => 'Simli AI','priority' => 4, 'is_default' => false, 'capabilities' => ['face_replacement', 'lip_sync', 'real_time']],
            ['provider' => 'openai', 'name' => 'OpenAI',  'priority' => 5, 'is_default' => false, 'capabilities' => ['avatar_generation', 'voice_cloning']],
        ];

        foreach ($providers as $provider) {
            AiProviderSetting::firstOrCreate(
                ['provider' => $provider['provider']],
                array_merge($provider, ['is_enabled' => false, 'monthly_limit' => null])
            );
        }
    }
}
