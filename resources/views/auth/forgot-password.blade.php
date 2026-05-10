@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div x-data="{ loading: false, sent: false }">
    <div x-show="!sent">
        <div class="mb-6">
            <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 mb-4">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to login
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Reset password</h2>
            <p class="text-gray-500 text-sm mt-1">Enter your email and we'll send you a reset link</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" @submit="loading = true" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="input-field @error('email') border-red-400 @enderror"
                       placeholder="you@example.com" required autofocus>
                @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="btn-primary flex items-center justify-center gap-2" :disabled="loading">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                <span x-text="loading ? 'Sending...' : 'Send Reset Link'">Send Reset Link</span>
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="text-center py-4">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 text-lg">Check your email</h3>
        <p class="text-gray-500 text-sm mt-1">We sent a password reset link to your email address.</p>
        <a href="{{ route('login') }}" class="mt-4 inline-block text-primary-600 font-medium text-sm hover:text-primary-700">← Back to login</a>
    </div>
    @endif
</div>
@endsection
