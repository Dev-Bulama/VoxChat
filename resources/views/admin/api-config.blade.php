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

    {{-- ── Platform Setup Guide ── --}}
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 rounded-2xl border border-indigo-200 dark:border-indigo-800 p-6">
        <h2 class="font-bold text-gray-900 dark:text-white text-base mb-1 flex items-center gap-2">
            🛠️ Platform Setup Guide
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Follow these steps to enable all features. Items marked <span class="text-green-600 dark:text-green-400 font-medium">Free / Built-in</span> need no API key.</p>

        <div class="space-y-4">

            {{-- AI Face Tracking --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0 text-lg">🎭</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">AI Face Tracking (live movement)</h3>
                            <span class="text-[11px] px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full font-medium">✓ Free — No API key needed</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Uses <strong>face-api.js (MediaPipe)</strong> running entirely in the browser. During a video call the AI detects your face and maps the selected avatar to your exact head position at 30 fps — the other person sees the avatar moving like you do. No server processing or external API required.</p>
                        <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded-lg text-xs text-gray-500 dark:text-gray-400">
                            <strong>Status:</strong> Always enabled. Models auto-load from jsDelivr CDN on first call.<br>
                            <strong>Works best when:</strong> Good lighting, face clearly visible, frontal camera position.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pusher --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0 text-lg">⚡</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Real-time Chat (WebSockets)</h3>
                            <span class="text-[11px] px-2 py-0.5 bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 rounded-full font-medium">Optional — Free tier at pusher.com</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Without this, chat uses automatic 3.5-second polling (messages appear with a short delay). With Pusher, messages arrive instantly. Fallback polling works on all shared hosting automatically.</p>
                        <ol class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400 list-decimal list-inside">
                            <li>Sign up free at <strong>pusher.com</strong> → Create a Channels app</li>
                            <li>Copy your App ID, Key, Secret and Cluster</li>
                            <li>Add to your server <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">.env</code> file:</li>
                        </ol>
                        <pre class="mt-2 p-2 bg-gray-900 text-green-400 rounded-lg text-[11px] overflow-x-auto leading-relaxed">BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1</pre>
                    </div>
                </div>
            </div>

            {{-- TURN servers --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0 text-lg">📞</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Video / Voice Calls (TURN server)</h3>
                            <span class="text-[11px] px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full font-medium">✓ Google STUN built-in (works for most)</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Calls use Google's free STUN servers by default. If calls fail behind strict firewalls or carrier-grade NAT, add a TURN server for full reliability.</p>
                        <ol class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400 list-decimal list-inside">
                            <li>Sign up free at <strong>metered.ca</strong> or <strong>xirsys.com</strong></li>
                            <li>Add TURN credentials to <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">.env</code>:</li>
                        </ol>
                        <pre class="mt-2 p-2 bg-gray-900 text-green-400 rounded-lg text-[11px] overflow-x-auto leading-relaxed">TURN_SERVER_URL=turn:your.turn.server:3478
TURN_USERNAME=your_username
TURN_PASSWORD=your_password</pre>
                    </div>
                </div>
            </div>

            {{-- File storage --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-800">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0 text-lg">📁</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">File / Media Storage</h3>
                            <span class="text-[11px] px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded-full font-medium">✓ Local storage built-in</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Files are stored locally by default. For production with many users, use S3-compatible storage — Cloudflare R2 has a very generous free tier (10 GB free).</p>
                        <pre class="mt-2 p-2 bg-gray-900 text-green-400 rounded-lg text-[11px] overflow-x-auto leading-relaxed">FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.r2.cloudflarestorage.com</pre>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ── AI Provider Config Forms ── --}}
    @foreach($providers as $provider)
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="font-semibold text-gray-900 dark:text-white">{{ $provider->display_name ?? ucfirst($provider->provider_name) }}</h2>
                <p class="text-sm text-gray-400">{{ $provider->description ?? 'AI Provider' }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($provider->is_default)
                <span class="text-xs px-2.5 py-1 bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300 rounded-full font-medium">Default</span>
                @endif
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $provider->is_enabled ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                    {{ $provider->is_enabled ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.api-config.update', $provider) }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">API Key</label>
                    <input type="password" name="api_key"
                           placeholder="{{ $provider->has_api_key ? '••••••••••••••••' : 'Enter API key' }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Daily Limit (minutes)</label>
                    <input type="number" name="daily_limit" value="{{ $provider->daily_limit }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_enabled" value="1" {{ $provider->is_enabled ? 'checked' : '' }} class="w-4 h-4 text-primary-500 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Enable this provider</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" {{ $provider->is_default ? 'checked' : '' }} class="w-4 h-4 text-primary-500 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Set as default</span>
                </label>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition-colors">
                    Save Configuration
                </button>
            </div>
        </form>
    </div>
    @endforeach
</div>
@endsection
