<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ theme: localStorage.getItem('voxchat_theme') || '{{ auth()->user()?->theme ?? 'system' }}' }"
      x-bind:class="{ 'dark': theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#6366f1" id="theme-color-meta">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="mobile-web-app-capable" content="yes">
    <title>@yield('title', config('app.name')) — VoxChat</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" href="/icons/icon-192.png">

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary:  { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81',950:'#1e1b4b' },
                        brand:    { DEFAULT:'#6366f1', dark:'#4f46e5' },
                        surface:  { DEFAULT:'#ffffff', dark:'#1a1a2e' },
                    },
                    fontFamily: { sans: ['Inter','system-ui','-apple-system','sans-serif'] },
                    screens: { xs: '375px' },
                    animation: {
                        'slide-in-right': 'slideInRight 0.2s ease-out',
                        'slide-in-up': 'slideInUp 0.25s ease-out',
                        'fade-in': 'fadeIn 0.2s ease-out',
                        'bounce-in': 'bounceIn 0.3s ease-out',
                        'pulse-dot': 'pulseDot 2s infinite',
                        'typing': 'typing 1.4s infinite ease-in-out',
                    },
                    keyframes: {
                        slideInRight: { from:{ transform:'translateX(100%)', opacity:0 }, to:{ transform:'translateX(0)', opacity:1 } },
                        slideInUp:    { from:{ transform:'translateY(20px)', opacity:0 }, to:{ transform:'translateY(0)', opacity:1 } },
                        fadeIn:       { from:{ opacity:0 }, to:{ opacity:1 } },
                        bounceIn:     { '0%':{ transform:'scale(0.3)', opacity:0 }, '50%':{ transform:'scale(1.05)' }, '70%':{ transform:'scale(0.9)' }, '100%':{ transform:'scale(1)', opacity:1 } },
                        pulseDot:     { '0%,100%':{ transform:'scale(1)', opacity:1 }, '50%':{ transform:'scale(1.4)', opacity:0.7 } },
                        typing:       { '0%,100%':{ transform:'translateY(0)' }, '50%':{ transform:'translateY(-4px)' } },
                    },
                    boxShadow: {
                        'glass': '0 8px 32px 0 rgba(31, 38, 135, 0.07)',
                        'message': '0 1px 3px rgba(0,0,0,0.12)',
                        'card': '0 4px 24px rgba(0,0,0,0.08)',
                        'nav': '0 -2px 20px rgba(0,0,0,0.06)',
                    },
                }
            },
        }
    </script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Custom CSS --}}
    <style>
        [x-cloak] { display: none !important; }

        * { -webkit-tap-highlight-color: transparent; box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            overscroll-behavior: none;
            -webkit-font-smoothing: antialiased;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }
        .dark ::-webkit-scrollbar-thumb { background: #374151; }

        /* Safe area insets for notched phones */
        .safe-top    { padding-top: env(safe-area-inset-top); }
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom); }
        .pb-safe     { padding-bottom: calc(env(safe-area-inset-bottom) + 4rem); }

        /* Glassmorphism */
        .glass {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .dark .glass {
            background: rgba(26,26,46,0.85);
        }

        /* Message bubbles */
        .msg-out {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        .msg-in {
            background: #f3f4f6;
            color: #111827;
            border-radius: 18px 18px 18px 4px;
        }
        .dark .msg-in {
            background: #1f2937;
            color: #f9fafb;
        }

        /* Typing indicator */
        .typing-dot:nth-child(1) { animation: typing 1.4s infinite 0s; }
        .typing-dot:nth-child(2) { animation: typing 1.4s infinite 0.2s; }
        .typing-dot:nth-child(3) { animation: typing 1.4s infinite 0.4s; }

        /* Story ring */
        .story-ring {
            background: linear-gradient(135deg, #f97316, #ec4899, #6366f1);
            padding: 2px;
            border-radius: 50%;
        }
        .story-ring-viewed {
            background: #d1d5db;
            padding: 2px;
            border-radius: 50%;
        }

        /* Bottom nav active */
        .nav-active svg { color: #6366f1; }
        .nav-active span { color: #6366f1; font-weight: 600; }

        /* Swipe gesture area */
        .swipe-action {
            transform: translateX(0);
            transition: transform 0.2s ease;
        }

        /* Skeleton loader */
        .skeleton {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
        }
        .dark .skeleton {
            background: linear-gradient(90deg, #1f2937 25%, #374151 50%, #1f2937 75%);
            background-size: 200% 100%;
        }
        @keyframes skeleton-loading {
            0%   { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Avatar online indicator */
        .online-dot {
            width: 10px; height: 10px;
            background: #22c55e;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 0; right: 0;
        }
        .dark .online-dot { border-color: #111827; }

        /* Voice waveform */
        .waveform-bar {
            background: #6366f1;
            border-radius: 2px;
            animation: waveform 1.2s ease-in-out infinite;
        }
        @keyframes waveform {
            0%,100% { height: 4px; }
            50%      { height: 20px; }
        }
    </style>

    @stack('head')
</head>

<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen overflow-x-hidden">

{{-- Incoming Call Modal --}}
<div x-data="incomingCallManager()" x-cloak>
    <div x-show="call" x-transition:enter="animate-slide-in-up"
         class="fixed inset-0 z-[100] flex items-end justify-center p-4 pointer-events-none">
        <div x-show="call" class="w-full max-w-sm bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-6 pointer-events-auto animate-bounce-in">
            <div class="flex flex-col items-center text-center space-y-4">
                <div class="relative">
                    <img :src="call?.caller?.avatar_url" class="w-20 h-20 rounded-full object-cover ring-4 ring-primary-500 animate-pulse">
                    <div class="absolute -bottom-1 -right-1 bg-primary-500 text-white rounded-full p-1.5">
                        <svg x-show="call?.type === 'video'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
                        <svg x-show="call?.type === 'voice'" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    </div>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Incoming <span x-text="call?.type"></span> call</p>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1" x-text="call?.caller?.name"></h3>
                </div>
                <div class="flex space-x-6 mt-2">
                    <button @click="rejectCall()" class="flex flex-col items-center space-y-1">
                        <div class="w-14 h-14 bg-red-500 rounded-full flex items-center justify-center shadow-lg">
                            <svg class="w-7 h-7 text-white rotate-135" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                        </div>
                        <span class="text-xs text-gray-500">Decline</span>
                    </button>
                    <button @click="answerCall()" class="flex flex-col items-center space-y-1">
                        <div class="w-14 h-14 bg-green-500 rounded-full flex items-center justify-center shadow-lg animate-pulse-dot">
                            <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                        </div>
                        <span class="text-xs text-gray-500">Answer</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notifications --}}
<div x-data="toastManager()" @toast.window="show($event.detail)" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 320px;">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible" x-transition:enter="animate-slide-in-right" x-transition:leave="opacity-0 translate-x-full"
             :class="{
                'bg-green-500': toast.type === 'success',
                'bg-red-500': toast.type === 'error',
                'bg-primary-500': toast.type === 'info',
                'bg-amber-500': toast.type === 'warning',
             }"
             class="flex items-center space-x-3 text-white px-4 py-3 rounded-2xl shadow-card text-sm font-medium">
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>

{{-- Main content --}}
<div id="app-container" class="flex flex-col min-h-screen">
    @yield('content')
</div>

{{-- Bottom Navigation (Mobile) --}}
@auth
<nav class="fixed bottom-0 left-0 right-0 z-40 glass border-t border-gray-200/50 dark:border-gray-800/50 shadow-nav safe-bottom lg:hidden">
    <div class="grid grid-cols-5 h-16">
        @php $current = request()->routeIs('chats.*') ? 'chats' : (request()->routeIs('calls.*') ? 'calls' : (request()->routeIs('status.*') ? 'status' : (request()->routeIs('communities.*') ? 'communities' : (request()->routeIs('profile.*') ? 'profile' : '')))); @endphp
        <a href="{{ route('chats.index') }}" class="flex flex-col items-center justify-center space-y-1 {{ $current === 'chats' ? 'nav-active' : '' }} relative">
            <div class="relative">
                <svg class="w-6 h-6 {{ $current === 'chats' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}" fill="{{ $current === 'chats' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                @if(auth()->user()->unread_notifications_count > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ min(auth()->user()->unread_notifications_count, 9) }}</span>
                @endif
            </div>
            <span class="text-[10px] font-medium {{ $current === 'chats' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}">Chats</span>
        </a>

        <a href="{{ route('calls.index') }}" class="flex flex-col items-center justify-center space-y-1 {{ $current === 'calls' ? 'nav-active' : '' }}">
            <svg class="w-6 h-6 {{ $current === 'calls' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}" fill="{{ $current === 'calls' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <span class="text-[10px] font-medium {{ $current === 'calls' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}">Calls</span>
        </a>

        <a href="{{ route('status.index') }}" class="flex flex-col items-center justify-center space-y-1 {{ $current === 'status' ? 'nav-active' : '' }}">
            <svg class="w-6 h-6 {{ $current === 'status' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}" fill="{{ $current === 'status' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-[10px] font-medium {{ $current === 'status' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}">Status</span>
        </a>

        <a href="{{ route('communities.index') }}" class="flex flex-col items-center justify-center space-y-1 {{ $current === 'communities' ? 'nav-active' : '' }}">
            <svg class="w-6 h-6 {{ $current === 'communities' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}" fill="{{ $current === 'communities' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="text-[10px] font-medium {{ $current === 'communities' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}">Communities</span>
        </a>

        <a href="{{ route('profile.show', auth()->user()->username) }}" class="flex flex-col items-center justify-center space-y-1 {{ $current === 'profile' ? 'nav-active' : '' }}">
            <div class="relative">
                <img src="{{ auth()->user()->avatar_url }}" class="w-6 h-6 rounded-full object-cover {{ $current === 'profile' ? 'ring-2 ring-primary-500' : '' }}">
            </div>
            <span class="text-[10px] font-medium {{ $current === 'profile' ? 'text-primary-500' : 'text-gray-400 dark:text-gray-500' }}">Profile</span>
        </a>
    </div>
</nav>
@endauth

{{-- Laravel Echo & Real-time JS --}}
@auth
@php
    $authUserData = [
        'id'         => auth()->id(),
        'name'       => auth()->user()->name,
        'username'   => auth()->user()->username,
        'avatar_url' => auth()->user()->avatar_url,
    ];
@endphp
<script>
    const AUTH_USER = @json($authUserData);
    const PUSHER_KEY = '{{ config("broadcasting.connections.pusher.key") }}';
    const PUSHER_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
    const PUSHER_HOST = '{{ config("broadcasting.connections.pusher.options.host") }}';
    const PUSHER_PORT = {{ config("broadcasting.connections.pusher.options.port", 443) }};
</script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script src="/js/app.js"></script>
@endauth

@stack('scripts')
</body>
</html>
