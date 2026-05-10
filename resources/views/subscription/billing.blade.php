@extends('layouts.app')
@section('title', 'Billing History')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 pb-24 lg:pb-6">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('settings.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Billing History</h1>
    </div>

    @if($transactions->isEmpty())
    <div class="text-center py-16">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">No billing history yet.</p>
        <a href="{{ route('subscription.index') }}" class="mt-4 inline-block text-sm text-primary-500 hover:text-primary-600 font-medium">View Plans</a>
    </div>
    @else
    <div class="space-y-3">
        @foreach($transactions as $tx)
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $tx->subscription?->plan?->name ?? 'Subscription' }}</p>
                    <p class="text-sm text-gray-400 mt-0.5">{{ ucfirst($tx->gateway) }} · {{ ucfirst($tx->billing_cycle ?? 'monthly') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $tx->created_at->format('d M Y, H:i') }}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-gray-900 dark:text-white">${{ number_format($tx->amount, 2) }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $tx->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                           ($tx->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ ucfirst($tx->status) }}
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $transactions->links() }}
    </div>
    @endif
</div>
@endsection
