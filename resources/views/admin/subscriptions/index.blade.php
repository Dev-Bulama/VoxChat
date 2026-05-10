@extends('layouts.admin')
@section('title', 'Subscriptions')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscriptions</h1>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([['Active', $stats['active'], 'green'], ['Cancelled', $stats['cancelled'], 'red'], ['Expired', $stats['expired'], 'gray'], ['Trial', $stats['trial'] ?? 0, 'blue']] as [$label, $count, $color])
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-4">
            <p class="text-sm text-gray-400">{{ $label }}</p>
            <p class="text-2xl font-bold text-{{ $color }}-500 mt-1">{{ number_format($count) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Subscriptions table --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cycle</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gateway</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($subscriptions as $sub)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sub->user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $sub->user->email }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $sub->plan->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ ucfirst($sub->billing_cycle) }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $sub->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                               ($sub->status === 'cancelled' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-600') }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $sub->current_period_end?->format('d M Y') ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 uppercase">{{ $sub->payment_gateway ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No subscriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscriptions->links() }}
</div>
@endsection
