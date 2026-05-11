@extends('layouts.admin')
@section('title', 'API Configuration')

@section('content')
<div class="max-w-4xl space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">API Configuration</h1>

    @if(session('success'))
    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    {{-- ══ PLATFORM SETUP GUIDE ══ --}}
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 rounded-2xl border border-indigo-200 dark:border-indigo-800 p-6">
        <h2 class="font-bold text-gray-900 dark:text-white text-base mb-1">🛠️ Platform Setup Guide</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            Items marked <span class="text-green-600 dark:text-green-400 font-medium">✓ Built-in</span> work immediately. Third-party providers need an API key but each has a free tier.
        </p>
        <div class="space-y-3">
            @foreach([
                ['icon'=>'⚡','title'=>'Real-time Chat (WebSockets)','badge'=>'Optional — pusher.com free tier','color'=>'yellow',
                 'desc'=>'Without Pusher, chat uses 3.5s polling (slight delay). With Pusher, messages arrive instantly.',
                 'code'=>"BROADCAST_DRIVER=pusher\nPUSHER_APP_ID=your_id\nPUSHER_APP_KEY=your_key\nPUSHER_APP_SECRET=your_secret\nPUSHER_APP_CLUSTER=mt1"],
                ['icon'=>'📞','title'=>'Voice / Video Calls (WebRTC)','badge'=>'✓ Built-in — Google STUN included','color'=>'green',
                 'desc'=>'Works peer-to-peer. Add a TURN server for users behind strict firewalls (metered.ca or xirsys.com — both free tiers).',
                 'code'=>"TURN_SERVER_URL=turn:your.turn.server:3478\nTURN_USERNAME=your_username\nTURN_PASSWORD=your_password"],
                ['icon'=>'🎭','title'=>'AI Face Tracking (MediaPipe)','badge'=>'✓ Built-in — No API key needed','color'=>'green',
                 'desc'=>'face-api.js runs in the browser — detects face position at 6 fps and overlays the avatar at 30 fps. No cost, no server calls.','code'=>null],
                ['icon'=>'📁','title'=>'File & Media Storage','badge'=>'✓ Local storage built-in','color'=>'green',
                 'desc'=>'For production, Cloudflare R2 has 10 GB free and is S3-compatible.',
                 'code'=>"FILESYSTEM_DISK=s3\nAWS_ACCESS_KEY_ID=...\nAWS_SECRET_ACCESS_KEY=...\nAWS_DEFAULT_REGION=auto\nAWS_BUCKET=your-bucket\nAWS_URL=https://your-bucket.r2.cloudflarestorage.com"],
            ] as $item)
            <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-{{ $item['color'] }}-100 dark:bg-{{ $item['color'] }}-900/30 flex items-center justify-center flex-shrink-0 text-lg">{{ $item['icon'] }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">{{ $item['title'] }}</h3>
                            <span class="text-[11px] px-2 py-0.5 bg-{{ $item['color'] }}-100 text-{{ $item['color'] }}-700 dark:bg-{{ $item['color'] }}-900/30 dark:text-{{ $item['color'] }}-400 rounded-full font-medium">{{ $item['badge'] }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item['desc'] }}</p>
                        @if($item['code'])
                        <pre class="mt-2 p-2 bg-gray-900 text-green-400 rounded-lg text-[11px] overflow-x-auto">{{ $item['code'] }}</pre>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ══ AI FACE / AVATAR PROVIDERS ══ --}}
    <h2 class="text-lg font-bold text-gray-900 dark:text-white pt-2">🤖 AI Face & Avatar Providers</h2>
    <p class="text-sm text-gray-400 -mt-4">Real-time face replacement during video calls. MediaPipe is always active (free). Configure paid providers for higher quality.</p>

    @php
    $aiProviders = [
        'deepfacelive' => [
            'icon'  => '🧠',
            'name'  => 'DeepFaceLive',
            'badge' => 'Self-hosted · Open Source · Free',
            'color' => 'gray',
            'desc'  => 'Deep learning face swap running on your own GPU server. Best quality, zero per-call cost.',
            'guide' => "1. Install: git clone https://github.com/iperov/DeepFaceLive && pip install -r requirements.txt\n2. Run WS bridge: python run_ws_server.py --port 8765\n3. Set the WebSocket URL below.",
            'fields'=> [['key'=>'endpoint','label'=>'WebSocket Server URL','type'=>'text','ph'=>'ws://your-server:8765'],
                        ['key'=>'api_key','label'=>'Auth Secret (optional)','type'=>'password','ph'=>'Leave blank if no auth']],
        ],
        'heygen' => [
            'icon'  => '🎬',
            'name'  => 'HeyGen API',
            'badge' => 'Cloud · Paid (free 1 credit/mo)',
            'color' => 'blue',
            'desc'  => 'Photorealistic streaming avatars. Requires Scale plan ($89/mo) for real-time Streaming Avatar API.',
            'guide' => "Sign up at heygen.com → API → Generate API Key.",
            'fields'=> [['key'=>'api_key','label'=>'API Key','type'=>'password','ph'=>'hg_...']],
        ],
        'did' => [
            'icon'  => '🪄',
            'name'  => 'D-ID API',
            'badge' => 'Cloud · 14-day free trial',
            'color' => 'blue',
            'desc'  => 'Talking head avatars from a single photo. Real-time Streams API on Advanced plan ($49/mo).',
            'guide' => "Sign up at d-id.com → Studio → API → Create Key. Use Basic Auth: base64(email:key).",
            'fields'=> [['key'=>'api_key','label'=>'Basic Auth Token','type'=>'password','ph'=>'Basic base64(email:key)']],
        ],
        'tavus' => [
            'icon'  => '🎥',
            'name'  => 'Tavus API',
            'badge' => 'Cloud · 25 free video credits',
            'color' => 'blue',
            'desc'  => 'Hyper-personalised video avatars that clone your voice and face. Phoenix model for real-time on Developer plan ($39/mo).',
            'guide' => "Sign up at tavus.io → Settings → API Key.",
            'fields'=> [['key'=>'api_key','label'=>'API Key','type'=>'password','ph'=>'tvs-...']],
        ],
        'simli' => [
            'icon'  => '💫',
            'name'  => 'Simli AI',
            'badge' => 'Cloud · 100 face-seconds free/mo',
            'color' => 'purple',
            'desc'  => 'Ultra-low-latency real-time face streaming (<80ms). Interactive AI avatars over WebRTC. No server install — just the API key.',
            'guide' => "Sign up at simli.com → Dashboard → API Keys.",
            'fields'=> [['key'=>'api_key','label'=>'API Key','type'=>'password','ph'=>'sk-simli-...']],
        ],
        'openai' => [
            'icon'  => '🤖',
            'name'  => 'OpenAI Realtime API',
            'badge' => 'Cloud · Pay-per-use',
            'color' => 'blue',
            'desc'  => 'GPT-4o Realtime for speech-to-speech AI conversations inside calls. $0.06/min audio input, $0.24/min output.',
            'guide' => "Sign up at platform.openai.com → API keys → Create secret key.",
            'fields'=> [['key'=>'api_key','label'=>'API Key','type'=>'password','ph'=>'sk-...']],
            'extra' => true,
        ],
    ];
    @endphp

    @foreach($aiProviders as $key => $p)
    @php $saved = $providers->get($key); $savedCfg = $saved?->api_config ?? []; @endphp
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="text-xl">{{ $p['icon'] }}</span> {{ $p['name'] }}
                    <span class="text-[11px] px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 rounded-full font-normal">{{ $p['badge'] }}</span>
                </h3>
                @if($saved?->is_enabled)
                <span class="text-[11px] px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full font-medium">Active</span>
                @endif
            </div>
            <p class="text-sm text-gray-400">{{ $p['desc'] }}</p>
            <div class="mt-2 p-2.5 bg-gray-50 dark:bg-gray-800 rounded-xl text-xs text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $p['guide'] }}</div>
        </div>
        <form method="POST" action="{{ route('admin.api-config.update', $key) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                @foreach($p['fields'] as $f)
                @php
                    $savedVal = $savedCfg[$f['key']] ?? '';
                    $isSecret = $f['type'] === 'password';
                    $displayVal = $isSecret ? '' : $savedVal;
                    $placeholder = ($isSecret && $savedVal !== '') ? '••••••••  (saved — leave blank to keep)' : $f['ph'];
                @endphp
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $f['label'] }}</label>
                    <input type="{{ $f['type'] }}" name="api_config[{{ $f['key'] }}]"
                           placeholder="{{ $placeholder }}" value="{{ $displayVal }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                @endforeach
                @if(!empty($p['extra']))
                @php $savedModel = $savedCfg['model'] ?? 'gpt-4o-realtime-preview'; @endphp
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Model</label>
                    <select name="api_config[model]" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="gpt-4o-realtime-preview" {{ $savedModel === 'gpt-4o-realtime-preview' ? 'selected' : '' }}>gpt-4o-realtime-preview</option>
                        <option value="gpt-4o-mini-realtime-preview" {{ $savedModel === 'gpt-4o-mini-realtime-preview' ? 'selected' : '' }}>gpt-4o-mini-realtime-preview (cheaper)</option>
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Daily Limit (minutes, 0 = unlimited)</label>
                    <input type="number" name="daily_limit" placeholder="0" value="{{ $saved?->daily_limit ?? '' }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div class="flex items-center gap-4 mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_enabled" value="1" class="w-4 h-4 text-primary-500 rounded" {{ $saved?->is_enabled ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Enable {{ $p['name'] }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 text-primary-500 rounded" {{ $saved?->is_default ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Set as default AI provider</span>
                </label>
            </div>
            <button type="submit" class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition-colors">Save Configuration</button>
        </form>
    </div>
    @endforeach

    {{-- ══ CALL INFRASTRUCTURE PROVIDERS ══ --}}
    <h2 class="text-lg font-bold text-gray-900 dark:text-white pt-2">📡 Call Infrastructure Providers</h2>
    <p class="text-sm text-gray-400 -mt-4">Replace built-in WebRTC for enterprise scale. WebRTC already works — these add SFU routing for large groups and better NAT traversal.</p>

    @php
    $callProviders = [
        'livekit' => [
            'icon' => '🚀', 'name' => 'LiveKit', 'badge' => 'Self-host free · Cloud 10k min/mo free',
            'desc' => 'Open-source WebRTC SFU. Scales to 1,000+ participants. Best alternative to built-in WebRTC for group calls.',
            'guide'=> "Option A (self-host): docker run -p 7880:7880 livekit/livekit-server --dev\nOption B (cloud): livekit.io/cloud — 10,000 participant-minutes free/month",
            'fields'=> [
                ['key'=>'api_key','label'=>'API Key','type'=>'text','ph'=>'APIxxxxxxxx'],
                ['key'=>'api_secret','label'=>'API Secret','type'=>'password','ph'=>'Secret...'],
                ['key'=>'endpoint','label'=>'Server URL','type'=>'text','ph'=>'wss://your.livekit.cloud','wide'=>true],
            ],
        ],
        'agora' => [
            'icon' => '📶', 'name' => 'Agora', 'badge' => '10,000 min free/month',
            'desc' => 'Enterprise-grade video/voice. Low-latency global routing, excellent for mobile. No credit card to sign up.',
            'guide'=> "Sign up at agora.io → Console → Create project → Copy App ID + App Certificate.",
            'fields'=> [
                ['key'=>'app_id','label'=>'App ID','type'=>'text','ph'=>'32-char hex App ID'],
                ['key'=>'api_key','label'=>'App Certificate','type'=>'password','ph'=>'App Certificate'],
            ],
        ],
        'daily' => [
            'icon' => '📅', 'name' => 'Daily.co', 'badge' => '2,000 min free/month',
            'desc' => 'Simplest integration — rooms created via REST API, joined via URL embed. Great for drop-in video calls.',
            'guide'=> "Sign up at daily.co → Developers → API keys → Create key. Rooms are auto-created per call.",
            'fields'=> [
                ['key'=>'api_key','label'=>'API Key','type'=>'password','ph'=>'daily-...'],
                ['key'=>'domain','label'=>'Daily Domain','type'=>'text','ph'=>'yourapp.daily.co'],
            ],
        ],
    ];
    @endphp

    @foreach($callProviders as $key => $p)
    @php $saved = $providers->get($key); $savedCfg = $saved?->api_config ?? []; @endphp
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="mb-4">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="text-xl">{{ $p['icon'] }}</span> {{ $p['name'] }}
                    <span class="text-[11px] px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full font-normal">{{ $p['badge'] }}</span>
                </h3>
                @if($saved?->is_enabled)
                <span class="text-[11px] px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full font-medium">Active</span>
                @endif
            </div>
            <p class="text-sm text-gray-400">{{ $p['desc'] }}</p>
            <div class="mt-2 p-2.5 bg-gray-50 dark:bg-gray-800 rounded-xl text-xs text-gray-500 dark:text-gray-400 whitespace-pre-line">{{ $p['guide'] }}</div>
        </div>
        <form method="POST" action="{{ route('admin.api-config.update', $key) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                @foreach($p['fields'] as $f)
                @php
                    $savedVal = $savedCfg[$f['key']] ?? '';
                    $isSecret = $f['type'] === 'password';
                    $displayVal = $isSecret ? '' : $savedVal;
                    $placeholder = ($isSecret && $savedVal !== '') ? '••••••••  (saved — leave blank to keep)' : $f['ph'];
                @endphp
                <div class="{{ !empty($f['wide']) ? 'md:col-span-2' : '' }}">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $f['label'] }}</label>
                    <input type="{{ $f['type'] }}" name="api_config[{{ $f['key'] }}]"
                           placeholder="{{ $placeholder }}" value="{{ $displayVal }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                @endforeach
            </div>
            <div class="flex items-center gap-4 mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_enabled" value="1" class="w-4 h-4 text-primary-500 rounded" {{ $saved?->is_enabled ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Enable {{ $p['name'] }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 text-primary-500 rounded" {{ $saved?->is_default ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700 dark:text-gray-300">Set as default call provider</span>
                </label>
            </div>
            <button type="submit" class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition-colors">Save Configuration</button>
        </form>
    </div>
    @endforeach
</div>
@endsection
