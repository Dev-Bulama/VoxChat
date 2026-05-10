<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($call->type) }} Call — VoxChat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: { colors: { primary: { 500:'#6366f1', 600:'#4f46e5' } } } } }</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #000; }
        #local-video, #remote-video, #ai-canvas { object-fit: cover; }
        .control-btn { width:56px; height:56px; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all .2s; }
        .control-btn:hover { transform: scale(1.05); }
        .glass-dark { background: rgba(0,0,0,0.55); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); }
        .face-card { transition: all .2s; cursor: pointer; }
        .face-card:hover { transform: scale(1.05); }
        .face-card.selected { ring: 3px; }
        .panel-slide { transition: transform .3s cubic-bezier(.4,0,.2,1); }
    </style>
</head>
<body class="h-full bg-gray-950 overflow-hidden"
      x-data="videoCall({{ $call->id }}, '{{ $call->room_id }}', {{ auth()->id() }}, '{{ $token }}')"
      x-init="init()">

{{-- Remote video --}}
<div class="relative w-full h-screen">
    <video id="remote-video" class="w-full h-full object-cover bg-gray-900" autoplay playsinline></video>

    {{-- Voice call avatar --}}
    @if($call->type === 'voice')
    <div class="absolute inset-0 flex flex-col items-center justify-center">
        @php $other = $call->participants->firstWhere('user_id', '!=', auth()->id()); @endphp
        <div class="relative mb-6">
            <img src="{{ $other?->user?->avatar_url ?? '' }}" class="w-32 h-32 rounded-full object-cover ring-4 ring-white/20">
            <div class="absolute inset-0 rounded-full animate-ping bg-white/10"></div>
        </div>
        <h2 class="text-2xl font-bold text-white">{{ $other?->user?->name ?? 'Unknown' }}</h2>
        <p class="text-white/60 mt-1" x-text="statusLabel">Connecting...</p>
        <p class="text-white/80 text-lg font-mono mt-3" x-text="duration" x-show="callActive">00:00</p>
    </div>
    @endif

    {{-- Local video / AI canvas (PiP) --}}
    @if($call->type === 'video')
    <div class="absolute top-4 right-4 w-32 h-48 rounded-2xl overflow-hidden shadow-xl border-2 border-white/20 cursor-pointer"
         @click="swapVideos()">
        {{-- Real camera feed --}}
        <video id="local-video" class="w-full h-full object-cover bg-gray-800 absolute inset-0"
               autoplay playsinline muted x-show="!aiFaceEnabled"></video>
        {{-- Canvas feed shown when AI face active --}}
        <canvas id="ai-canvas" class="w-full h-full object-cover absolute inset-0"
                x-show="aiFaceEnabled" style="display:none"></canvas>
        <div x-show="localVideoOff && !aiFaceEnabled"
             class="absolute inset-0 bg-gray-800 flex items-center justify-center z-10">
            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
        </div>
        {{-- AI face badge --}}
        <div x-show="aiFaceEnabled"
             class="absolute top-1 left-1 bg-purple-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full z-20">
            AI FACE
        </div>
    </div>
    @endif

    {{-- Top bar --}}
    <div class="absolute top-0 left-0 right-0 p-4 glass-dark">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                @php $other = $call->participants->firstWhere('user_id', '!=', auth()->id()); @endphp
                <img src="{{ $other?->user?->avatar_url ?? '' }}" class="w-9 h-9 rounded-full object-cover">
                <div>
                    <p class="text-white font-semibold text-sm">{{ $other?->user?->name ?? 'Unknown' }}</p>
                    <p class="text-white/60 text-xs" x-text="duration">00:00</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-end gap-0.5">
                    <div class="w-1 h-2 bg-green-400 rounded-sm"></div>
                    <div class="w-1 h-3 bg-green-400 rounded-sm"></div>
                    <div class="w-1 h-4 bg-green-400 rounded-sm"></div>
                </div>
                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Bottom controls --}}
    <div class="absolute bottom-0 left-0 right-0 glass-dark p-5">

        {{-- AI Face row --}}
        @if($call->type === 'video')
        <div class="flex items-center justify-center gap-3 mb-4">
            <button @click="showFacePanel = !showFacePanel"
                    :class="aiFaceEnabled ? 'bg-purple-500 shadow-lg shadow-purple-500/30' : 'bg-white/15'"
                    class="flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium text-white transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="aiFaceEnabled ? 'AI Face: ON ✓' : 'AI Face'"></span>
                <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <template x-if="aiFaceEnabled && selectedFaceName">
                <span class="text-xs text-purple-300 font-medium" x-text="'Using: ' + selectedFaceName"></span>
            </template>
        </div>
        @endif

        {{-- Main controls --}}
        <div class="flex items-center justify-center gap-4">
            <button @click="toggleMute()"
                    :class="isMuted ? 'bg-red-500' : 'bg-white/20'"
                    class="control-btn text-white">
                <svg x-show="!isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                <svg x-show="isMuted" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
            </button>

            <button @click="endCall()" class="control-btn bg-red-500 text-white w-16 h-16 shadow-xl shadow-red-500/40">
                <svg class="w-7 h-7 rotate-135" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
            </button>

            @if($call->type === 'video')
            <button @click="toggleVideo()"
                    :class="localVideoOff ? 'bg-red-500' : 'bg-white/20'"
                    class="control-btn text-white">
                <svg x-show="!localVideoOff" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                <svg x-show="localVideoOff" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
            </button>

            <button @click="toggleScreenShare()"
                    :class="isScreenSharing ? 'bg-green-500' : 'bg-white/20'"
                    class="control-btn text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </button>
            @else
            <button @click="toggleSpeaker()"
                    :class="speakerOn ? 'bg-white/20' : 'bg-white/10'"
                    class="control-btn text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6v12m-3-9.5a7 7 0 000 7"/></svg>
            </button>
            @endif
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- AI FACE PANEL                                                --}}
    {{-- ============================================================ --}}
    <div x-show="showFacePanel"
         x-transition:enter="panel-slide"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="panel-slide"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="absolute inset-x-0 bottom-0 z-50 rounded-t-3xl overflow-hidden"
         style="max-height: 85vh; background: rgba(10,10,20,0.97); backdrop-filter: blur(20px);">

        {{-- Handle + Header --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-white/10">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-bold text-sm">AI Face Replacement</h3>
                    <p class="text-white/40 text-xs">Your face will be replaced with the selected avatar</p>
                </div>
            </div>
            <button @click="showFacePanel = false" class="text-white/40 hover:text-white/80 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="overflow-y-auto" style="max-height: calc(85vh - 130px);">

            {{-- Turn off option --}}
            <div class="px-5 pt-4 pb-2">
                <button @click="disableAiFace()"
                        :class="!aiFaceEnabled ? 'ring-2 ring-white/50 bg-white/10' : 'bg-white/5'"
                        class="w-full flex items-center gap-3 p-3 rounded-2xl text-sm text-white/70 hover:bg-white/10 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-gray-800 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="font-medium text-white/80">No replacement</p>
                        <p class="text-xs text-white/40">Use your real camera</p>
                    </div>
                    <div x-show="!aiFaceEnabled" class="ml-auto w-5 h-5 bg-white rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-black" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                </button>
            </div>

            {{-- Upload your own face --}}
            <div class="px-5 py-3 border-t border-white/5">
                <p class="text-xs font-semibold text-white/40 uppercase tracking-wider mb-3">Upload Your Photo</p>
                <label class="relative flex items-center gap-3 p-3 rounded-2xl border-2 border-dashed border-white/20 hover:border-purple-400/50 cursor-pointer transition-colors group">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 group-hover:bg-purple-500/20 flex items-center justify-center flex-shrink-0 transition-colors">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white/80">Upload a face photo</p>
                        <p class="text-xs text-white/40">JPG or PNG, clear frontal face</p>
                    </div>
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handleFaceUpload($event)">
                    <div x-show="uploadedFaceUrl" class="ml-auto">
                        <img :src="uploadedFaceUrl" class="w-10 h-10 rounded-lg object-cover ring-2 ring-purple-400">
                    </div>
                </label>

                <template x-if="uploadedFaceUrl">
                    <button @click="applyFace(uploadedFaceUrl, 'My Photo', true)"
                            :class="selectedFaceUrl === uploadedFaceUrl ? 'bg-purple-500 text-white' : 'bg-white/10 text-white/70'"
                            class="w-full mt-2 py-2.5 rounded-xl text-sm font-medium transition-colors">
                        <span x-text="selectedFaceUrl === uploadedFaceUrl ? '✓ Using My Photo' : 'Use My Photo'"></span>
                    </button>
                </template>
            </div>

            {{-- Sample faces --}}
            <div class="px-5 py-3 border-t border-white/5">
                <p class="text-xs font-semibold text-white/40 uppercase tracking-wider mb-3">Sample Avatars</p>
                <div class="grid grid-cols-3 gap-3">
                    @php
                    $sampleFaces = [
                        ['name' => 'Alex',    'seed' => 'Alex',    'style' => 'lorelei'],
                        ['name' => 'Jordan',  'seed' => 'Jordan',  'style' => 'lorelei'],
                        ['name' => 'Morgan',  'seed' => 'Morgan',  'style' => 'lorelei'],
                        ['name' => 'Taylor',  'seed' => 'Taylor',  'style' => 'lorelei'],
                        ['name' => 'Casey',   'seed' => 'Casey',   'style' => 'lorelei'],
                        ['name' => 'Robin',   'seed' => 'Robin',   'style' => 'lorelei'],
                        ['name' => 'Sam',     'seed' => 'Sam',     'style' => 'avataaars'],
                        ['name' => 'Chris',   'seed' => 'Chris',   'style' => 'avataaars'],
                        ['name' => 'Jamie',   'seed' => 'Jamie',   'style' => 'avataaars'],
                    ];
                    @endphp

                    @foreach($sampleFaces as $face)
                    @php $faceUrl = "https://api.dicebear.com/8.x/{$face['style']}/svg?seed={$face['seed']}&backgroundColor=b6e3f4,c0aede,d1d4f9"; @endphp
                    <button @click="applyFace('{{ $faceUrl }}', '{{ $face['name'] }}', false)"
                            :class="selectedFaceUrl === '{{ $faceUrl }}' ? 'ring-2 ring-purple-400 bg-purple-500/20' : 'bg-white/5 hover:bg-white/10'"
                            class="face-card relative rounded-2xl p-2 flex flex-col items-center gap-1.5 transition-all">
                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-800 flex items-center justify-center">
                            <img src="{{ $faceUrl }}" alt="{{ $face['name'] }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ $face['name'] }}&background=6366f1&color=fff&size=64'">
                        </div>
                        <span class="text-xs text-white/60 font-medium">{{ $face['name'] }}</span>
                        <div x-show="selectedFaceUrl === '{{ $faceUrl }}'"
                             class="absolute top-1 right-1 w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Your saved avatars --}}
            @if(auth()->user()->aiAvatars && auth()->user()->aiAvatars->count() > 0)
            <div class="px-5 py-3 border-t border-white/5">
                <p class="text-xs font-semibold text-white/40 uppercase tracking-wider mb-3">Your Saved Faces</p>
                <div class="grid grid-cols-3 gap-3">
                    @foreach(auth()->user()->aiAvatars->where('processing_status', 'completed') as $avatar)
                    <button @click="applyFace('{{ $avatar->avatar_url }}', 'Saved Face', true)"
                            :class="selectedFaceUrl === '{{ $avatar->avatar_url }}' ? 'ring-2 ring-purple-400 bg-purple-500/20' : 'bg-white/5 hover:bg-white/10'"
                            class="face-card relative rounded-2xl p-2 flex flex-col items-center gap-1.5">
                        <img src="{{ $avatar->avatar_url }}" class="w-16 h-16 rounded-xl object-cover bg-gray-800">
                        <span class="text-xs text-white/60">Saved</span>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Apply button --}}
        <div class="px-5 py-4 border-t border-white/10">
            <button @click="confirmAiFace()"
                    :disabled="!selectedFaceUrl"
                    :class="selectedFaceUrl ? 'bg-purple-500 hover:bg-purple-600' : 'bg-white/10 opacity-50 cursor-not-allowed'"
                    class="w-full py-3 rounded-2xl text-white font-semibold text-sm transition-colors">
                <span x-text="aiFaceEnabled ? 'Update AI Face' : 'Apply AI Face'"></span>
            </button>
        </div>
    </div>

    {{-- Backdrop --}}
    <div x-show="showFacePanel" @click="showFacePanel = false"
         class="absolute inset-0 bg-black/40 z-40" x-transition:enter="opacity-0" x-transition:enter-end="opacity-100"></div>
