<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class TavusProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.tavus.api_key', '');
        $this->apiUrl = config('services.tavus.api_url', 'https://tavusapi.com/v2');
    }

    public function createLiveSession(array $params): array
    {
        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->post("{$this->apiUrl}/conversations", [
                'replica_id'   => $params['replica_id'] ?? null,
                'persona_id'   => $params['persona_id'] ?? null,
                'callback_url' => null,
                'conversation_name' => 'VoxChat Live ' . now()->timestamp,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Tavus API error: ' . $response->body());
        }

        return $response->json();
    }

    public function createAvatar(array $params): array
    {
        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->post("{$this->apiUrl}/replicas", [
                'train_video_url' => $params['portrait_url'],
                'replica_name'    => $params['name'] ?? 'VoxChat Avatar',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Tavus replica error: ' . $response->body());
        }

        return $response->json();
    }

    public function stopSession(string $sessionId): bool
    {
        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->delete("{$this->apiUrl}/conversations/{$sessionId}");
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
        ];
    }
}
