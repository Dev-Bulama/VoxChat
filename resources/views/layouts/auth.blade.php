<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#6366f1">
    <title>@yield('title', 'Welcome') — VoxChat</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81' },
                    },
                    fontFamily: { sans: ['Inter','system-ui','sans-serif'] },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'slide-up': 'slideUp 0.4s ease-out',
                    },
                    keyframes: {
                        float:   { '0%,100%':{ transform:'translateY(0)' }, '50%':{ transform:'translateY(-15px)' } },
                        slideUp: { from:{ transform:'translateY(30px)', opacity:0 }, to:{ transform:'translateY(0)', opacity:1 } },
                    },
                }
            },
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Inter', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
        [x-cloak] { display: none !important; }
        .auth-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #6366f1 100%);
        }
        .auth-card {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        input:focus { outline: none; }
        .input-field {
            background: #f9fafb;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            width: 100%;
            font-size: 15px;
            transition: all 0.2s;
        }
        .input-field:focus {
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
            border-radius: 12px;
            padding: 14px 24px;
            width: 100%;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(99,102,241,0.4);
        }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(99,102,241,0.5); }
        .btn-primary:active { transform: translateY(0); }
        .social-btn {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            border: 1.5px solid #e5e7eb; border-radius: 12px;
            padding: 11px 16px; font-weight: 500; font-size: 14px;
            background: white; cursor: pointer; transition: all 0.2s;
        }
        .social-btn:hover { border-color: #6366f1; background: #f5f3ff; }
    </style>
</head>
<body class="h-full auth-bg flex items-center justify-center min-h-screen p-4">

<div class="w-full max-w-sm animate-slide-up">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/20 backdrop-blur mb-4">
            <svg class="w-9 h-9 text-white" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56-.35-.12-.74-.03-1.01.24l-1.57 1.97c-2.83-1.35-5.48-3.9-6.89-6.83l1.95-1.66c.27-.28.35-.67.24-1.02-.37-1.11-.56-2.3-.56-3.53 0-.54-.45-.99-.99-.99H4.19C3.65 3 3 3.24 3 3.99 3 13.28 10.73 21 20.01 21c.71 0 .99-.63.99-1.18v-3.45c0-.54-.45-.99-.99-.99z"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white">VoxChat</h1>
        <p class="text-white/70 text-sm mt-1">Connect. Chat. Call. Anywhere.</p>
    </div>

    {{-- Auth Card --}}
    <div class="auth-card rounded-3xl shadow-2xl p-8">

        {{-- Error/Success messages --}}
        @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
            <ul class="text-sm text-red-600 space-y-1">
                @foreach ($errors->all() as $error)
                    <li class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if (session('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-600 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @yield('content')
    </div>
</div>

</body>
</html>
