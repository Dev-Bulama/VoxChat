@extends('layouts.app')
@section('title', $community->name)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6 pb-24 lg:pb-6">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('communities.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $community->name }}</h1>
    </div>

    {{-- Community info --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-4">
        <div class="flex items-start gap-4">
            @if($community->avatar)
            <img src="{{ Storage::url($community->avatar) }}" alt="" class="w-16 h-16 rounded-2xl object-cover flex-shrink-0">
            @else
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                <span class="text-2xl font-bold text-white">{{ substr($community->name, 0, 1) }}</span>
            </div>
            @endif

            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-gray-900 dark:text-white">{{ $community->name }}</h2>
                    @if(!$community->is_public)
                    <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                    @endif
                </div>
                <p class="text-sm text-gray-400 mt-0.5">{{ $members->total() }} members · Created by {{ $community->creator->name ?? 'Unknown' }}</p>
                @if($community->description)
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ $community->description }}</p>
                @endif
            </div>
        </div>

        @php $isMember = $community->members()->where('user_id', auth()->id())->exists(); @endphp

        <div class="mt-4 flex gap-3">
            @if($isMember)
            <form method="POST" action="{{ route('communities.leave', $community) }}" onsubmit="return confirm('Leave this community?')">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium border-2 border-red-400 text-red-500 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20">
                    Leave
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('communities.join', $community) }}">
                @csrf
                <button type="submit" class="px-4 py-2 text-sm font-medium bg-primary-500 hover:bg-primary-600 text-white rounded-xl">
                    Join Community
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Members --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Members</h3>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($members as $member)
            <div class="flex items-center gap-3 px-5 py-3">
                <img src="{{ $member->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $member->name }}</p>
                    <p class="text-xs text-gray-400">@{{ $member->username }}</p>
                </div>
                @php $role = $member->pivot->role ?? 'member'; @endphp
                @if(in_array($role, ['owner', 'admin']))
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                    {{ $role === 'owner' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                    {{ ucfirst($role) }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
        @if($members->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">
            {{ $members->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
