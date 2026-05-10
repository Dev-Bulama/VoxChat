@extends('layouts.app')
@section('title', 'Settings')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 pb-24 lg:pb-6">

    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Settings</h1>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-4">

        {{-- Account --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Account</h2>
            </div>
            <div class="divide-y divide-gray-50 dark:divide-gray-800">
                <a href="{{ route('profile.edit') }}" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Edit Profile</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('subscription.index') }}" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Subscription</p>
                            <p class="text-xs text-gray-400">{{ ucfirst(auth()->user()->subscription_plan) }} plan</p>
                        </div>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('subscription.billing') }}" class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Billing History</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Notifications --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Notifications</h2>
            <form method="POST" action="{{ route('profile.notifications') }}">
                @csrf
                <div class="space-y-3">
                    @foreach([
                        ['notify_messages', 'New Messages', auth()->user()->notify_messages ?? true],
                        ['notify_calls', 'Incoming Calls', auth()->user()->notify_calls ?? true],
                        ['notify_stories', 'Stories', auth()->user()->notify_stories ?? true],
                        ['notify_groups', 'Group Activity', auth()->user()->notify_groups ?? true],
                    ] as [$field, $label, $checked])
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input type="checkbox" name="{{ $field }}" value="1" {{ $checked ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-500"></div>
                        </label>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="w-full mt-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors text-sm">
                    Save Notifications
                </button>
            </form>
        </div>

        {{-- Appearance --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Appearance</h2>
            <form method="POST" action="{{ route('profile.theme') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Theme</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['light', 'dark', 'system'] as $theme)
                            <label class="cursor-pointer">
                                <input type="radio" name="theme" value="{{ $theme }}" class="sr-only peer"
                                       {{ auth()->user()->theme === $theme ? 'checked' : '' }}>
                                <div class="p-3 rounded-xl border-2 border-gray-200 dark:border-gray-700 peer-checked:border-primary-500 text-center transition-colors">
                                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ ucfirst($theme) }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors text-sm">
                        Save Appearance
                    </button>
                </div>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <h2 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Security</h2>
            <form method="POST" action="{{ route('password.change') }}">
                @csrf
                <div class="space-y-3">
                    <input type="password" name="current_password" placeholder="Current password"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <input type="password" name="password" placeholder="New password"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <input type="password" name="password_confirmation" placeholder="Confirm new password"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <button type="submit" class="w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors text-sm">
                        Change Password
                    </button>
                </div>
            </form>
        </div>

        {{-- Sign out --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-5 py-4 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span class="font-medium text-sm">Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
