<?php

namespace App\Http\Controllers;

use App\Events\CallInitiated;
use App\Events\CallAnswered;
use App\Events\CallEnded;
use App\Events\CallSignal;
use App\Models\Call;
use App\Models\CallParticipant;
use App\Models\Chat;
use App\Services\Call\CallService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    public function __construct(private CallService $callService) {}

    public function index()
    {
        $user = Auth::user();

        $callLogs = Call::query()
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id))
            ->with(['initiator', 'participants.user', 'chat'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('calls.index', compact('callLogs'));
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'chat_id'  => 'required|exists:chats,id',
            'type'     => 'required|in:voice,video',
            'provider' => 'nullable|string|in:webrtc,agora,livekit,twilio',
        ]);

        $user = Auth::user();
        $chat = Chat::findOrFail($request->chat_id);

        abort_unless($chat->participants()->where('user_id', $user->id)->whereNull('left_at')->exists(), 403);

        $call = $this->callService->initiateCall($user, $chat, $request->type, $request->provider ?? 'webrtc');

        broadcast(new CallInitiated($call))->toOthers();

        return response()->json([
            'call'      => $call->load('participants.user'),
            'room_id'   => $call->room_id,
            'token'     => $this->callService->generateToken($call, $user),
        ]);
    }

    public function pending()
    {
        $user = Auth::user();
        $call = Call::where('status', 'ringing')
            ->whereHas('participants', fn($q) => $q->where('user_id', $user->id)->where('status', 'ringing'))
            ->with(['participants.user', 'chat'])
            ->latest()
            ->first();

        if (!$call) return response()->json(['call' => null]);

        $caller = $call->participants->firstWhere('user_id', '!=', $user->id);

        return response()->json([
            'call' => [
                'call_id'  => $call->id,
                'type'     => $call->type,
                'room_id'  => $call->room_id,
                'caller'   => [
                    'name'       => $caller?->user?->name ?? 'Unknown',
                    'avatar_url' => $caller?->user?->avatar_url ?? '',
                ],
            ],
        ]);
    }

    public function join(Call $call)
    {
        $user = Auth::user();

        abort_unless($call->isActive(), 422, 'This call is no longer active.');
        abort_unless($call->chat->participants()->where('user_id', $user->id)->whereNull('left_at')->exists(), 403);

        $this->callService->joinCall($call, $user);

        return response()->json([
            'call'  => $call->load('participants.user'),
            'token' => $this->callService->generateToken($call, $user),
        ]);
    }

    public function answer(Call $call)
    {
        $participant = CallParticipant::where('call_id', $call->id)->where('user_id', Auth::id())->firstOrFail();
        $participant->update(['status' => 'joined', 'joined_at' => now()]);

        if ($call->status === 'ringing') {
            $call->update(['status' => 'ongoing', 'started_at' => now()]);
        }

        broadcast(new CallAnswered($call, Auth::user()))->toOthers();

        return response()->json(['success' => true]);
    }

    public function reject(Call $call)
    {
        $participant = CallParticipant::where('call_id', $call->id)->where('user_id', Auth::id())->firstOrFail();
        $participant->update(['status' => 'rejected', 'left_at' => now()]);

        broadcast(new CallEnded($call, 'rejected'))->toOthers();

        return response()->json(['success' => true]);
    }

    public function end(Call $call)
    {
        $user = Auth::user();

        abort_unless(
            $call->initiated_by === $user->id ||
            $call->participants()->where('user_id', $user->id)->where('status', 'joined')->exists(),
            403
        );

        $this->callService->endCall($call, $user->id);

        broadcast(new CallEnded($call, 'ended'))->toOthers();

        return response()->json(['success' => true, 'duration' => $call->fresh()->duration]);
    }

    public function showRoom(Call $call)
    {
        $user = Auth::user();

        abort_unless($call->chat->participants()->where('user_id', $user->id)->whereNull('left_at')->exists(), 403);

        $token = $this->callService->generateToken($call, $user);

        return view('calls.room', compact('call', 'token'));
    }

    public function signal(Request $request, Call $call)
    {
        $request->validate([
            'type'    => 'required|string|in:offer,answer,ice-candidate,call-ended,callee-ready',
            'payload' => 'required',
        ]);

        abort_unless(
            $call->participants()->where('user_id', Auth::id())->exists(),
            403
        );

        broadcast(new CallSignal($call, Auth::id(), $request->type, $request->payload))->toOthers();

        return response()->json(['success' => true]);
    }

    public function updateMedia(Request $request, Call $call)
    {
        $request->validate([
            'is_muted'          => 'boolean',
            'is_video_off'      => 'boolean',
            'is_screen_sharing' => 'boolean',
        ]);

        CallParticipant::where('call_id', $call->id)
            ->where('user_id', Auth::id())
            ->update($request->only(['is_muted', 'is_video_off', 'is_screen_sharing']));

        broadcast(new \App\Events\CallMediaUpdated($call, Auth::user(), $request->all()))->toOthers();

        return response()->json(['success' => true]);
    }

    public function enableAiFace(Request $request, Call $call)
    {
        $request->validate([
            'provider'   => 'required|string',
            'avatar_id'  => 'nullable|exists:user_ai_avatars,id',
        ]);

        $user = Auth::user();
        abort_if(!$user->is_premium, 402, 'AI face replacement requires a premium subscription.');

        $result = $this->callService->enableAiFaceReplacement($call, $user, $request->provider, $request->avatar_id);

        return response()->json($result);
    }
}
