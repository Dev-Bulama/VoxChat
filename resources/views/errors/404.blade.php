@extends('layouts.app')
@section('title', 'Page Not Found')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[60vh] px-4 text-center">
    <p class="text-8xl font-black text-gray-100 dark:text-gray-800">404</p>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white -mt-6 mb-2">Page not found</h1>
    <p class="text-gray-400 text-sm mb-8">The page you're looking for doesn't exist or has been moved.</p>
    <a href="{{ url('/') }}" class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-semibold rounded-xl transition-colors">
        Go Home
    </a>
</div>
@endsection
