@extends('layouts.app')
@section('title', $chat->getDisplayNameFor(auth()->id()))

@section('content')
<div class="flex flex-col h-screen pb-16 lg:pb-0 overflow-hidden"
     x-data="chatRoom({{ $chat->id }}, {{ auth()->id() }})"
     x-init="init()">

    {{-- Chat Header --}}
    <div class="flex items-center gap-3 px-3 py-2.5 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 safe-top z-10 flex-shrink-0">
        {{-- Back button --}}
        <a href="{{ route('chats.index') }}" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>

        {{-- Avatar & Name --}}
        <a href="{{ $other ? route('profile.show', $other->username) : '#' }}" class="flex items-center gap-2.5 flex-1 min-w-0">
            <div class="relative flex-shrink-0">
                <img src="{{ $chat->getDisplayAvatarFor(auth()->id()) }}" class="w-10 h-10 rounded-full object-cover">
                @if($other?->is_online)
                <div class="online-dot"></div>
                @endif
            </div>
            <div class="min-w-0">
                <h2 class="font-semibold text-[15px] text-gray-900 dark:text-white truncate">
                    {{ $chat->getDisplayNameFor(auth()->id()) }}
                </h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 truncate" id="status-text">
                    <span x-text="statusText">
                        @if($other)
                            {{ $other->is_online ? 'online' : $other->last_seen_formatted }}
                        @elseif($group)
                            {{ $chat->activeParticipants()->count() }} members
                        @endif
                    </span>
                </p>
            </div>
        </a>

        {{-- Action buttons --}}
        <div class="flex items-center gap-1 flex-shrink-0">
            @if($other || $group)
            <button @click="initiateCall('voice')"
                    class="p-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </button>
            <button @click="initiateCall('video')"
                    class="p-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
            @endif
            <button @click="showMenu = !showMenu"
                    class="p-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500 dark:text-gray-400 relative">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/></svg>

                {{-- Dropdown menu --}}
                <div x-show="showMenu" @click.away="showMenu = false" x-cloak
                     class="absolute top-full right-0 mt-1 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-card border border-gray-100 dark:border-gray-700 py-1 z-50">
                    <a href="#" @click.prevent="searchMessages()" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search messages
                    </a>
                    <button @click.prevent="pinChat()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        Pin chat
                    </button>
                    <button @click.prevent="muteChat()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        Mute notifications
                    </button>
                    <hr class="my-1 border-gray-100 dark:border-gray-700">
                    <button @click.prevent="deleteChat()" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete chat
                    </button>
                </div>
            </button>
        </div>
    </div>

    {{-- Messages Container --}}
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-2" id="messages-container"
         @scroll="onScroll($event)">

        {{-- Load more --}}
        <div id="load-more" class="text-center py-2" x-show="hasMoreMessages">
            <button @click="loadMore()" class="text-xs text-primary-500 font-medium px-3 py-1.5 bg-primary-50 dark:bg-primary-900/20 rounded-full">
                Load older messages
            </button>
        </div>

        {{-- Messages --}}
        <div id="messages-list">
            @foreach($messages as $message)
                @include('chat.partials.message', ['message' => $message, 'user' => auth()->user()])
            @endforeach
        </div>

        {{-- Typing indicator --}}
        <div x-show="typingUsers.length > 0" x-cloak class="flex items-end gap-2 ml-1 animate-fade-in">
            <div class="flex items-center gap-1.5 msg-in px-4 py-3 rounded-2xl">
                <div class="flex gap-1">
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full typing-dot"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full typing-dot"></div>
                    <div class="w-1.5 h-1.5 bg-gray-400 rounded-full typing-dot"></div>
                </div>
                <span class="text-xs text-gray-400 ml-1" x-text="typingText"></span>
            </div>
        </div>

        {{-- Anchor for scroll to bottom --}}
        <div id="messages-end"></div>
    </div>

    {{-- Reply Preview --}}
    <div x-show="replyTo" x-cloak
         class="flex items-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
        <div class="w-1 h-8 bg-primary-500 rounded-full flex-shrink-0"></div>
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-primary-500" x-text="replyTo?.sender?.name"></p>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="replyTo?.body_preview"></p>
        </div>
        <button @click="replyTo = null" class="p-1 text-gray-400 hover:text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Message Input --}}
    <div class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 px-3 py-2.5 pb-safe flex-shrink-0">
        <div class="flex items-end gap-2">
            {{-- Attachment button --}}
            <button @click="showAttachMenu = !showAttachMenu"
                    class="p-2.5 rounded-xl text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 flex-shrink-0 relative">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>

                {{-- Attachment menu --}}
                <div x-show="showAttachMenu" @click.away="showAttachMenu = false" x-cloak
                     class="absolute bottom-full left-0 mb-2 bg-white dark:bg-gray-800 rounded-2xl shadow-card border border-gray-100 dark:border-gray-700 p-2 grid grid-cols-3 gap-1 w-48">
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-center">
                        <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-[10px] text-gray-500 font-medium">Photo</span>
                        <input type="file" class="hidden" accept="image/*" @change="sendMedia($event, 'image')">
                    </label>
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-center">
                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-[10px] text-gray-500 font-medium">Video</span>
                        <input type="file" class="hidden" accept="video/*" @change="sendMedia($event, 'video')">
                    </label>
                    <label class="flex flex-col items-center gap-1 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer text-center">
                        <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-[10px] text-gray-500 font-medium">File</span>
                        <input type="file" class="hidden" @change="sendMedia($event, 'file')">
                    </label>
                </div>
            </button>

            {{-- Text input --}}
            <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-2xl px-4 py-2.5 min-h-[44px] max-h-32 overflow-hidden">
                <textarea x-ref="msgInput" x-model="messageText"
                          @keydown.enter.prevent.exact="send()"
                          @keydown.enter.shift.prevent="messageText += '\n'"
                          @input="autoResize(); sendTyping()"
                          placeholder="Message..."
                          rows="1"
                          class="w-full bg-transparent text-[15px] text-gray-900 dark:text-white placeholder-gray-400 resize-none outline-none leading-relaxed"></textarea>
            </div>

            {{-- Voice note / Send button --}}
            <div class="flex-shrink-0">
                <button x-show="!messageText.trim()" @click="toggleVoiceNote()"
                        :class="{ 'bg-red-500 text-white': recording, 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800': !recording }"
                        class="p-2.5 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                    </svg>
                </button>

                <button x-show="messageText.trim()" @click="send()"
                        class="w-10 h-10 bg-primary-500 hover:bg-primary-600 rounded-xl flex items-center justify-center text-white transition-colors shadow-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Voice Recording UI --}}
        <div x-show="recording" x-cloak class="flex items-center gap-3 px-2 py-2 animate-fade-in">
            <div class="flex items-center gap-1">
                <div class="waveform-bar" style="height:8px; animation-delay:0s"></div>
                <div class="waveform-bar" style="height:16px; animation-delay:0.1s"></div>
                <div class="waveform-bar" style="height:12px; animation-delay:0.2s"></div>
                <div class="waveform-bar" style="height:20px; animation-delay:0.3s"></div>
                <div class="waveform-bar" style="height:14px; animation-delay:0.4s"></div>
                <div class="waveform-bar" style="height:18px; animation-delay:0.5s"></div>
                <div class="waveform-bar" style="height:10px; animation-delay:0.6s"></div>
            </div>
            <span class="text-sm font-medium text-red-500" x-text="recordingDuration"></span>
            <div class="flex-1"></div>
            <button @click="cancelRecording()" class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1">Cancel</button>
            <button @click="sendVoiceNote()" class="px-3 py-1.5 bg-primary-500 text-white text-xs font-medium rounded-xl">Send</button>
        </div>
    </div>

    {{-- Emoji Picker --}}
    <div x-show="showEmojiPicker" @click.away="showEmojiPicker = false" x-cloak
         class="fixed bottom-24 right-4 z-50 bg-white dark:bg-gray-800 rounded-2xl shadow-card border border-gray-100 dark:border-gray-700 p-3">
        <div class="grid grid-cols-8 gap-1.5">
            @foreach(['😀','😂','🥹','😍','🥰','😘','😎','🤩','😜','🤔','😔','😭','🔥','❤️','👍','👎','🙏','🎉','✅','⭐','💯','🚀','💪','😈','🤝','🫶','💬','🎵','📷','🎮','💰','🌟'] as $emoji)
            <button @click="addEmoji('{{ $emoji }}')" class="w-8 h-8 text-xl hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors">{{ $emoji }}</button>
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
function chatRoom(chatId, userId) {
    return {
        chatId,
        userId,
        messageText: '',
        replyTo: null,
        typingUsers: [],
        typingText: '',
        typingTimeout: null,
        recording: false,
        mediaRecorder: null,
        audioChunks: [],
        recordingTimer: null,
        recordingSeconds: 0,
        hasMoreMessages: {{ $messages->hasMorePages() ? 'true' : 'false' }},
        currentPage: 1,
        showMenu: false,
        showAttachMenu: false,
        showEmojiPicker: false,
        statusText: '{{ $other ? ($other->is_online ? "online" : $other->last_seen_formatted) : ($group ? $chat->activeParticipants()->count() . " members" : "") }}',

        get recordingDuration() {
            const m = Math.floor(this.recordingSeconds / 60).toString().padStart(2, '0');
            const s = (this.recordingSeconds % 60).toString().padStart(2, '0');
            return m + ':' + s;
        },

        init() {
            this.scrollToBottom();
            this.listenForMessages();
            this.markRead();
        },

        scrollToBottom(smooth = false) {
            this.$nextTick(() => {
                const el = document.getElementById('messages-end');
                el?.scrollIntoView({ behavior: smooth ? 'smooth' : 'instant' });
            });
        },

        autoResize() {
            const ta = this.$refs.msgInput;
            ta.style.height = 'auto';
            ta.style.height = Math.min(ta.scrollHeight, 128) + 'px';
        },

        addEmoji(emoji) {
            this.messageText += emoji;
            this.showEmojiPicker = false;
            this.$refs.msgInput?.focus();
        },

        async send() {
            if (!this.messageText.trim()) return;
            const text = this.messageText.trim();
            this.messageText = '';
            this.$nextTick(() => { if (this.$refs.msgInput) { this.$refs.msgInput.style.height = 'auto'; } });

            const body = {
                type: 'text',
                body: text,
                reply_to_id: this.replyTo?.id || null,
            };
            this.replyTo = null;

            const res = await this.apiPost(`/chats/${this.chatId}/messages`, body);
            if (res?.html) {
                document.getElementById('messages-list').insertAdjacentHTML('beforeend', res.html);
                this.scrollToBottom(true);
            }
        },

        async sendMedia(event, type) {
            this.showAttachMenu = false;
            const file = event.target.files[0];
            if (!file) return;

            const form = new FormData();
            form.append('type', type);
            form.append('media', file);
            if (this.replyTo) form.append('reply_to_id', this.replyTo.id);

            const res = await fetch(`/chats/${this.chatId}/messages`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: form,
            });
            const data = await res.json();
            if (data.html) {
                document.getElementById('messages-list').insertAdjacentHTML('beforeend', data.html);
                this.scrollToBottom(true);
            }
        },

        sendTyping() {
            clearTimeout(this.typingTimeout);
            fetch(`/chats/${this.chatId}/messages/typing`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            });
            this.typingTimeout = setTimeout(() => {}, 3000);
        },

        async toggleVoiceNote() {
            if (this.recording) { this.sendVoiceNote(); return; }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.audioChunks = [];
                this.mediaRecorder = new MediaRecorder(stream);
                this.mediaRecorder.ondataavailable = e => this.audioChunks.push(e.data);
                this.mediaRecorder.start();
                this.recording = true;
                this.recordingSeconds = 0;
                this.recordingTimer = setInterval(() => this.recordingSeconds++, 1000);
            } catch { alert('Microphone access denied.'); }
        },

        async sendVoiceNote() {
            if (!this.mediaRecorder) return;
            this.mediaRecorder.stop();
            this.mediaRecorder.onstop = async () => {
                clearInterval(this.recordingTimer);
                this.recording = false;
                const blob = new Blob(this.audioChunks, { type: 'audio/webm' });
                const form = new FormData();
                form.append('type', 'voice_note');
                form.append('media', blob, 'voice-note.webm');
                const res = await fetch(`/chats/${this.chatId}/messages`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
                    body: form,
                });
                const data = await res.json();
                if (data.html) {
                    document.getElementById('messages-list').insertAdjacentHTML('beforeend', data.html);
                    this.scrollToBottom(true);
                }
            };
        },

        cancelRecording() {
            if (this.mediaRecorder) { this.mediaRecorder.stop(); }
            clearInterval(this.recordingTimer);
            this.recording = false;
            this.audioChunks = [];
        },

        async initiateCall(type) {
            const res = await this.apiPost('/calls/initiate', { chat_id: this.chatId, type });
            if (res?.room_id) {
                window.open(`/calls/${res.call.id}/room`, '_blank', 'width=900,height=700');
            }
        },

        async markRead() {
            await fetch(`/chats/${this.chatId}/messages/read`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'X-Requested-With': 'XMLHttpRequest' },
            });
        },

        listenForMessages() {
            if (typeof Echo === 'undefined') return;

            Echo.private(`chat.${this.chatId}`)
                .listen('.message.sent', (e) => {
                    if (e.sender_id === this.userId) return;
                    // Render incoming message
                    const html = this.renderMessage(e);
                    document.getElementById('messages-list').insertAdjacentHTML('beforeend', html);
                    this.scrollToBottom(true);
                    this.markRead();
                    // Remove typing indicator for this user
                    this.typingUsers = this.typingUsers.filter(u => u.user_id !== e.sender_id);
                })
                .listen('.message.deleted', (e) => {
                    const el = document.getElementById(`msg-${e.message_id}`);
                    if (el) el.innerHTML = '<span class="text-xs text-gray-400 italic">🚫 Message deleted</span>';
                })
                .listen('.message.edited', (e) => {
                    const bodyEl = document.getElementById(`msg-body-${e.message_id}`);
                    if (bodyEl) bodyEl.textContent = e.body;
                })
                .listen('.typing.started', (e) => {
                    if (e.user_id === this.userId) return;
                    if (!this.typingUsers.find(u => u.user_id === e.user_id)) {
                        this.typingUsers.push(e);
                        this.typingText = `${e.name} is typing...`;
                    }
                    clearTimeout(this.typingTimeout);
                    this.typingTimeout = setTimeout(() => {
                        this.typingUsers = this.typingUsers.filter(u => u.user_id !== e.user_id);
                    }, 3000);
                })
                .listen('.call.initiated', (e) => {
                    window.dispatchEvent(new CustomEvent('incoming-call', { detail: e }));
                });
        },

        renderMessage(msg) {
            const isOut = msg.sender_id === this.userId;
            const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            let bodyHtml = '';
            if (msg.type === 'text') {
                bodyHtml = `<p class="text-[15px] leading-relaxed" id="msg-body-${msg.id}">${this.escapeHtml(msg.body)}</p>`;
            } else {
                bodyHtml = `<p class="text-sm italic text-gray-300">${msg.body_preview}</p>`;
            }
            return `
            <div id="msg-${msg.id}" class="flex ${isOut ? 'justify-end' : 'justify-start'} mb-1 animate-fade-in">
                <div class="${isOut ? 'msg-out' : 'msg-in'} max-w-[75%] px-4 py-2.5">
                    ${bodyHtml}
                    <p class="text-[11px] ${isOut ? 'text-white/60' : 'text-gray-400'} text-right mt-0.5">${time}</p>
                </div>
            </div>`;
        },

        escapeHtml(text) {
            const d = document.createElement('div');
            d.textContent = text || '';
            return d.innerHTML;
        },

        async apiPost(url, data) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(data),
            });
            return res.json();
        },

        async pinChat() {
            await fetch(`/chats/${this.chatId}/pin`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });
            this.showMenu = false;
        },

        async muteChat() {
            await fetch(`/chats/${this.chatId}/mute`, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type':'application/json' }, body: JSON.stringify({ duration: 8 }) });
            this.showMenu = false;
        },

        deleteChat() {
            if (confirm('Delete this chat for you?')) {
                fetch(`/chats/${this.chatId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } })
                    .then(() => window.location = '/chats');
            }
        },

        onScroll(e) {
            if (e.target.scrollTop < 50 && this.hasMoreMessages) this.loadMore();
        },

        async loadMore() {
            // Implement pagination
        },

        searchMessages() {
            this.showMenu = false;
        },
    };
}
</script>
@endpush
@endsection
