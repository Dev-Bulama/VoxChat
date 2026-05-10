<?php

namespace App\Services\Call;

use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Chat;
use App\Models\User;
use App\Models\UserAiAvatar;
use App\Services\AI\AIFaceService;

class CallService
{
    public function __construct(private AIFaceService $aiFaceService) {}

    public function initiateCall(User $user, Chat $chat, string $type, string $provider = 'webrtc'): Call
    {
        $call = Call::create([
            'chat_id'      => $chat->id,
            'initiated_by' => $user->id,
            'type'         => $type,
            'status'       => 'ringing',
            'provider'     => $provider,
            'is_group_call'=> $chat->type === 'group',
        ]);

        // Add initiator
        CallParticipant::create([
            'call_id'  => $call->id,
            'user_id'  => $user->id,
            'status'   => 'joined',
            'joined_at'=> now(),
        ]);

        // Add other participants as ringing
        $otherParticipants = $chat->activeParticipants()->where('users.id', '!=', $user->id)->get();
        foreach ($otherParticipants as $participant) {
            CallParticipant::create([
                'call_id' => $call->id,
                'user_id' => $participant->id,
                'status'  => 'ringing',
            ]);
        }

        return $call;
    }

    public function joinCall(Call $call, User $user): void
    {
        CallParticipant::updateOrCreate(
            ['call_id' => $call->id, 'user_id' => $user->id],
            ['status' => 'joined', 'joined_at' => now()]
        );

        if ($call->status === 'ringing') {
            $call->update(['status' => 'ongoing', 'started_at' => now()]);
        }
    }

    public function endCall(Call $call, int $endedByUserId): void
    {
        $startedAt = $call->started_at ?? now();
        $duration  = (int) $startedAt->diffInSeconds(now());

        $call->update([
            'status'   => 'ended',
            'ended_at' => now(),
            'duration' => $duration,
        ]);

        CallParticipant::where('call_id', $call->id)
            ->where('status', 'joined')
            ->update(['status' => 'left', 'left_at' => now()]);

        CallParticipant::where('call_id', $call->id)
            ->where('status', 'ringing')
            ->update(['status' => 'missed']);
    }

    public function generateToken(Call $call, User $user): string
    {
        return match ($call->provider) {
            'agora'   => $this->generateAgoraToken($call, $user),
            'livekit' => $this->generateLiveKitToken($call, $user),
            'twilio'  => $this->generateTwilioToken($call, $user),
            default   => $this->generateWebRTCToken($call, $user),
        };
    }

    private function generateWebRTCToken(Call $call, User $user): string
    {
        // Generate a signed JWT token for internal WebRTC signaling
        return base64_encode(json_encode([
            'room'      => $call->room_id,
            'user_id'   => $user->id,
            'username'  => $user->username,
            'expires'   => now()->addHours(4)->timestamp,
        ]));
    }

    private function generateAgoraToken(Call $call, User $user): string
    {
        // Agora RTC Token generation would use the Agora PHP SDK
        return 'agora_token_placeholder_' . $call->room_id;
    }

    private function generateLiveKitToken(Call $call, User $user): string
    {
        return 'livekit_token_placeholder_' . $call->room_id;
    }

    private function generateTwilioToken(Call $call, User $user): string
    {
        return 'twilio_token_placeholder_' . $call->room_id;
    }

    public function enableAiFaceReplacement(Call $call, User $user, string $provider, ?int $avatarId): array
    {
        $avatar = $avatarId ? UserAiAvatar::where('id', $avatarId)->where('user_id', $user->id)->first() : $user->activeAvatar;

        if (!$avatar) {
            return ['success' => false, 'error' => 'No AI avatar found. Please upload one first.'];
        }

        try {
            $result = $this->aiFaceService->startFaceReplacement($call, $user, $avatar, $provider);

            CallParticipant::where('call_id', $call->id)
                ->where('user_id', $user->id)
                ->update([
                    'ai_face_enabled' => true,
                    'ai_avatar_url'   => $avatar->avatar_url,
                    'ai_provider'     => $provider,
                ]);

            return ['success' => true, 'session' => $result];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
