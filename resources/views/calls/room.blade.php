<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($call->type) }} Call — VoxChat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: { 500:'#6366f1' } } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #000; }
        #local-video, #remote-video { object-fit: cover; }
        .control-btn { width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s; }
        .control-btn:hover { transform: scale(1.05); }
        .glass-dark { background: rgba(0,0,0,0.5); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); }
    </style>
</head>
<body class="h-full bg-gray-950 overflow-hidden" x-data="videoCall({{ $call->id }}, '{{ $call->room_id }}', {{ auth()->id() }}, '{{ $token }}')" x-init="init()">

{{-- Remote video (full screen) --}}
<div class="relative w-full h-full">
    <video id="remote-video" class="w-full h-full object-cover bg-gray-900" autoplay playsinline></video>

    {{-- Call type indicator --}}
    @if($call->type === 'voice')
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        @php $other = $call->participants->firstWhere('user_id', '!=', auth()->id()); @endphp
        <div class="relative mb-6">
            <img src="{{ $other?->user?->avatar_url ?? asset('images/user-placeholder.png') }}" class="w-32 h-32 rounded-full object-cover ring-4 ring-white/20">
            <div class="absolute inset-0 rounded-full animate-ping bg-white/10"></div>
        </div>
        <h2 class="text-2xl font-bold text-white">{{ $other?->user?->name ?? 'Unknown' }}</h2>
        <p class="text-white/60 mt-1" x-text="statusLabel">Connecting...</p>
        <p class="text-white/80 text-lg font-mono mt-3" x-text="duration" x-show="callActive">00:00</p>
    </div>
    @endif

    {{-- Local video (picture-in-picture) --}}
    @if($call->type === 'video')
    <div class="absolute top-4 right-4 w-32 h-48 rounded-2xl overflow-hidden shadow-xl border-2 border-white/20 cursor-pointer"
         x-show="!localVideoOff" @click="swapVideos()">
        <video id="local-video" class="w-full h-full object-cover bg-gray-800" autoplay playsinline muted></video>
        <div x-show="localVideoOff" class="absolute inset-0 bg-gray-800 flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
        </div>
    </div>
    @endif

    {{-- Top bar --}}
    <div class="absolute top-0 left-0 right-0 p-4 glass-dark">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @php $other = $call->participants->firstWhere('user_id', '!=', auth()->id()); @endphp
                <img src="{{ $other?->user?->avatar_url ?? asset('images/user-placeholder.png') }}" class="w-9 h-9 rounded-full object-cover">
                <div>
                    <p class="text-white font-semibold text-sm">{{ $other?->user?->name ?? 'Unknown' }}</p>
                    <p class="text-white/60 text-xs" x-text="duration">00:00</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {{-- Quality indicator --}}
                <div class="flex items-end gap-0.5">
                    <div class="w-1 h-2 bg-green-400 rounded-sm"></div>
                    <div class="w-1 h-3 bg-green-400 rounded-sm"></div>
                    <div class="w-1 h-4 bg-green-400 rounded-sm"></div>
                </div>
                {{-- Encryption lock --}}
                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
            </div>
        </div>
    </div>

    {{-- Bottom controls --}}
    <div class="absolute bottom-0 left-0 right-0 p-6 glass-dark">

        {{-- AI Face Replacement Toggle --}}
        <div class="flex items-center justify-center mb-4">
            <button @click="toggleAiFace()"
                    :class="aiFaceEnabled ? 'bg-purple-500 text-white' : 'bg-white/20 text-white'"
                    class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="aiFaceEnabled ? 'AI Face: ON' : 'AI Face'"></span>
            </button>
        </div>

        <div class="flex items-center justify-center gap-4">
            {{-- Mute --}}
            <button @click="toggleMute()"
                    :class="isMuted ? 'bg-red-500' : 'bg-white/20'"
                    class="control-btn text-white">
                <svg x-show="!isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                <svg x-show="isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
            </button>

            {{-- End Call --}}
            <button @click="endCall()" class="control-btn bg-red-500 text-white w-16 h-16 shadow-xl">
                <svg class="w-7 h-7 rotate-135" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
            </button>

            {{-- Camera toggle (video calls) --}}
            @if($call->type === 'video')
            <button @click="toggleVideo()"
                    :class="localVideoOff ? 'bg-red-500' : 'bg-white/20'"
                    class="control-btn text-white">
                <svg x-show="!localVideoOff" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <svg x-show="localVideoOff" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
            </button>

            {{-- Screen share --}}
            <button @click="toggleScreenShare()"
                    :class="isScreenSharing ? 'bg-green-500' : 'bg-white/20'"
                    class="control-btn text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </button>
            @else
            {{-- Speaker --}}
            <button @click="toggleSpeaker()"
                    :class="speakerOn ? 'bg-white/20' : 'bg-white/10'"
                    class="control-btn text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3-9.5a7 7 0 000 7M6.343 6.343A9.96 9.96 0 004 12a9.96 9.96 0 002.343 5.657"/></svg>
            </button>
            @endif
        </div>
    </div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
