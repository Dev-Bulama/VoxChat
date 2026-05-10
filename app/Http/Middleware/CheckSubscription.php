<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $plan = 'premium'): Response
    {
        $user = $request->user();

        $plans = ['free' => 0, 'premium' => 1, 'business' => 2];
        $required = $plans[$plan] ?? 1;
        $current  = $plans[$user?->subscription_plan ?? 'free'] ?? 0;

        if ($current < $required) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Upgrade your subscription to access this feature.', 'upgrade_required' => true], 402);
            }
            return redirect()->route('subscription.index')->with('warning', 'Please upgrade your subscription to access this feature.');
        }

        return $next($request);
    }
}
