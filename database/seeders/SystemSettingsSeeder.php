<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site_name', 'value' => 'VoxChat', 'type' => 'string', 'group' => 'general', 'label' => 'Site Name', 'is_public' => true],
            ['key' => 'site_tagline', 'value' => 'Connect. Chat. Call. Anywhere.', 'type' => 'string', 'group' => 'general', 'label' => 'Site Tagline', 'is_public' => true],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'general', 'label' => 'Maintenance Mode'],
            ['key' => 'registration_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'general', 'label' => 'Allow Registrations'],
            ['key' => 'default_theme', 'value' => 'system', 'type' => 'string', 'group' => 'general', 'label' => 'Default Theme'],
            // Storage
            ['key' => 'storage_driver', 'value' => 'local', 'type' => 'string', 'group' => 'storage', 'label' => 'Storage Driver'],
            // AI
            ['key' => 'ai_face_replacement_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'ai', 'label' => 'AI Face Replacement'],
            ['key' => 'default_ai_provider', 'value' => 'did', 'type' => 'string', 'group' => 'ai', 'label' => 'Default AI Provider'],
            // Calling
            ['key' => 'calls_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'calls', 'label' => 'Enable Calls'],
            ['key' => 'default_call_provider', 'value' => 'webrtc', 'type' => 'string', 'group' => 'calls', 'label' => 'Default Call Provider'],
            // Notifications
            ['key' => 'push_notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications', 'label' => 'Push Notifications'],
            ['key' => 'email_notifications_enabled', 'value' => '1', 'type' => 'boolean', 'group' => 'notifications', 'label' => 'Email Notifications'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
