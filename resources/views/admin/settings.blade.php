@extends('layouts.admin')
@section('title', 'System Settings')

@section('content')
<div class="max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h1>

    @if(session('success'))
    <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf

        @foreach($settings->groupBy('group') as $group => $groupSettings)
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white capitalize mb-5">{{ $group }} Settings</h2>
            <div class="space-y-4">
                @foreach($groupSettings as $setting)
                <div class="flex items-center justify-between gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ ucwords(str_replace(['_', '.'], ' ', $setting->key)) }}
                        </label>
                    </div>
                    <div class="w-56">
                        @if($setting->type === 'boolean')
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                            <input type="checkbox" name="settings[{{ $setting->key }}]" value="1"
                                   {{ $setting->value ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-500"></div>
                        </label>
                        @else
                        <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}"
                               name="settings[{{ $setting->key }}]"
                               value="{{ $setting->value }}"
                               class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>
@endsection