function videoCall(callId, roomId, userId, token) {
    return {
        callId, roomId, userId, token,
        localStream: null,
        remoteStream: null,
        peerConnection: null,
        isMuted: false,
        localVideoOff: false,
        isScreenSharing: false,
        speakerOn: true,
        aiFaceEnabled: false,
        callActive: false,
        callStartTime: null,
        durationInterval: null,
        duration: '00:00',
        statusLabel: 'Connecting...',

        async init() {
            await this.setupMedia();
            await this.setupWebRTC();
            this.listenForSignaling();
        },

        async setupMedia() {
            try {
                this.localStream = await navigator.mediaDevices.getUserMedia({
                    video: '{{ $call->type }}' === 'video',
                    audio: true,
                });
                const localVideo = document.getElementById('local-video');
                if (localVideo) localVideo.srcObject = this.localStream;
            } catch (err) {
                console.error('Media error:', err);
                this.statusLabel = 'Camera/mic access denied';
            }
        },

        async setupWebRTC() {
            const config = {
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' },
                ]
            };

            this.peerConnection = new RTCPeerConnection(config);

            if (this.localStream) {
                this.localStream.getTracks().forEach(track => {
                    this.peerConnection.addTrack(track, this.localStream);
                });
            }

            this.peerConnection.ontrack = (event) => {
                const remoteVideo = document.getElementById('remote-video');
                if (remoteVideo) remoteVideo.srcObject = event.streams[0];
                this.callActive = true;
                this.statusLabel = 'Connected';
                this.startDurationTimer();
            };

            this.peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    this.sendSignal('ice-candidate', event.candidate);
                }
            };

            // Create offer
            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            this.sendSignal('offer', offer);
        },

        listenForSignaling() {
            if (typeof Echo === 'undefined') return;

            Echo.private(`call.${this.roomId}`)
                .listen('.call.signal', async (data) => {
                    if (data.user_id === this.userId) return;

                    if (data.type === 'offer') {
                        await this.peerConnection.setRemoteDescription(data.payload);
                        const answer = await this.peerConnection.createAnswer();
                        await this.peerConnection.setLocalDescription(answer);
                        this.sendSignal('answer', answer);
                    } else if (data.type === 'answer') {
                        await this.peerConnection.setRemoteDescription(data.payload);
                    } else if (data.type === 'ice-candidate') {
                        await this.peerConnection.addIceCandidate(data.payload);
                    } else if (data.type === 'call-ended') {
                        this.handleRemoteEnd();
                    }
                });
        },

        sendSignal(type, payload) {
            fetch(`/api/calls/${this.callId}/signal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ type, payload }),
            });
        },

        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.localStream) {
                this.localStream.getAudioTracks().forEach(t => t.enabled = !this.isMuted);
            }
            fetch(`/calls/${this.callId}/media`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ is_muted: this.isMuted }),
            });
        },

        toggleVideo() {
            this.localVideoOff = !this.localVideoOff;
            if (this.localStream) {
                this.localStream.getVideoTracks().forEach(t => t.enabled = !this.localVideoOff);
            }
        },

        async toggleScreenShare() {
            if (this.isScreenSharing) {
                await this.setupMedia();
                this.isScreenSharing = false;
                return;
            }
            try {
                const screen = await navigator.mediaDevices.getDisplayMedia({ video: true });
                const screenTrack = screen.getVideoTracks()[0];
                const sender = this.peerConnection?.getSenders().find(s => s.track?.kind === 'video');
                if (sender) sender.replaceTrack(screenTrack);
                screenTrack.onended = () => { this.toggleScreenShare(); };
                this.isScreenSharing = true;
            } catch (err) { console.error('Screen share error:', err); }
        },

        toggleSpeaker() { this.speakerOn = !this.speakerOn; },

        async toggleAiFace() {
            if (this.aiFaceEnabled) {
                this.aiFaceEnabled = false;
                return;
            }
            const res = await fetch(`/calls/${this.callId}/ai-face`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ provider: 'did' }),
            });
            const data = await res.json();
            if (data.success) this.aiFaceEnabled = true;
            else alert(data.error || 'Failed to enable AI face');
        },

        endCall() {
            this.sendSignal('call-ended', {});
            fetch(`/calls/${this.callId}/end`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            this.cleanup();
            window.close();
        },

        handleRemoteEnd() {
            this.cleanup();
            this.statusLabel = 'Call ended';
            setTimeout(() => window.close(), 2000);
        },

        cleanup() {
            clearInterval(this.durationInterval);
            if (this.localStream) this.localStream.getTracks().forEach(t => t.stop());
            if (this.peerConnection) this.peerConnection.close();
        },

        startDurationTimer() {
            this.callStartTime = Date.now();
            this.durationInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - this.callStartTime) / 1000);
                const m = Math.floor(elapsed / 60).toString().padStart(2, '0');
                const s = (elapsed % 60).toString().padStart(2, '0');
                this.duration = `${m}:${s}`;
            }, 1000);
        },

        swapVideos() {
            const local = document.getElementById('local-video');
            const remote = document.getElementById('remote-video');
            [local.srcObject, remote.srcObject] = [remote.srcObject, local.srcObject];
        },
    };
}

// Setup Echo for signaling
const AUTH_USER = { id: {{ auth()->id() }} };
const PUSHER_KEY = '{{ config("broadcasting.connections.pusher.key") }}';
const PUSHER_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
</script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