</div>

<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
function videoCall(callId, roomId, userId, token) {
    return {
        callId, roomId, userId, token,
        localStream: null,
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

        // AI Face
        showFacePanel: false,
        selectedFaceUrl: null,
        selectedFaceName: null,
        uploadedFaceUrl: null,
        faceImage: null,
        canvasInterval: null,

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
                const lv = document.getElementById('local-video');
                if (lv) lv.srcObject = this.localStream;
            } catch (err) {
                console.error('Media error:', err);
                this.statusLabel = 'Camera/mic access denied';
            }
        },

        async setupWebRTC() {
            this.peerConnection = new RTCPeerConnection({
                iceServers: [
                    { urls: 'stun:stun.l.google.com:19302' },
                    { urls: 'stun:stun1.l.google.com:19302' },
                ]
            });

            if (this.localStream) {
                this.localStream.getTracks().forEach(t => this.peerConnection.addTrack(t, this.localStream));
            }

            this.peerConnection.ontrack = (e) => {
                const rv = document.getElementById('remote-video');
                if (rv) rv.srcObject = e.streams[0];
                this.callActive = true;
                this.statusLabel = 'Connected';
                this.startDurationTimer();
            };

            this.peerConnection.onicecandidate = (e) => {
                if (e.candidate) this.sendSignal('ice-candidate', e.candidate);
            };

            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            this.sendSignal('offer', offer);
        },

        listenForSignaling() {
            if (typeof Echo === 'undefined') return;
            Echo.private(`call.${this.roomId}`).listen('.call.signal', async (data) => {
                if (data.user_id === this.userId) return;
                if (data.type === 'offer') {
                    await this.peerConnection.setRemoteDescription(data.payload);
                    const ans = await this.peerConnection.createAnswer();
                    await this.peerConnection.setLocalDescription(ans);
                    this.sendSignal('answer', ans);
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
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ type, payload }),
            });
        },

        // ---- AI Face ----
        handleFaceUpload(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.uploadedFaceUrl = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        applyFace(url, name, isCustom) {
            this.selectedFaceUrl = url;
            this.selectedFaceName = name;
        },

        async confirmAiFace() {
            if (!this.selectedFaceUrl) return;
            this.aiFaceEnabled = true;
            this.showFacePanel = false;
            this.startCanvasOverlay();

            // Notify server
            try {
                await fetch(`/calls/${this.callId}/ai-face`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ provider: 'canvas', face_url: this.selectedFaceUrl }),
                });
            } catch (e) { /* continue even if server call fails */ }
        },

        disableAiFace() {
            this.aiFaceEnabled = false;
            this.selectedFaceUrl = null;
            this.selectedFaceName = null;
            this.stopCanvasOverlay();
            this.showFacePanel = false;
        },

        startCanvasOverlay() {
            const canvas = document.getElementById('ai-canvas');
            if (!canvas) return;
            const video = document.getElementById('local-video');
            if (!video) return;

            canvas.width = 320;
            canvas.height = 480;
            const ctx = canvas.getContext('2d');

            // Load face image
            this.faceImage = new Image();
            this.faceImage.crossOrigin = 'anonymous';
            this.faceImage.src = this.selectedFaceUrl;

            this.canvasInterval = setInterval(() => {
                // Draw video frame
                try {
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                } catch(e) {}

                // Draw face overlay (centered top ~40% of frame — face position estimate)
                if (this.faceImage && this.faceImage.complete && this.faceImage.naturalWidth > 0) {
                    const fw = canvas.width * 0.55;
                    const fh = canvas.height * 0.45;
                    const fx = (canvas.width - fw) / 2;
                    const fy = canvas.height * 0.02;

                    // Circular clip for face
                    ctx.save();
                    ctx.globalAlpha = 0.92;
                    ctx.beginPath();
                    ctx.ellipse(fx + fw/2, fy + fh * 0.45, fw/2, fh/2, 0, 0, Math.PI * 2);
                    ctx.clip();
                    ctx.drawImage(this.faceImage, fx, fy, fw, fh);
                    ctx.restore();
                }

                // Purple border indicator
                ctx.strokeStyle = '#a855f7';
                ctx.lineWidth = 3;
                ctx.strokeRect(0, 0, canvas.width, canvas.height);

                // Label
                ctx.fillStyle = 'rgba(168,85,247,0.8)';
                ctx.fillRect(6, 6, 70, 20);
                ctx.fillStyle = '#fff';
                ctx.font = 'bold 11px Inter, sans-serif';
                ctx.fillText('AI FACE', 12, 20);

            }, 33); // ~30fps

            // Replace video track sent to peer with canvas stream
            try {
                const canvasStream = canvas.captureStream(30);
                const canvasVideoTrack = canvasStream.getVideoTracks()[0];
                const sender = this.peerConnection?.getSenders().find(s => s.track?.kind === 'video');
                if (sender && canvasVideoTrack) sender.replaceTrack(canvasVideoTrack);
            } catch(e) { console.warn('Canvas stream replace failed:', e); }
        },

        stopCanvasOverlay() {
            if (this.canvasInterval) {
                clearInterval(this.canvasInterval);
                this.canvasInterval = null;
            }
            // Restore real camera track
            if (this.localStream && this.peerConnection) {
                const realTrack = this.localStream.getVideoTracks()[0];
                const sender = this.peerConnection.getSenders().find(s => s.track?.kind === 'video');
                if (sender && realTrack) sender.replaceTrack(realTrack);
            }
        },

        // ---- Call controls ----
        toggleMute() {
            this.isMuted = !this.isMuted;
            if (this.localStream) this.localStream.getAudioTracks().forEach(t => t.enabled = !this.isMuted);
            fetch(`/calls/${this.callId}/media`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ is_muted: this.isMuted }),
            });
        },

        toggleVideo() {
            this.localVideoOff = !this.localVideoOff;
            if (this.localStream) this.localStream.getVideoTracks().forEach(t => t.enabled = !this.localVideoOff);
        },

        async toggleScreenShare() {
            if (this.isScreenSharing) {
                await this.setupMedia();
                this.isScreenSharing = false;
                return;
            }
            try {
                const screen = await navigator.mediaDevices.getDisplayMedia({ video: true });
                const track = screen.getVideoTracks()[0];
                const sender = this.peerConnection?.getSenders().find(s => s.track?.kind === 'video');
                if (sender) sender.replaceTrack(track);
                track.onended = () => { this.isScreenSharing = false; };
                this.isScreenSharing = true;
            } catch(e) { console.error(e); }
        },

        toggleSpeaker() { this.speakerOn = !this.speakerOn; },

        endCall() {
            this.sendSignal('call-ended', {});
            fetch(`/calls/${this.callId}/end`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            });
            this.cleanup();
            window.location.href = '/calls';
        },

        handleRemoteEnd() {
            this.cleanup();
            this.statusLabel = 'Call ended';
            setTimeout(() => { window.location.href = '/calls'; }, 2000);
        },

        cleanup() {
            clearInterval(this.durationInterval);
            this.stopCanvasOverlay();
            if (this.localStream) this.localStream.getTracks().forEach(t => t.stop());
            if (this.peerConnection) this.peerConnection.close();
        },

        startDurationTimer() {
            this.callStartTime = Date.now();
            this.durationInterval = setInterval(() => {
                const s = Math.floor((Date.now() - this.callStartTime) / 1000);
                this.duration = `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`;
            }, 1000);
        },

        swapVideos() {
            const local = document.getElementById('local-video');
            const remote = document.getElementById('remote-video');
            if (local && remote) [local.srcObject, remote.srcObject] = [remote.srcObject, local.srcObject];
        },
    };
}

const AUTH_USER = { id: {{ auth()->id() }} };
const PUSHER_KEY = '{{ config("broadcasting.connections.pusher.key") }}';
const PUSHER_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
const PUSHER_PORT = {{ config("broadcasting.connections.pusher.options.port", 443) }};
</script>
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
