@extends('layouts.admin')
@section('title', $user->name)

@section('content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">User Profile</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Profile card --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 text-center">
            <img src="{{ $user->avatar_url }}" alt="" class="w-20 h-20 rounded-full object-cover mx-auto mb-3">
            <h2 class="font-bold text-gray-900 dark:text-white">{{ $user->name }}</h2>
            <p class="text-sm text-gray-400">@{{ $user->username }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user->email }}</p>

            <div class="mt-4 flex flex-col gap-2">
                @if(!$user->is_banned)
                <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="w-full">
                    @csrf
                    <input type="text" name="reason" placeholder="Ban reason (optional)" class="w-full mb-2 px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white">
                    <button type="submit" class="w-full py-2 rounded-xl text-sm font-medium bg-red-500 hover:bg-red-600 text-white">Ban User</button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-xl text-sm font-medium bg-green-500 hover:bg-green-600 text-white">Unban User</button>
                </form>
                @endif

                <form method="POST" action="{{ route('admin.users.verify', $user) }}">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-xl text-sm font-medium bg-blue-500 hover:bg-blue-600 text-white">
                        {{ $user->is_verified ? 'Remove Verification' : 'Verify User' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Details --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Account Details</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400">Plan</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ ucfirst($user->subscription_plan) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Status</dt>
                        <dd class="font-medium {{ $user->is_banned ? 'text-red-500' : 'text-green-500' }}">
                            {{ $user->is_banned ? 'Banned' : 'Active' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Verified</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $user->is_verified ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Online</dt>
                        <dd class="font-medium {{ $user->is_online ? 'text-green-500' : 'text-gray-400' }}">
                            {{ $user->is_online ? 'Now' : $user->last_seen_formatted }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Country</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $user->country ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400">Joined</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('d M Y') }}</dd>
                    </div>
                    @if($user->is_banned && $user->ban_reason)
                    <div class="col-span-2">
                        <dt class="text-gray-400">Ban Reason</dt>
                        <dd class="font-medium text-red-500">{{ $user->ban_reason }}</dd>
                    </div>
                    @endif
                </dl>
            </div>

            @if($user->bio)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-2">Bio</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $user->bio }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
