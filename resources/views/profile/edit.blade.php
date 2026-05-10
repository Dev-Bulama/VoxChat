@extends('layouts.app')
@section('title', 'Edit Profile')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 pb-24 lg:pb-6">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('profile.show', auth()->user()->username) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>
    </div>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl text-sm text-green-700 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-4">
        {{-- Avatar + Cover --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Photos</h2>
            <div class="flex items-center gap-6">
                <div class="text-center">
                    <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-20 h-20 rounded-2xl object-cover mb-2">
                    <form method="POST" action="{{ route('profile.avatar') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="text-xs text-primary-500 cursor-pointer hover:text-primary-600 font-medium">
                            Change
                            <input type="file" name="avatar" accept="image/*" class="hidden" onchange="this.form.submit()">
                        </label>
                    </form>
                </div>
            </div>
        </div>

        {{-- Basic info --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h2>
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Username</label>
                            <input type="text" name="username" value="{{ old('username', auth()->user()->username) }}"
                                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bio</label>
                        <textarea name="bio" rows="2" maxlength="200"
                                  class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('bio', auth()->user()->bio) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Message</label>
                        <input type="text" name="status_message" value="{{ old('status_message', auth()->user()->status_message) }}" maxlength="100"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="What's on your mind?">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                        <input type="text" name="country" value="{{ old('country', auth()->user()->country) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors text-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Privacy --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Privacy</h2>
            <form method="POST" action="{{ route('profile.privacy') }}">
                @csrf
                <div class="space-y-3">
                    @foreach([
                        ['last_seen_privacy', 'Last Seen', auth()->user()->last_seen_privacy],
                        ['avatar_privacy', 'Profile Photo', auth()->user()->avatar_privacy],
                        ['bio_privacy', 'Bio', auth()->user()->bio_privacy],
                        ['status_privacy', 'Status', auth()->user()->status_privacy],
                    ] as [$field, $label, $value])
                    <div class="flex items-center justify-between">
                        <label class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</label>
                        <select name="{{ $field }}" class="px-3 py-1.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white">
                            <option value="everyone" @selected($value === 'everyone')>Everyone</option>
                            <option value="contacts" @selected($value === 'contacts')>Contacts</option>
                            <option value="nobody" @selected($value === 'nobody')>Nobody</option>
                        </select>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="w-full mt-4 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-xl transition-colors text-sm">
                    Save Privacy
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
