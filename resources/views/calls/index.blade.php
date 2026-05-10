@extends('layouts.app')
@section('title', 'Calls')

@section('content')
<div class="flex flex-col h-screen pb-16 lg:pb-0 overflow-hidden">
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 safe-top">
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Calls</h1>
    </div>

    {{-- Call Logs --}}
    <div class="flex-1 overflow-y-auto">
        @forelse($callLogs as $call)
        @php
            $participant = $call->participants->firstWhere('user_id', auth()->id());
            $other = $call->participants->firstWhere('user_id', '!=', auth()->id());
            $isOutgoing = $call->initiated_by === auth()->id();
            $isMissed = !$isOutgoing && $participant?->status === 'missed';
            $statusColor = $isMissed ? 'text-red-500' : ($isOutgoing ? 'text-blue-500' : 'text-green-500');
        @endphp
        <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 border-b border-gray-50 dark:border-gray-800/50 cursor-pointer"
             onclick="window.location='{{ route('chats.show', $call->chat) }}'">
            <div class="relative flex-shrink-0">
                <img src="{{ $other?->user?->avatar_url ?? asset('images/user-placeholder.png') }}"
                     class="w-13 h-13 w-[52px] h-[52px] rounded-full object-cover">
                <div class="absolute -bottom-0.5 -right-0.5 w-6 h-6 {{ $call->type === 'video' ? 'bg-purple-500' : 'bg-blue-500' }} rounded-full flex items-center justify-center border-2 border-white dark:border-gray-900">
                    @if($call->type === 'video')
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
                    @else
                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                    @endif
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-semibold text-[15px] text-gray-900 dark:text-white">{{ $other?->user?->name ?? 'Unknown' }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    @if($isOutgoing)
                    <svg class="w-3.5 h-3.5 {{ $statusColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
                    @else
                    <svg class="w-3.5 h-3.5 {{ $statusColor }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                    @endif
                    <span class="text-sm {{ $statusColor }}">{{ $isMissed ? 'Missed' : ($isOutgoing ? 'Outgoing' : 'Incoming') }} {{ ucfirst($call->type) }}</span>
                    @if($call->duration)
                    <span class="text-sm text-gray-400">• {{ $call->duration_formatted }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                <span class="text-xs text-gray-400">{{ $call->created_at->format($call->created_at->isToday() ? 'H:i' : 'd M') }}</span>
                <button onclick="event.stopPropagation(); callUser('{{ $call->chat_id }}', '{{ $call->type }}')"
                        class="p-1.5 rounded-lg {{ $call->type === 'video' ? 'text-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20' : 'text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20' }}">
                    @if($call->type === 'video')
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.82v6.36a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    @else
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    @endif
                </button>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center h-64 text-center px-6">
            <div class="w-20 h-20 bg-primary-50 dark:bg-primary-900/20 rounded-3xl flex items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <h3 class="font-semibold text-gray-700 dark:text-gray-300">No call history</h3>
            <p class="text-sm text-gray-400 mt-1">Start a call from any chat</p>
        </div>
        @endforelse

        {{ $callLogs->links() }}
    </div>
</div>
@endsection
