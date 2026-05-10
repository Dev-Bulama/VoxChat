<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Auth::user()->chats()
            ->with(['lastMessage', 'participants'])
            ->wherePivotNull('left_at')
            ->latest('updated_at')
            ->get();

        return response()->json($chats);
    }

    public function unreadCount()
    {
        $count = Auth::user()->chats()
            ->wherePivotNull('left_at')
            ->get()
            ->sum(fn($chat) => $chat->getUnreadCountFor(Auth::user()));

        return response()->json(['count' => $count]);
    }
}
