<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Update last seen every 5 minutes to reduce DB writes
            if (!$user->last_seen_at || $user->last_seen_at->diffInMinutes(now()) >= 5) {
                $user->updateQuietly(['is_online' => true, 'last_seen_at' => now()]);
            }
        }

        return $next($request);
    }
}
