<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return response()->json($request->user()->load('activeAvatar'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $users = User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'username', 'avatar', 'is_online', 'last_seen_at')
            ->limit(20)
            ->get()
            ->map(fn($u) => array_merge($u->toArray(), ['avatar_url' => $u->avatar_url]));

        return response()->json($users);
    }

    public function updateStatus(Request $request)
    {
        $request->validate(['is_online' => 'required|boolean']);
        $request->user()->update(['is_online' => $request->is_online, 'last_seen_at' => now()]);
        return response()->json(['ok' => true]);
    }
}
