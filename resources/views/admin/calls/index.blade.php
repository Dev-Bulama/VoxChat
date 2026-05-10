@extends('layouts.admin')
@section('title', 'Call Management')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Calls</h1>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $calls->total() }} total</span>
    </div>

    {{-- Active calls --}}
    @if($activeCalls->isNotEmpty())
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl p-4">
        <h2 class="text-sm font-semibold text-green-700 dark:text-green-300 mb-3">Active Calls ({{ $activeCalls->count() }})</h2>
        <div class="space-y-2">
            @foreach($activeCalls as $call)
            <div class="flex items-center justify-between bg-white dark:bg-gray-900 rounded-xl p-3">
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ ucfirst($call->type) }} call — Room: {{ $call->room_id }}</p>
                    <p class="text-xs text-gray-400">Started {{ $call->created_at->diffForHumans() }} · {{ $call->participants_count }} participants</p>
                </div>
                <span class="text-xs px-2.5 py-1 bg-green-100 text-green-700 rounded-full font-medium">Live</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Call history --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Provider</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">AI Face</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($calls as $call)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <td class="px-6 py-4">
                        <span class="flex items-center gap-2 text-sm text-gray-900 dark:text-white">
                            @if($call->type === 'video')
                            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm12.553 1.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
                            @else
                            <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.773-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/></svg>
                            @endif
                            {{ ucfirst($call->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $call->status === 'ended' ? 'bg-gray-100 text-gray-600' :
                               ($call->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700') }}">
                            {{ ucfirst($call->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $call->duration_formatted ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 uppercase">{{ $call->provider }}</td>
                    <td class="px-6 py-4 text-sm">
                        @if($call->ai_face_enabled)
                        <span class="text-purple-500 font-medium">Enabled</span>
                        @else
                        <span class="text-gray-400">Off</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $call->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-400">No calls found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $calls->links() }}
</div>
@endsection
