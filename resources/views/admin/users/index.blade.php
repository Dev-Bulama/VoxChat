@extends('layouts.admin')
@section('title', 'User Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users</h1>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $users->total() }} total</span>
    </div>

    {{-- Filters --}}
    <form method="GET" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search name, email, username..."
               class="flex-1 min-w-48 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">

        <select name="plan" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            <option value="">All Plans</option>
            <option value="free" @selected(request('plan') === 'free')>Free</option>
            <option value="premium" @selected(request('plan') === 'premium')>Premium</option>
            <option value="business" @selected(request('plan') === 'business')>Business</option>
        </select>

        <select name="status" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="banned" @selected(request('status') === 'banned')>Banned</option>
        </select>

        <button type="submit" class="px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white rounded-xl text-sm font-medium">Filter</button>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 rounded-xl text-sm">Reset</a>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $user->subscription_plan === 'business' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' :
                               ($user->subscription_plan === 'premium' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' :
                               'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400') }}">
                            {{ ucfirst($user->subscription_plan) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($user->is_banned)
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Banned</span>
                        @elseif($user->is_online)
                        <span class="flex items-center gap-1.5 text-xs text-green-600 dark:text-green-400 font-medium"><span class="w-2 h-2 bg-green-500 rounded-full"></span>Online</span>
                        @else
                        <span class="text-xs text-gray-400">{{ $user->last_seen_formatted }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">View</a>
                            @if($user->is_banned)
                            <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                @csrf
                                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200">Unban</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.users.ban', $user) }}" onsubmit="return confirm('Ban this user?')">
                                @csrf
                                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-200">Ban</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->withQueryString()->links() }}
</div>
@endsection
