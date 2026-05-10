<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class HeyGenProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.heygen.api_key', '');
        $this->apiUrl = config('services.heygen.api_url', 'https://api.heygen.com/v2');
    }

    public function createLiveSession(array $params): array
    {
        $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
            ->post("{$this->apiUrl}/streaming.new", [
                'quality'   => 'high',
                'avatar_id' => $params['avatar_id'] ?? null,
                'voice'     => ['voice_id' => null],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('HeyGen API error: ' . $response->body());
        }

        return $response->json('data', []);
    }

    public function createAvatar(array $params): array
    {
        $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
            ->post("{$this->apiUrl}/avatar/create", [
                'type'        => 'photo',
                'source_urls' => [$params['portrait_url']],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('HeyGen avatar error: ' . $response->body());
        }

        return $response->json('data', []);
    }

    public function stopSession(string $sessionId): bool
    {
        $response = Http::withHeaders(['X-Api-Key' => $this->apiKey])
            ->post("{$this->apiUrl}/streaming.stop", ['session_id' => $sessionId]);
        return $response->successful();
    }

    public function getCapabilities(): array
    {
        return [
            'face_replacement' => true,
            'lip_sync'         => true,
            'head_movement'    => true,
            'expressions'      => true,
            'live_streaming'   => true,
            'voice_cloning'    => true,
        ];
    }
}
