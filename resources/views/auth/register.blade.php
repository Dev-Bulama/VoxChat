@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
<div x-data="{ showPassword: false, showConfirm: false, loading: false, username: '', usernameAvailable: null, checkingUsername: false }"
     x-init="$watch('username', val => { if (val.length >= 3) checkUsername(val) })">

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Create account</h2>
        <p class="text-gray-500 text-sm mt-1">Join millions on VoxChat today</p>
    </div>

    <form method="POST" action="{{ route('register.post') }}" @submit="loading = true" class="space-y-4">
        @csrf

        <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="input-field @error('name') border-red-400 @enderror"
                       placeholder="John Doe" required>
                @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>

            <div class="col-span-2 sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">@</span>
                    <input type="text" name="username" x-model="username" value="{{ old('username') }}"
                           class="input-field pl-7 @error('username') border-red-400 @enderror"
                           placeholder="yourname" required pattern="[a-zA-Z0-9_]+">
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg x-show="checkingUsername" class="w-4 h-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <svg x-show="usernameAvailable === true && !checkingUsername" class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20" x-cloak><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <svg x-show="usernameAvailable === false && !checkingUsername" class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20" x-cloak><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    </div>
                </div>
                @error('username')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="input-field @error('email') border-red-400 @enderror"
                   placeholder="you@example.com" required>
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" name="password"
                       class="input-field pr-12 @error('password') border-red-400 @enderror"
                       placeholder="At least 8 characters" required>
                <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm Password</label>
            <div class="relative">
                <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation"
                       class="input-field pr-12"
                       placeholder="Repeat your password" required>
                <button type="button" @click="showConfirm = !showConfirm" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </button>
            </div>
        </div>

        <div class="flex items-start gap-2">
            <input type="checkbox" required id="terms" class="mt-0.5 w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <label for="terms" class="text-xs text-gray-500">I agree to the <a href="#" class="text-primary-600 underline">Terms of Service</a> and <a href="#" class="text-primary-600 underline">Privacy Policy</a></label>
        </div>

        <button type="submit" class="btn-primary flex items-center justify-center gap-2" :disabled="loading">
            <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            <span x-text="loading ? 'Creating account...' : 'Create Account'">Create Account</span>
        </button>
    </form>

    <p class="text-center text-sm text-gray-500 mt-5">
        Already have an account?
        <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:text-primary-700">Sign in</a>
    </p>
</div>

@push('scripts')
<script>
    function checkUsername(val) {
        // Username availability check would call an API endpoint
    }
</script>
@endpush
@endsection
