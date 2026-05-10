<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class OpenAIProvider implements AIProviderInterface
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key', '');
    }

    public function createLiveSession(array $params): array
    {
        // OpenAI Realtime API for voice/video
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/realtime/sessions', [
                'model'          => 'gpt-4o-realtime-preview',
                'voice'          => 'alloy',
                'modalities'     => ['audio', 'text'],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        return $response->json();
    }

    public function createAvatar(array $params): array
    {
        // Use DALL-E to generate an avatar
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/images/generations', [
                'prompt' => 'Professional avatar portrait based on this description: ' . ($params['name'] ?? 'person'),
                'model'  => 'dall-e-3',
                'n'      => 1,
                'size'   => '1024x1024',
                'quality'=> 'standard',
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI image error: ' . $response->body());
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
            'face_replacement' => false,
            'lip_sync'         => false,
            'avatar_generation'=> true,
            'voice_cloning'    => true,
            'live_streaming'   => true,
        ];
    }
}
