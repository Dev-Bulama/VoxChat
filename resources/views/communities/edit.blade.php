@extends('layouts.app')
@section('title', 'Edit Community')

@section('content')
<div class="max-w-lg mx-auto px-4 py-6 pb-24 lg:pb-6">

    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('communities.show', $community) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Edit Community</h1>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6">
        <form method="POST" action="{{ route('communities.update', $community) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Community Name *</label>
                    <input type="text" name="name" value="{{ old('name', $community->name) }}" required maxlength="100"
                           class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" rows="3" maxlength="500"
                              class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none">{{ old('description', $community->description) }}</textarea>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Public Community</p>
                        <p class="text-xs text-gray-400 mt-0.5">Anyone can find and join</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" {{ $community->is_public ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-500"></div>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-colors">
                    Save Changes
                </button>
                <form method="POST" action="{{ route('communities.destroy', $community) }}" onsubmit="return confirm('Delete this community permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-3 border-2 border-red-400 text-red-500 font-medium rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20">
                        Delete
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>
@endsection
