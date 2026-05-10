<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class SimliProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.simli.api_key', '');
        $this->apiUrl = config('services.simli.api_url', 'https://api.simli.ai');
    }

    public function createLiveSession(array $params): array
    {
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
            ->post("{$this->apiUrl}/startAudioToVideoSession", [
                'face_id'        => $params['face_id'] ?? null,
                'isJPG'          => true,
                'syncAudio'      => true,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Simli API error: ' . $response->body());
        }

        return $response->json();
    }

    public function createAvatar(array $params): array
    {
        $response = Http::withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
            ->post("{$this->apiUrl}/faces", [
                'imageUrl' => $params['portrait_url'],
                'name'     => $params['name'] ?? 'VoxChat Face',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Simli face creation error: ' . $response->body());
        }

        return $response->json();
    }

    public function stopSession(string $sessionId): bool
    {
        return true;
    }

    public function getCapabilities(): array
    {
        return [
            'face_replacement' => true,
            'lip_sync'         => true,
            'head_movement'    => false,
            'expressions'      => true,
            'live_streaming'   => true,
            'real_time'        => true,
        ];
    }
}
