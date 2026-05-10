@extends('layouts.app')
@section('title', 'Subscription Active')

@section('content')
<div class="max-w-md mx-auto px-4 py-16 text-center">
    <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">You're all set!</h1>
    <p class="text-gray-500 dark:text-gray-400 mb-8">
        Your subscription has been activated. Enjoy all the premium features of VoxChat.
    </p>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <a href="{{ route('chats.index') }}" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-colors">
            Start Chatting
        </a>
        <a href="{{ route('subscription.billing') }}" class="px-6 py-3 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            View Billing
        </a>
    </div>
</div>
@endsection
