@extends('layouts.app')
@section('title', 'Communities')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6 pb-24 lg:pb-6">

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Communities</h1>
        <a href="{{ route('communities.create') }}"
           class="flex items-center gap-2 px-4 py-2 bg-primary-500 hover:bg-primary-600 text-white text-sm font-medium rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Community
        </a>
    </div>

    {{-- My Communities --}}
    @if($myCommunities->isNotEmpty())
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">My Communities</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($myCommunities as $community)
            <a href="{{ route('communities.show', $community) }}"
               class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-card transition-all group">
                <div class="flex items-start gap-3">
                    @if($community->avatar)
                    <img src="{{ Storage::url($community->avatar) }}" alt="" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                    @else
                    <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center flex-shrink-0">
                        <span class="text-xl font-bold text-primary-500">{{ substr($community->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $community->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $community->members_count ?? 0 }} members</p>
                        @if($community->description)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $community->description }}</p>
                        @endif
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Discover --}}
    <div>
        <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Discover</h2>

        @if($discoverCommunities->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm">No more communities to discover.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($discoverCommunities as $community)
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5">
                <div class="flex items-start gap-3 mb-4">
                    @if($community->avatar)
                    <img src="{{ Storage::url($community->avatar) }}" alt="" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                    @else
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                        <span class="text-xl font-bold text-white">{{ substr($community->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-gray-900 dark:text-white truncate">{{ $community->name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $community->members_count }} members</p>
                    </div>
                </div>

                @if($community->description)
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">{{ $community->description }}</p>
                @endif

                <form method="POST" action="{{ route('communities.join', $community) }}">
                    @csrf
                    <button type="submit" class="w-full py-2 rounded-xl text-sm font-medium border-2 border-primary-500 text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                        Join Community
                    </button>
                </form>
            </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $discoverCommunities->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
