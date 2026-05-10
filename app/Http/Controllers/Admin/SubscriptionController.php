<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserSubscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = UserSubscription::with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        $stats = [
            'active'    => UserSubscription::where('status', 'active')->count(),
            'cancelled' => UserSubscription::where('status', 'cancelled')->count(),
            'expired'   => UserSubscription::where('status', 'expired')->count(),
            'trial'     => UserSubscription::where('status', 'trial')->count(),
        ];

        return view('admin.subscriptions.index', compact('subscriptions', 'stats'));
    }
}
