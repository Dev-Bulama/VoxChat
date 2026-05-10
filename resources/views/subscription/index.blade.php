@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8 pb-24 lg:pb-8" x-data="{ billing: 'monthly' }">

    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Choose Your Plan</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Unlock the full VoxChat experience</p>

        {{-- Billing toggle --}}
        <div class="inline-flex items-center gap-3 mt-4 bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
            <button @click="billing = 'monthly'"
                    :class="billing === 'monthly' ? 'bg-white dark:bg-gray-700 shadow-sm' : ''"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 transition-all">
                Monthly
            </button>
            <button @click="billing = 'yearly'"
                    :class="billing === 'yearly' ? 'bg-white dark:bg-gray-700 shadow-sm' : ''"
                    class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 transition-all">
                Yearly <span class="ml-1 text-xs text-green-500 font-semibold">Save 20%</span>
            </button>
        </div>
    </div>

    {{-- Current subscription notice --}}
    @if($currentSubscription)
    <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-2xl text-center">
        <p class="text-sm text-primary-700 dark:text-primary-300">
            You're currently on the <strong>{{ ucfirst($currentSubscription->plan?->name ?? auth()->user()->subscription_plan) }}</strong> plan.
            Renews {{ $currentSubscription->current_period_end->format('d M Y') }}.
        </p>
    </div>
    @endif

    {{-- Plans --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="relative bg-white dark:bg-gray-900 rounded-3xl border-2 p-6 transition-all hover:shadow-card
                    {{ $plan->is_popular ? 'border-primary-500 shadow-lg scale-105' : 'border-gray-200 dark:border-gray-800' }}">

            @if($plan->is_popular)
            <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                MOST POPULAR
            </div>
            @endif

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: {{ $plan->color }}20">
                    <svg class="w-5 h-5" style="color: {{ $plan->color }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $plan->description }}</p>
                </div>
            </div>

            <div class="mb-5">
                <div x-show="billing === 'monthly'">
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($plan->price_monthly, 2) }}</span>
                    <span class="text-gray-400 text-sm">/month</span>
                </div>
                <div x-show="billing === 'yearly'" x-cloak>
                    <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($plan->price_yearly / 12, 2) }}</span>
                    <span class="text-gray-400 text-sm">/month</span>
                    <p class="text-xs text-green-500 mt-0.5">Billed ${{ number_format($plan->price_yearly, 2) }}/year</p>
                </div>
            </div>

            {{-- Features --}}
            <ul class="space-y-2 mb-6">
                @foreach(($plan->features ?? []) as $feature)
                <li class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>

            @if(auth()->user()->subscription_plan === $plan->slug)
            <button disabled class="w-full py-3 rounded-xl font-semibold text-sm bg-gray-100 dark:bg-gray-800 text-gray-400 cursor-not-allowed">
                Current Plan
            </button>
            @else
            <form method="POST" action="{{ route('subscription.checkout') }}" x-data>
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                <input type="hidden" name="billing_cycle" :value="billing">
                <input type="hidden" name="gateway" value="stripe">

                <button type="submit"
                        class="w-full py-3 rounded-xl font-semibold text-sm transition-all
                               {{ $plan->is_popular ? 'bg-primary-500 hover:bg-primary-600 text-white shadow-lg' : 'border-2 border-primary-500 text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20' }}">
                    {{ $plan->price_monthly == 0 ? 'Get Started Free' : 'Upgrade Now' }}
                </button>
            </form>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Payment methods --}}
    <div class="text-center mt-8">
        <p class="text-xs text-gray-400 mb-3">Secure payment via</p>
        <div class="flex items-center justify-center gap-4 flex-wrap">
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg">STRIPE</span>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg">PAYSTACK</span>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg">FLUTTERWAVE</span>
            <span class="text-xs font-semibold text-gray-500 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-lg">PAYPAL</span>
        </div>
    </div>

    @if($currentSubscription && $currentSubscription->status === 'active')
    <div class="text-center mt-6">
        <form method="POST" action="{{ route('subscription.cancel-sub') }}" onsubmit="return confirm('Are you sure you want to cancel your subscription?')">
            @csrf
            <button type="submit" class="text-sm text-red-500 hover:text-red-600 underline">
                Cancel subscription
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
