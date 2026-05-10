<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    public function createLiveSession(array $params): array;
    public function createAvatar(array $params): array;
    public function stopSession(string $sessionId): bool;
    public function getCapabilities(): array;
}
