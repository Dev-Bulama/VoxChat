<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiProviderSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ApiConfigController extends Controller
{
    public function index()
    {
        $rawProviders = AiProviderSetting::orderBy('priority')->get()->keyBy('provider');

        // Decrypt api_key in each provider's api_config for display
        $providers = $rawProviders->map(function ($setting) {
            $config = $setting->api_config ?? [];
            foreach ($config as $k => $v) {
                if (str_contains($k, 'key') || str_contains($k, 'secret') || str_contains($k, 'token')) {
                    try { $config[$k] = Crypt::decryptString($v); } catch (\Throwable $e) { /* already plain */ }
                }
            }
            $setting->api_config = $config;
            return $setting;
        });

        $availableProviders = config('voxchat.ai_providers', []);
        $callProviders      = config('voxchat.call_providers', []);

        return view('admin.api-config', compact('providers', 'availableProviders', 'callProviders'));
    }

    public function update(Request $request, string $provider)
    {
        $request->validate([
            'is_enabled'      => 'boolean',
            'is_default'      => 'boolean',
            'monthly_limit'   => 'nullable|integer|min:0',
            'daily_limit'     => 'nullable|integer|min:0',
            'priority'        => 'integer|min:0',
            'fallback_provider' => 'nullable|string',
            'api_config'      => 'nullable|array',
        ]);

        // Encrypt sensitive fields
        $apiConfig = $request->api_config ?? [];
        foreach ($apiConfig as $k => $v) {
            if ($v !== '' && (str_contains($k, 'key') || str_contains($k, 'secret') || str_contains($k, 'token'))) {
                $apiConfig[$k] = Crypt::encryptString($v);
            }
        }
        // Remove blank values so they don't overwrite existing encrypted values
        $apiConfig = array_filter($apiConfig, fn($v) => $v !== '');

        // Merge with existing config so non-submitted fields are preserved
        $existing = AiProviderSetting::where('provider', $provider)->first();
        if ($existing && $existing->api_config) {
            $apiConfig = array_merge($existing->api_config, $apiConfig);
        }

        $providerName = config("voxchat.ai_providers.{$provider}.name")
            ?? config("voxchat.call_providers.{$provider}.name")
            ?? ucfirst($provider);

        AiProviderSetting::updateOrCreate(
            ['provider' => $provider],
            [
                'name'             => $providerName,
                'is_enabled'       => $request->boolean('is_enabled'),
                'is_default'       => $request->boolean('is_default'),
                'monthly_limit'    => $request->monthly_limit,
                'daily_limit'      => $request->daily_limit,
                'priority'         => $request->priority ?? 0,
                'fallback_provider'=> $request->fallback_provider,
                'api_config'       => $apiConfig ?: null,
            ]
        );

        // If this is set as default, unset others
        if ($request->boolean('is_default')) {
            AiProviderSetting::where('provider', '!=', $provider)->update(['is_default' => false]);
        }

        \App\Models\AdminLog::create([
            'admin_id'     => auth()->id(),
            'action'       => 'api_config_updated',
            'subject_type' => 'ai_provider',
            'new_values'   => ['provider' => $provider, 'enabled' => $request->boolean('is_enabled')],
        ]);

        return back()->with('success', "API configuration for {$provider} updated.");
    }
}
