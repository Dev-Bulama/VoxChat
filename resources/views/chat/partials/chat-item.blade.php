@php
    $lastMsg = $chat->lastMessage;
    $isGroup = $chat->type === 'group';
    $other   = !$isGroup ? $chat->getOtherParticipant(auth()->id()) : null;
@endphp
<a href="{{ route('chats.show', $chat) }}"
   class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 active:bg-gray-100 dark:active:bg-gray-700 transition-colors cursor-pointer relative group"
   id="chat-item-{{ $chat->id }}">

    {{-- Avatar --}}
    <div class="relative flex-shrink-0">
        <img src="{{ $chat->display_avatar }}" class="w-13 h-13 w-[52px] h-[52px] rounded-full object-cover">
        @if(!$isGroup && $other?->is_online)
        <div class="online-dot"></div>
        @endif
        @if($isGroup)
        <div class="absolute -bottom-0.5 -right-0.5 w-5 h-5 bg-purple-500 rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900">
            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
        </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between mb-0.5">
            <div class="flex items-center gap-1.5 min-w-0">
                <span class="font-semibold text-[15px] text-gray-900 dark:text-white truncate">{{ $chat->display_name }}</span>
                @if(!$isGroup && $other?->is_verified)
                <svg class="w-3.5 h-3.5 text-primary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                @endif
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                @if($chat->is_muted)
                <svg class="w-3 h-3 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                @endif
                @if($chat->is_pinned)
                <svg class="w-3 h-3 text-gray-300 dark:text-gray-600" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v2a2 2 0 01-2 2h-1v3.832a1 1 0 01-.553.894l-3 1.5A1 1 0 017 13V8H6a2 2 0 01-2-2V4z"/></svg>
                @endif
                <span class="text-xs text-gray-400 dark:text-gray-500">
                    {{ $lastMsg ? $lastMsg->created_at->format($lastMsg->created_at->isToday() ? 'H:i' : 'd/m') : '' }}
                </span>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-1 min-w-0 flex-1">
                {{-- Typing indicator (real-time) --}}
                <span class="typing-indicator text-xs text-primary-500 font-medium hidden" id="typing-{{ $chat->id }}">typing...</span>

                {{-- Last message preview --}}
                <span class="text-sm text-gray-500 dark:text-gray-400 truncate" id="last-msg-{{ $chat->id }}">
                    @if($lastMsg)
                        @if($lastMsg->sender_id === auth()->id())
                        <span class="text-primary-400">You: </span>
                        @elseif($isGroup)
                        <span class="text-gray-400">{{ $lastMsg->sender?->name }}: </span>
                        @endif
                        {{ $lastMsg->body_preview }}
                    @else
                        <span class="italic">No messages yet</span>
                    @endif
                </span>
            </div>
            @if($chat->unread_count > 0)
            <span class="ml-2 min-w-[20px] h-5 px-1.5 bg-primary-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center flex-shrink-0" id="unread-{{ $chat->id }}">
                {{ $chat->unread_count > 99 ? '99+' : $chat->unread_count }}
            </span>
            @endif
        </div>
    </div>
</a>
