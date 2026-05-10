@php
    $isOwn    = $message->sender_id === $user->id;
    $sender   = $message->sender;
    $isGroup  = $message->chat->type === 'group';
@endphp

<div id="msg-{{ $message->id }}"
     class="flex {{ $isOwn ? 'justify-end' : 'justify-start' }} mb-1 group animate-fade-in"
     x-data="{ showActions: false, showReactions: false }">

    {{-- Avatar (group/other) --}}
    @if(!$isOwn && $isGroup)
    <img src="{{ $sender?->avatar_url }}" class="w-7 h-7 rounded-full object-cover self-end mr-1.5 flex-shrink-0">
    @endif

    <div class="max-w-[75%] flex flex-col {{ $isOwn ? 'items-end' : 'items-start' }}">
        {{-- Sender name (group) --}}
        @if(!$isOwn && $isGroup && $sender)
        <span class="text-xs font-medium text-primary-500 mb-0.5 ml-3">{{ $sender->name }}</span>
        @endif

        {{-- Reply preview --}}
        @if($message->replyTo)
        <div class="{{ $isOwn ? 'bg-primary-600/30' : 'bg-gray-200 dark:bg-gray-700' }} rounded-xl px-3 py-1.5 mb-0.5 border-l-2 border-primary-400 max-w-full">
            <p class="text-[11px] font-medium text-primary-400">{{ $message->replyTo->sender?->name ?? 'Unknown' }}</p>
            <p class="text-[12px] {{ $isOwn ? 'text-white/70' : 'text-gray-500 dark:text-gray-400' }} truncate">{{ $message->replyTo->body_preview }}</p>
        </div>
        @endif

        {{-- Message bubble --}}
        <div class="relative">
            <div class="{{ $isOwn ? 'msg-out' : 'msg-in' }} px-4 py-2.5 cursor-pointer"
                 @click.right.prevent="showActions = !showActions"
                 @press.long="showActions = !showActions">

                @if($message->is_deleted)
                    <p class="text-sm italic {{ $isOwn ? 'text-white/60' : 'text-gray-400' }}">🚫 This message was deleted</p>
                @else
                    {{-- Forwarded label --}}
                    @if($message->is_forwarded)
                    <p class="text-[11px] {{ $isOwn ? 'text-white/60' : 'text-gray-400' }} mb-1 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"/></svg>
                        Forwarded
                    </p>
                    @endif

                    {{-- Content by type --}}
                    @if($message->type === 'text')
                        <p class="text-[15px] leading-relaxed break-words" id="msg-body-{{ $message->id }}">{!! nl2br(e($message->body)) !!}</p>

                    @elseif($message->type === 'image')
                        <div class="-mx-4 -my-2.5 overflow-hidden rounded-2xl">
                            <img src="{{ $message->media_url_full }}" class="max-w-full max-h-64 object-cover" loading="lazy"
                                 @click="window.open('{{ $message->media_url_full }}', '_blank')">
                        </div>
                        @if($message->body)
                        <p class="text-sm mt-2 break-words">{{ $message->body }}</p>
                        @endif

                    @elseif($message->type === 'video')
                        <div class="-mx-4 -my-2.5 overflow-hidden rounded-2xl">
                            <video src="{{ $message->media_url_full }}" class="max-w-full max-h-64" controls preload="metadata">
                                Your browser doesn't support video.
                            </video>
                        </div>

                    @elseif($message->type === 'voice_note')
                        <div class="flex items-center gap-3 min-w-[200px]">
                            <button class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center flex-shrink-0"
                                    onclick="this.closest('.voice-player').querySelector('audio').paused ? this.closest('.voice-player').querySelector('audio').play() : this.closest('.voice-player').querySelector('audio').pause()">
                                <svg class="w-5 h-5 {{ $isOwn ? 'text-white' : 'text-primary-500' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg>
                            </button>
                            <div class="flex-1 voice-player">
                                <audio class="hidden" src="{{ $message->media_url_full }}"></audio>
                                <div class="flex items-center gap-0.5 h-6">
                                    @for($i = 0; $i < 20; $i++)
                                    <div class="{{ $isOwn ? 'bg-white/60' : 'bg-primary-300' }} rounded-full w-1" style="height: {{ rand(4, 20) }}px"></div>
                                    @endfor
                                </div>
                                <p class="text-[11px] {{ $isOwn ? 'text-white/60' : 'text-gray-400' }} mt-0.5">{{ gmdate('i:s', $message->media_duration ?? 0) }}</p>
                            </div>
                        </div>

                    @elseif($message->type === 'file')
                        <a href="{{ $message->media_url_full }}" target="_blank"
                           class="flex items-center gap-3 {{ $isOwn ? 'text-white' : 'text-gray-700 dark:text-gray-200' }}">
                            <div class="w-10 h-10 {{ $isOwn ? 'bg-white/20' : 'bg-primary-100 dark:bg-primary-900/30' }} rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 {{ $isOwn ? 'text-white' : 'text-primary-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $message->metadata['filename'] ?? 'File' }}</p>
                                <p class="text-[11px] opacity-60">{{ $message->media_size ? number_format($message->media_size / 1024, 0) . ' KB' : '' }}</p>
                            </div>
                        </a>

                    @elseif($message->type === 'call_log')
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span class="text-sm">{{ ucfirst($message->metadata['call_type'] ?? 'Call') }} • {{ $message->metadata['duration'] ?? '' }}</span>
                        </div>
                    @endif
                @endif

                {{-- Time & Status --}}
                <div class="flex items-center justify-end gap-1 mt-0.5">
                    @if($message->is_edited && !$message->is_deleted)
                    <span class="text-[10px] {{ $isOwn ? 'text-white/50' : 'text-gray-400' }}">edited</span>
                    @endif
                    <span class="text-[11px] {{ $isOwn ? 'text-white/60' : 'text-gray-400' }}">{{ $message->created_at->format('H:i') }}</span>
                    @if($isOwn)
                    {{-- Read receipts --}}
                    <svg class="w-3.5 h-3.5 {{ $message->receipts->where('status', 'read')->count() > 0 ? 'text-blue-400' : 'text-white/50' }}" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    @endif
                </div>
            </div>

            {{-- Reactions display --}}
            @if($message->reactions->count() > 0)
            <div class="flex gap-0.5 mt-0.5 {{ $isOwn ? 'justify-end' : 'justify-start' }}">
                @foreach($message->reactions->groupBy('emoji') as $emoji => $reactions)
                <span class="text-xs bg-gray-100 dark:bg-gray-800 rounded-full px-1.5 py-0.5 cursor-pointer hover:bg-gray-200"
                      onclick="reactToMessage({{ $message->id }}, '{{ $emoji }}')">
                    {{ $emoji }} {{ $reactions->count() > 1 ? $reactions->count() : '' }}
                </span>
                @endforeach
            </div>
            @endif

            {{-- Context menu --}}
            <div x-show="showActions" @click.away="showActions = false" x-cloak
                 class="absolute {{ $isOwn ? 'right-0' : 'left-0' }} bottom-full mb-2 bg-white dark:bg-gray-800 rounded-2xl shadow-card border border-gray-100 dark:border-gray-700 py-1 z-50 w-44">
                <button @click="showReactions = true; showActions = false"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                    😀 React
                </button>
                <button onclick="setReply({{ json_encode(['id' => $message->id, 'body_preview' => $message->body_preview, 'sender' => ['name' => $sender?->name]]) }})"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Reply
                </button>
                @if(!$message->is_deleted && $message->sender_id === $user->id && $message->type === 'text')
                <button onclick="editMessage({{ $message->id }}, {{ json_encode($message->body) }})"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </button>
                @endif
                <button onclick="forwardMessage({{ $message->id }})"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Forward
                </button>
                <hr class="my-1 border-gray-100 dark:border-gray-700">
                @if(!$message->is_deleted && $message->sender_id === $user->id)
                <button onclick="deleteMessage({{ $message->id }})"
                        class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
                @endif
            </div>

            {{-- Reaction picker --}}
            <div x-show="showReactions" @click.away="showReactions = false" x-cloak
                 class="absolute {{ $isOwn ? 'right-0' : 'left-0' }} bottom-full mb-2 bg-white dark:bg-gray-800 rounded-2xl shadow-card border border-gray-100 dark:border-gray-700 p-2 z-50">
                <div class="flex gap-1">
                    @foreach(['❤️','😂','😮','😢','😡','👍','👎'] as $emoji)
                    <button class="w-9 h-9 text-xl hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full flex items-center justify-center transition-transform hover:scale-110"
                            onclick="reactToMessage({{ $message->id }}, '{{ $emoji }}')">{{ $emoji }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Own avatar (optional) --}}
    @if($isOwn && $isGroup)
    <img src="{{ $user->avatar_url }}" class="w-7 h-7 rounded-full object-cover self-end ml-1.5 flex-shrink-0">
    @endif
</div>
