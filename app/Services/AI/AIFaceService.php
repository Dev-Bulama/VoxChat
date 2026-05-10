<?php

namespace App\Services\AI;

use App\Models\AiProviderSetting;
use App\Models\Call;
use App\Models\User;
use App\Models\UserAiAvatar;

class AIFaceService
{
    private array $providers = [];

    public function __construct()
    {
        $this->providers = [
            'did'    => new DIDProvider(),
            'heygen' => new HeyGenProvider(),
            'tavus'  => new TavusProvider(),
            'simli'  => new SimliProvider(),
            'openai' => new OpenAIProvider(),
        ];
    }

    public function startFaceReplacement(Call $call, User $user, UserAiAvatar $avatar, string $providerName): array
    {
        $provider = $this->getProvider($providerName);

        $session = $provider->createLiveSession([
            'portrait_url' => $avatar->portrait_url_full,
            'face_url'     => $avatar->face_url ? asset('storage/' . $avatar->face_url) : null,
            'room_id'      => $call->room_id,
            'user_id'      => $user->id,
        ]);

        \App\Models\AiUsageLog::create([
            'user_id'  => $user->id,
            'call_id'  => $call->id,
            'provider' => $providerName,
            'feature'  => 'face_replacement',
            'status'   => 'success',
            'metadata' => $session,
        ]);

        return $session;
    }

    public function processAvatar(UserAiAvatar $avatar, string $providerName): array
    {
        $provider = $this->getProvider($providerName);

        $result = $provider->createAvatar([
            'portrait_url' => $avatar->portrait_url_full,
            'name'         => $avatar->name,
        ]);

        $avatar->update([
            'provider'             => $providerName,
            'provider_avatar_id'   => $result['id'] ?? null,
            'provider_metadata'    => $result,
            'is_processed'         => true,
            'processing_status'    => 'completed',
        ]);

        return $result;
    }

    private function getProvider(string $name): AIProviderInterface
    {
        if (!isset($this->providers[$name])) {
            throw new \InvalidArgumentException("Unknown AI provider: {$name}");
        }

        $setting = \App\Models\AiProviderSetting::where('provider', $name)->first();
        if ($setting && !$setting->is_enabled) {
            $fallback = $setting->fallback_provider;
            if ($fallback && isset($this->providers[$fallback])) {
                return $this->providers[$fallback];
            }
            throw new \RuntimeException("AI provider {$name} is disabled.");
        }

        return $this->providers[$name];
    }

    public function getDefaultProvider(): string
    {
        $default = \App\Models\AiProviderSetting::where('is_default', true)->where('is_enabled', true)->first();
        return $default?->provider ?? 'did';
    }
}
