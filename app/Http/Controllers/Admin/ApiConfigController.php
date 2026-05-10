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
        $providers = AiProviderSetting::orderBy('priority')->get()->keyBy('provider');

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

        // Encrypt sensitive API keys
        $apiConfig = $request->api_config ?? [];
        if (isset($apiConfig['api_key'])) {
            $apiConfig['api_key'] = Crypt::encryptString($apiConfig['api_key']);
        }

        AiProviderSetting::updateOrCreate(
            ['provider' => $provider],
            [
                'name'             => config("voxchat.ai_providers.{$provider}.name", ucfirst($provider)),
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
