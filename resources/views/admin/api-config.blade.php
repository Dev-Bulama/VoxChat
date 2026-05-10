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
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $provider->is_enabled ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
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
                    <input type="checkbox" name="is_enabled" value="1" {{ $provider->is_enabled ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-500 rounded">
                    <span class="text-sm text-gray-700 dark:text-gray-300">Enable this provider</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_default" value="1" {{ $provider->is_default ? 'checked' : '' }}
                           class="w-4 h-4 text-primary-500 rounded">
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
