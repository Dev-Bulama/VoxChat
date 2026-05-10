<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class DIDProvider implements AIProviderInterface
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.did.api_key', '');
        $this->apiUrl = config('services.did.api_url', 'https://api.d-id.com');
    }

    public function createLiveSession(array $params): array
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->apiUrl}/talks/streams", [
                'source_url' => $params['portrait_url'],
                'driver_url' => 'bank://lively',
                'config'     => [
                    'stitch' => true,
                    'result_format' => 'mp4',
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('D-ID API error: ' . $response->body());
        }

        return $response->json();
    }

    public function createAvatar(array $params): array
    {
        $response = Http::withToken($this->apiKey)
            ->post("{$this->apiUrl}/clips/actors", [
                'source_url' => $params['portrait_url'],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('D-ID avatar creation error: ' . $response->body());
        }

        return $response->json();
    }

    public function stopSession(string $sessionId): bool
    {
        $response = Http::withToken($this->apiKey)
            ->delete("{$this->apiUrl}/talks/streams/{$sessionId}");
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
