@extends('layouts.app')
@section('title', 'Chats')

@section('content')
<div class="flex h-screen pb-16 lg:pb-0 overflow-hidden" x-data="chatList()">

    {{-- Chat List Sidebar --}}
    <div class="w-full lg:w-80 xl:w-96 flex flex-col border-r border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex-shrink-0"
         :class="{ 'hidden lg:flex': activeChatId }">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 safe-top">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Chats</h1>
            <div class="flex items-center gap-1">
                <button @click="showSearch = !showSearch"
                        class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
                <button @click="showNewChat = true"
                        class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>

        {{-- Search Bar --}}
        <div x-show="showSearch" x-transition class="px-4 py-2 border-b border-gray-100 dark:border-gray-800">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" x-model="searchQuery" @input.debounce.300ms="search()"
                       placeholder="Search chats or people..."
                       class="w-full pl-9 pr-4 py-2.5 bg-gray-100 dark:bg-gray-800 rounded-xl text-sm border-0 outline-none focus:ring-2 focus:ring-primary-500 text-gray-900 dark:text-white placeholder-gray-400">
            </div>
        </div>

        {{-- Stories Strip --}}
        @if($stories->count() > 0 || auth()->user()->activeStories->count() > 0)
        <div class="overflow-x-auto flex gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-800 no-scrollbar">
            {{-- My Story --}}
            <div class="flex flex-col items-center gap-1.5 flex-shrink-0 cursor-pointer" @click="window.location='{{ route('status.index') }}'">
                <div class="relative">
                    @if(auth()->user()->activeStories->count() > 0)
                    <div class="story-ring">
                        <div class="w-12 h-12 rounded-full overflow-hidden bg-white p-0.5">
                            <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full rounded-full object-cover">
                        </div>
                    </div>
                    @else
                    <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-700">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute -bottom-0.5 -right-0.5 w-5 h-5 bg-primary-500 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900">
                        <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    </div>
                    @endif
                </div>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">My Status</span>
            </div>

            {{-- Contact stories --}}
            @foreach($stories->take(10) as $userId => $group)
            @php $storyUser = $group->first()->user; $hasUnviewed = $group['has_unviewed'] ?? true; @endphp
            <div class="flex flex-col items-center gap-1.5 flex-shrink-0 cursor-pointer"
                 @click="window.location='{{ route('status.index') }}'">
                <div class="{{ $hasUnviewed ? 'story-ring' : 'story-ring-viewed' }}">
                    <div class="w-12 h-12 rounded-full overflow-hidden bg-white p-0.5">
                        <img src="{{ $storyUser->avatar_url }}" class="w-full h-full rounded-full object-cover">
                    </div>
                </div>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium w-12 truncate text-center">{{ $storyUser->name }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Chat List --}}
        <div class="flex-1 overflow-y-auto" id="chat-list">
            {{-- Search Results --}}
            <div x-show="searchQuery && searchResults" x-cloak>
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">People</div>
                <template x-for="user in searchResults?.users" :key="user.id">
                    <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
                         @click="startChat(user.id)">
                        <div class="relative flex-shrink-0">
                            <img :src="user.avatar_url" class="w-12 h-12 rounded-full object-cover">
                            <div x-show="user.is_online" class="online-dot"></div>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white" x-text="user.name"></p>
                            <p class="text-xs text-gray-400" x-text="'@' + user.username"></p>
                        </div>
                    </div>
                </template>
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Messages</div>
            </div>

            {{-- Pinned Chats --}}
            @php $pinned = $chats->where('is_pinned', true)->where('is_archived', false); @endphp
            @if($pinned->count() > 0)
            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pinned</div>
            @foreach($pinned as $chat)
                @include('chat.partials.chat-item', ['chat' => $chat])
            @endforeach
            @endif

            {{-- Regular Chats --}}
            @php $regular = $chats->where('is_pinned', false)->where('is_archived', false); @endphp
            @foreach($regular as $chat)
                @include('chat.partials.chat-item', ['chat' => $chat])
            @endforeach

            {{-- Archived --}}
            @php $archived = $chats->where('is_archived', true); @endphp
            @if($archived->count() > 0)
            <div x-data="{ open: false }">
                <button @click="open = !open" class="w-full flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-800">
                    <svg class="w-3.5 h-3.5" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    Archived ({{ $archived->count() }})
                </button>
                <div x-show="open">
                    @foreach($archived as $chat)
                        @include('chat.partials.chat-item', ['chat' => $chat])
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Empty state --}}
            @if($chats->isEmpty())
            <div class="flex flex-col items-center justify-center h-64 text-center px-6">
                <div class="w-20 h-20 bg-primary-50 dark:bg-primary-900/20 rounded-3xl flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="font-semibold text-gray-700 dark:text-gray-300">No chats yet</h3>
                <p class="text-sm text-gray-400 mt-1">Start a conversation with someone!</p>
                <button @click="showNewChat = true"
                        class="mt-4 px-5 py-2.5 bg-primary-500 text-white rounded-xl text-sm font-medium hover:bg-primary-600 transition-colors">
                    New Chat
                </button>
            </div>
            @endif
        </div>
    </div>

    {{-- Chat Content Area (desktop) --}}
    <div class="hidden lg:flex flex-1 flex-col bg-gray-50 dark:bg-gray-950">
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center">
                <div class="w-24 h-24 bg-primary-50 dark:bg-primary-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-gray-400 dark:text-gray-500">VoxChat</h2>
                <p class="text-gray-400 dark:text-gray-600 text-sm mt-1">Select a chat to start messaging</p>
            </div>
        </div>
    </div>

    {{-- New Chat Modal --}}
    <div x-show="showNewChat" x-cloak class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         @click.self="showNewChat = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl animate-slide-in-up" @click.stop>
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white">New Conversation</h3>
                <button @click="showNewChat = false" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="p-4">
                <div class="relative mb-4">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="newChatSearch" @input.debounce.300ms="searchUsers()"
                           placeholder="Search people by name or username..."
                           class="w-full pl-9 pr-4 py-3 bg-gray-100 dark:bg-gray-800 rounded-xl text-sm border-0 outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div class="space-y-1 max-h-60 overflow-y-auto">
                    <template x-for="user in newChatUsers" :key="user.id">
                        <button @click="startChat(user.id)" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-left">
                            <div class="relative">
                                <img :src="user.avatar_url" class="w-10 h-10 rounded-full object-cover">
                                <div x-show="user.is_online" class="online-dot"></div>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white text-sm" x-text="user.name"></p>
                                <p class="text-xs text-gray-400" x-text="'@' + user.username"></p>
                            </div>
                        </button>
                    </template>
                    <div x-show="newChatSearch && newChatUsers.length === 0" class="text-center py-6 text-gray-400 text-sm">
                        No users found
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <button @click="showNewChat=false; window.location='{{ route('chats.group.new') }}'"
                            class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white text-sm">New Group</p>
                            <p class="text-xs text-gray-400">Create a group chat</p>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function chatList() {
    return {
        searchQuery: '',
        searchResults: null,
        showSearch: false,
        showNewChat: false,
        newChatSearch: '',
        newChatUsers: [],
        activeChatId: null,

        async search() {
            if (!this.searchQuery.trim()) { this.searchResults = null; return; }
            const res = await fetch(`/chats/search?q=${encodeURIComponent(this.searchQuery)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            this.searchResults = await res.json();
        },

        async searchUsers() {
            if (!this.newChatSearch.trim()) { this.newChatUsers = []; return; }
            const res = await fetch(`/chats/search?q=${encodeURIComponent(this.newChatSearch)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            this.newChatUsers = data.users || [];
        },

        async startChat(userId) {
            const res = await fetch('/chats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ user_id: userId }),
            });
            const data = await res.json();
            if (data.redirect) window.location = data.redirect;
        },
    }
}
</script>
@endpush
@endsection
