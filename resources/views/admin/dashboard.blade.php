@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div x-data="dashboard()" x-init="loadCharts()">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <span class="text-xs text-green-500 font-medium bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">+{{ $stats['users_growth'] ?? 0 }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total_users'] ?? 0) }}</p>
            <p class="text-sm text-gray-400 mt-0.5">Total Users</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <span class="text-xs text-green-500 font-medium bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">Today</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['messages_today'] ?? 0) }}</p>
            <p class="text-sm text-gray-400 mt-0.5">Messages Today</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <span class="text-xs text-purple-500 font-medium bg-purple-50 dark:bg-purple-900/20 px-2 py-0.5 rounded-full">Active</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['active_subscriptions'] ?? 0) }}</p>
            <p class="text-sm text-gray-400 mt-0.5">Paid Subscriptions</p>
        </div>

        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs text-green-500 font-medium bg-green-50 dark:bg-green-900/20 px-2 py-0.5 rounded-full">+{{ $stats['revenue_growth'] ?? 0 }}%</span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($stats['monthly_revenue'] ?? 0, 2) }}</p>
            <p class="text-sm text-gray-400 mt-0.5">Monthly Revenue</p>
        </div>
    </div>

    {{-- Secondary stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-xs font-medium text-gray-500">Online Now</span>
            </div>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['online_users'] ?? 0) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-gray-500">Active Calls</span>
            </div>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['active_calls'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-gray-500">Stories Today</span>
            </div>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $stats['stories_today'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-medium text-gray-500">Pending Reports</span>
            </div>
            <p class="text-xl font-bold {{ ($stats['pending_reports'] ?? 0) > 0 ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">{{ $stats['pending_reports'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">User Registrations (30 days)</h3>
            <canvas id="usersChart" height="200"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-800">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Revenue (30 days)</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
            <h3 class="font-semibold text-gray-900 dark:text-white">Recent Users</h3>
            <a href="{{ route('admin.users.index') }}" class="text-sm text-primary-500 hover:text-primary-600 font-medium">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">User</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 hidden sm:table-cell">Email</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Plan</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3 hidden md:table-cell">Joined</th>
                        <th class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider px-5 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                    @foreach($recentUsers as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400">@{{ $user->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5 hidden sm:table-cell">
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                @if($user->subscription_plan === 'business') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400
                                @elseif($user->subscription_plan === 'premium') bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400
                                @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 @endif">
                                {{ ucfirst($user->subscription_plan) }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 hidden md:table-cell">
                            <span class="text-sm text-gray-400">{{ $user->created_at->format('d M, Y') }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($user->is_banned)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                <div class="w-1.5 h-1.5 rounded-full bg-red-500"></div> Banned
                            </span>
                            @elseif($user->is_online)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div> Online
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-400"></div> Offline
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dashboard() {
    return {
        loadCharts() {
            // Users chart
            const usersCtx = document.getElementById('usersChart').getContext('2d');
            new Chart(usersCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode(array_keys($charts['users'] ?? [])) !!},
                    datasets: [{
                        label: 'New Users',
                        data: {!! json_encode(array_values($charts['users'] ?? [])) !!},
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } } } }
            });

            // Revenue chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode(array_keys($charts['revenue'] ?? [])) !!},
                    datasets: [{
                        label: 'Revenue ($)',
                        data: {!! json_encode(array_values($charts['revenue'] ?? [])) !!},
                        backgroundColor: 'rgba(99,102,241,0.8)',
                        borderRadius: 6,
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { display: false }, y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } } } }
            });
        }
    };
}
</script>
@endpush
@endsection
