@extends('layouts.app')
@section('title', 'Status')

@section('content')
<div class="flex flex-col h-screen pb-16 lg:pb-0 overflow-hidden" x-data="statusPage()">

    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 safe-top">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Status</h1>
        <button @click="showCreate = true"
                class="p-2 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>

    <div class="flex-1 overflow-y-auto">
        {{-- My Status --}}
        <div class="px-4 py-3">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">My Status</h3>
            <div class="flex items-center gap-3 cursor-pointer" @click="@if($myStories->count() > 0) viewMyStory() @else showCreate = true @endif">
                <div class="relative flex-shrink-0">
                    @if($myStories->count() > 0)
                    <div class="story-ring">
                        <div class="w-14 h-14 rounded-full overflow-hidden bg-white p-0.5">
                            <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full rounded-full object-cover">
                        </div>
                    </div>
                    @else
                    <div class="w-14 h-14 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-700">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute bottom-0 right-0 w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                    </div>
                    @endif
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white">My status</p>
                    <p class="text-sm text-gray-400">
                        @if($myStories->count() > 0)
                            {{ $myStories->count() }} update{{ $myStories->count() > 1 ? 's' : '' }} • Tap to view
                        @else
                            Tap to add status update
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Recent Updates --}}
        @if($contactStories->count() > 0)
        <div class="px-4 py-2">
            <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Recent Updates</h3>
        </div>

        @foreach($contactStories as $userId => $data)
        @php $story = $data['stories']->first(); $hasUnviewed = $data['has_unviewed'] ?? true; @endphp
        <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer transition-colors"
             @click="viewStory({{ json_encode($data['stories']->pluck('id')->toArray()) }})">
            <div class="{{ $hasUnviewed ? 'story-ring' : 'story-ring-viewed' }} flex-shrink-0">
                <div class="w-14 h-14 rounded-full overflow-hidden bg-white p-0.5">
                    <img src="{{ $story->user->avatar_url }}" class="w-full h-full rounded-full object-cover">
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white">{{ $story->user->name }}</p>
                <p class="text-sm text-gray-400">{{ $story->created_at->diffForHumans() }}</p>
            </div>
        </div>
        @endforeach
        @endif

        @if($myStories->isEmpty() && $contactStories->isEmpty())
        <div class="flex flex-col items-center justify-center h-64 text-center px-6">
            <div class="w-20 h-20 bg-primary-50 dark:bg-primary-900/20 rounded-3xl flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">No status updates</h3>
            <p class="text-sm text-gray-400 mt-1">Be the first to share a status!</p>
        </div>
        @endif
    </div>

    {{-- FAB --}}
    <div class="fixed bottom-20 lg:bottom-6 right-4">
        <button @click="showCreate = true"
                class="w-14 h-14 bg-primary-500 rounded-full flex items-center justify-center shadow-xl hover:bg-primary-600 transition-colors">
            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>

    {{-- Story Viewer --}}
    <div x-show="viewing" x-cloak
         class="fixed inset-0 z-50 bg-black flex flex-col">
        <div class="flex-1 relative overflow-hidden" @click="nextStory()">
            {{-- Progress bars --}}
            <div class="absolute top-0 left-0 right-0 z-10 flex gap-1 p-2">
                <template x-for="(story, i) in currentStories" :key="story.id">
                    <div class="flex-1 h-0.5 bg-white/30 rounded-full overflow-hidden">
                        <div class="h-full bg-white rounded-full transition-all"
                             :style="`width: ${i < currentIndex ? 100 : (i === currentIndex ? storyProgress : 0)}%`"></div>
                    </div>
                </template>
            </div>

            {{-- User info --}}
            <div class="absolute top-6 left-0 right-0 z-10 flex items-center gap-3 px-4">
                <img :src="currentStory?.user?.avatar_url" class="w-9 h-9 rounded-full object-cover ring-2 ring-white">
                <div>
                    <p class="text-white font-medium text-sm" x-text="currentStory?.user?.name"></p>
                    <p class="text-white/60 text-xs" x-text="currentStoryTime"></p>
                </div>
                <div class="flex-1"></div>
                <button @click.stop="closeStory()" class="p-1 text-white/80">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Story content --}}
            <div class="w-full h-full flex items-center justify-center"
                 :style="currentStory?.background_color ? `background: ${currentStory.background_color}` : 'background: #111'">
                <img x-show="currentStory?.type === 'image'" :src="currentStory?.media_url_full" class="w-full h-full object-contain" x-cloak>
                <video x-show="currentStory?.type === 'video'" :src="currentStory?.media_url_full" class="w-full h-full object-contain" autoplay x-cloak></video>
                <p x-show="currentStory?.type === 'text'" x-text="currentStory?.content"
                   class="text-white text-2xl font-bold text-center px-8 max-w-sm" x-cloak></p>
            </div>

            {{-- Navigation areas --}}
            <div class="absolute inset-y-0 left-0 w-1/3" @click.stop="prevStory()"></div>
            <div class="absolute inset-y-0 right-0 w-1/3" @click.stop="nextStory()"></div>
        </div>

        {{-- Reply input --}}
        <div class="p-4 flex items-center gap-3">
            <input type="text" x-model="storyReply" placeholder="Reply to status..."
                   class="flex-1 bg-white/20 text-white placeholder-white/50 rounded-full px-4 py-2.5 text-sm border-0 outline-none">
            <button @click="sendReply()" class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/></svg>
            </button>
        </div>
    </div>

    {{-- Create Status Modal --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 p-4"
         @click.self="showCreate = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl animate-slide-in-up" @click.stop>
            <div class="flex items-center justify-between p-5 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-gray-900 dark:text-white">Create Status</h3>
                <button @click="showCreate = false"><svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <div class="grid grid-cols-2 gap-3 p-5">
                <label class="flex flex-col items-center gap-2 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-2xl cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-300 text-sm">Photo</span>
                    <input type="file" class="hidden" accept="image/*" @change="uploadStatus($event, 'image')">
                </label>
                <label class="flex flex-col items-center gap-2 p-4 bg-purple-50 dark:bg-purple-900/20 rounded-2xl cursor-pointer hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                    <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-300 text-sm">Video</span>
                    <input type="file" class="hidden" accept="video/*" @change="uploadStatus($event, 'video')">
                </label>
                <button @click="createTextStatus()" class="flex flex-col items-center gap-2 p-4 bg-orange-50 dark:bg-orange-900/20 rounded-2xl cursor-pointer hover:bg-orange-100 dark:hover:bg-orange-900/30 transition-colors">
                    <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-300 text-sm">Text</span>
                </button>
                <button class="flex flex-col items-center gap-2 p-4 bg-green-50 dark:bg-green-900/20 rounded-2xl cursor-pointer hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                    <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                    </div>
                    <span class="font-medium text-gray-700 dark:text-gray-300 text-sm">Music</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function statusPage() {
    return {
        showCreate: false,
        viewing: false,
        currentStories: [],
        currentIndex: 0,
        storyProgress: 0,
        storyTimer: null,
        storyReply: '',

        get currentStory() { return this.currentStories[this.currentIndex] || null; },
        get currentStoryTime() {
            if (!this.currentStory?.created_at) return '';
            return new Date(this.currentStory.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        viewStory(ids) {
            // Load stories by IDs
            this.currentStories = ids.map(id => ({ id }));
            this.currentIndex = 0;
            this.viewing = true;
            this.startProgress();
        },

        viewMyStory() {
            this.viewing = true;
        },

        closeStory() {
            clearInterval(this.storyTimer);
            this.viewing = false;
            this.storyProgress = 0;
        },

        startProgress() {
            clearInterval(this.storyTimer);
            this.storyProgress = 0;
            this.storyTimer = setInterval(() => {
                this.storyProgress += 100 / (5000 / 100); // 5 second per story
                if (this.storyProgress >= 100) this.nextStory();
            }, 100);
        },

        nextStory() {
            if (this.currentIndex < this.currentStories.length - 1) {
                this.currentIndex++;
                this.startProgress();
            } else {
                this.closeStory();
            }
        },

        prevStory() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.startProgress();
            }
        },

        async uploadStatus(event, type) {
            this.showCreate = false;
            const file = event.target.files[0];
            if (!file) return;
            const form = new FormData();
            form.append('type', type);
            form.append('media', file);
            await fetch('/status', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: form,
            });
            window.location.reload();
        },

        createTextStatus() {
            this.showCreate = false;
            // Open text status editor
        },

        async sendReply() {
            if (!this.storyReply.trim() || !this.currentStory) return;
            await fetch(`/status/${this.currentStory.id}/reply`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ reply_text: this.storyReply }),
            });
            this.storyReply = '';
        },
    };
}
</script>
@endpush
@endsection
