<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markRead(Request $request)
    {
        if ($request->has('id')) {
            Auth::user()->notifications()->where('id', $request->id)->update(['read_at' => now()]);
        } else {
            Auth::user()->unreadNotifications->markAsRead();
        }

        return response()->json(['ok' => true]);
    }

    public function unreadCount()
    {
        return response()->json(['count' => Auth::user()->unreadNotifications()->count()]);
    }
}
