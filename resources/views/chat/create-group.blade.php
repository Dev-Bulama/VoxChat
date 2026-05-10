@extends('layouts.app')
@section('title', 'New Group Chat')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6 pb-24 lg:pb-6" x-data="{
    search: '',
    selected: [],
    results: [],
    loading: false,
    async fetchUsers() {
        if (this.search.length < 2) { this.results = []; return; }
        this.loading = true;
        const res = await fetch('/chats/search?q=' + encodeURIComponent(this.search), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        this.results = data.users || [];
        this.loading = false;
    },
    toggle(user) {
        const idx = this.selected.findIndex(u => u.id === user.id);
        if (idx >= 0) this.selected.splice(idx, 1);
        else this.selected.push(user);
    },
    isSelected(id) { return this.selected.some(u => u.id === id); }
}">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('chats.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">New Group Chat</h1>
    </div>

    <form method="POST" action="{{ route('chats.group.create') }}" enctype="multipart/form-data">
        @csrf

        {{-- Group name --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 mb-4">
            <div class="flex items-center gap-4">
                <label for="avatar" class="relative cursor-pointer flex-shrink-0">
                    <div class="w-14 h-14 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                        <svg class="w-7 h-7 text-primary-400" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/></svg>
                    </div>
                    <input type="file" id="avatar" name="avatar" accept="image/*" class="hidden">
                </label>
                <input type="text" name="name" required maxlength="100" placeholder="Group name"
                       class="flex-1 px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                       value="{{ old('name') }}">
            </div>
        </div>

        {{-- Selected members --}}
        <template x-if="selected.length > 0">
            <div class="mb-4 flex flex-wrap gap-2">
                <template x-for="u in selected" :key="u.id">
                    <span class="flex items-center gap-1.5 px-3 py-1.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full text-sm font-medium">
                        <span x-text="u.name"></span>
                        <button type="button" @click="toggle(u)" class="text-primary-500 hover:text-primary-700">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </button>
                        <input type="hidden" name="members[]" :value="u.id">
                    </span>
                </template>
            </div>
        </template>

        {{-- Search --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-4">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                <input type="text" x-model="search" @input.debounce.300ms="fetchUsers"
                       placeholder="Search people to add..."
                       class="w-full bg-transparent text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none">
            </div>

            <div x-show="loading" class="px-4 py-6 text-center text-sm text-gray-400">Searching...</div>

            <div x-show="results.length > 0 && !loading" class="divide-y divide-gray-50 dark:divide-gray-800 max-h-64 overflow-y-auto">
                <template x-for="user in results" :key="user.id">
                    <button type="button" @click="toggle(user)"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors text-left">
                        <img :src="user.avatar_url" class="w-9 h-9 rounded-full object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="user.name"></p>
                            <p class="text-xs text-gray-400" x-text="'@' + user.username"></p>
                        </div>
                        <div :class="isSelected(user.id) ? 'bg-primary-500' : 'border-2 border-gray-300 dark:border-gray-600'"
                             class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg x-show="isSelected(user.id)" class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                    </button>
                </template>
            </div>

            <div x-show="search.length >= 2 && results.length === 0 && !loading"
                 class="px-4 py-6 text-center text-sm text-gray-400">No users found.</div>

            <div x-show="search.length < 2"
                 class="px-4 py-4 text-center text-sm text-gray-400">Type at least 2 characters to search.</div>
        </div>

        <button type="submit" :disabled="selected.length === 0"
                :class="selected.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-primary-600'"
                class="w-full py-3 bg-primary-500 text-white font-semibold rounded-xl transition-colors">
            Create Group (<span x-text="selected.length"></span> members)
        </button>
    </form>
</div>
@endsection
