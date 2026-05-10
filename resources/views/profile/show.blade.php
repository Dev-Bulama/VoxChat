@extends('layouts.app')
@section('title', $profile->name)

@section('content')
<div class="max-w-2xl mx-auto pb-24 lg:pb-0" x-data="{ tab: 'about' }">

    {{-- Cover photo --}}
    <div class="relative h-48 bg-gradient-to-br from-primary-400 to-purple-600 lg:rounded-b-3xl overflow-hidden">
        @if($profile->cover_photo)
        <img src="{{ $profile->cover_photo_url }}" alt="" class="w-full h-full object-cover">
        @endif

        {{-- Back button --}}
        <a href="javascript:history.back()" class="absolute top-4 left-4 w-9 h-9 bg-black/30 backdrop-blur-sm rounded-full flex items-center justify-center text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
    </div>

    {{-- Profile section --}}
    <div class="px-4">
        <div class="flex items-end justify-between -mt-12 mb-4">
            <div class="relative">
                <img src="{{ $profile->avatar_url }}" alt=""
                     class="w-24 h-24 rounded-2xl object-cover border-4 border-white dark:border-gray-950 shadow-lg">
                @if($profile->is_online)
                <span class="absolute bottom-1 right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-white dark:border-gray-950"></span>
                @endif
            </div>

            <div class="flex gap-2 mb-2">
                @if(auth()->id() !== $profile->id)
                <form method="POST" action="{{ route('chats.create') }}" id="chatForm">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $profile->id }}">
                </form>
                <button onclick="document.getElementById('chatForm').submit()"
                        class="flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"/></svg>
                    Message
                </button>
                @else
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-2 px-4 py-2 border-2 border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit Profile
                </a>
                @endif
            </div>
        </div>

        {{-- Name & username --}}
        <div class="mb-4">
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $profile->name }}</h1>
                @if($profile->is_verified)
                <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @endif
            </div>
            <p class="text-sm text-gray-400">@{{ $profile->username }}</p>
            @if($profile->bio)
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ $profile->bio }}</p>
            @endif
            @if($profile->status_message)
            <p class="text-sm text-primary-500 italic mt-1">"{{ $profile->status_message }}"</p>
            @endif

            <div class="flex items-center gap-4 mt-3 text-xs text-gray-400">
                @if($profile->country)
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    {{ $profile->country }}
                </span>
                @endif
                <span>Joined {{ $profile->created_at->format('M Y') }}</span>
                <span class="{{ $profile->is_online ? 'text-green-500' : '' }}">{{ $profile->is_online ? 'Online' : $profile->last_seen_formatted }}</span>
            </div>
        </div>

        {{-- Plan badge --}}
        @if($profile->subscription_plan !== 'free')
        <div class="mb-4">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold
                {{ $profile->subscription_plan === 'business' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/></svg>
                {{ ucfirst($profile->subscription_plan) }}
            </span>
        </div>
        @endif
    </div>
</div>
@endsection
