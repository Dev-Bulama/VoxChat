@extends('layouts.admin')
@section('title', 'Analytics')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Analytics</h1>
        <div class="flex items-center gap-2">
            <select id="period" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-white">
                <option value="30">Last 30 days</option>
                <option value="7">Last 7 days</option>
                <option value="90">Last 90 days</option>
            </select>
        </div>
    </div>

    {{-- Chart grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">User Registrations</h3>
            <canvas id="usersChart" height="200"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Messages Sent</h3>
            <canvas id="messagesChart" height="200"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Calls</h3>
            <canvas id="callsChart" height="200"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Revenue ($)</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>
    </div>

    {{-- Subscription breakdown --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Plan Distribution</h3>
            <canvas id="planChart" height="200"></canvas>
        </div>
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Subscription Summary</h3>
            <div class="space-y-3" id="subStats">
                <div class="animate-pulse h-4 bg-gray-100 dark:bg-gray-800 rounded"></div>
                <div class="animate-pulse h-4 bg-gray-100 dark:bg-gray-800 rounded w-3/4"></div>
                <div class="animate-pulse h-4 bg-gray-100 dark:bg-gray-800 rounded w-1/2"></div>
            </div>
        </div>
    </div>
</div>

<script>
const colors = {
    primary: '#6366f1',
    green: '#10b981',
    blue: '#3b82f6',
    purple: '#8b5cf6',
    orange: '#f59e0b',
};

function makeChart(id, type, labels, data, color, label) {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    return new Chart(ctx, {
        type,
        data: {
            labels,
            datasets: [{ label, data, borderColor: color, backgroundColor: color + '20', fill: true, tension: 0.4, borderWidth: 2, pointRadius: 3 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
}

async function loadAnalytics(days = 30) {
    const res = await fetch(`/admin/analytics/data?days=${days}`);
    const d = await res.json();

    makeChart('usersChart', 'line', d.users.labels, d.users.data, colors.primary, 'Users');
    makeChart('messagesChart', 'bar', d.messages.labels, d.messages.data, colors.blue, 'Messages');
    makeChart('callsChart', 'line', d.calls.labels, d.calls.data, colors.green, 'Calls');
    makeChart('revenueChart', 'bar', d.revenue.labels, d.revenue.data, colors.orange, 'Revenue');

    if (d.subscriptions) {
        const subs = d.subscriptions;
        const el = document.getElementById('subStats');
        el.innerHTML = `
            <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Free</span><span class="font-semibold text-gray-900 dark:text-white">${subs.free ?? 0}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Premium</span><span class="font-semibold text-gray-900 dark:text-white">${subs.premium ?? 0}</span></div>
            <div class="flex justify-between text-sm"><span class="text-gray-500 dark:text-gray-400">Business</span><span class="font-semibold text-gray-900 dark:text-white">${subs.business ?? 0}</span></div>
            <div class="border-t border-gray-100 dark:border-gray-800 pt-3 flex justify-between text-sm font-semibold">
                <span class="text-gray-700 dark:text-gray-300">Total Revenue</span>
                <span class="text-green-500">$${(subs.revenue ?? 0).toFixed(2)}</span>
            </div>`;
    }
}

document.getElementById('period').addEventListener('change', e => loadAnalytics(e.target.value));
loadAnalytics();
</script>
@endsection
