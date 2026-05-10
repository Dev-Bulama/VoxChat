<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
            );
        }

        if ($request->plan) {
            $query->where('subscription_plan', $request->plan);
        }

        if ($request->status === 'banned') {
            $query->where('is_banned', true);
        } elseif ($request->status === 'active') {
            $query->where('is_banned', false);
        } elseif ($request->status === 'online') {
            $query->where('is_online', true);
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load('subscription', 'aiAvatars');
        return view('admin.users.show', compact('user'));
    }

    public function ban(Request $request, User $user)
    {
        $request->validate(['reason' => 'required|string|max:255']);

        abort_if($user->is_admin, 403, 'Cannot ban another admin.');

        $user->update([
            'is_banned'  => true,
            'ban_reason' => $request->reason,
            'banned_at'  => now(),
        ]);

        \App\Models\AdminLog::create([
            'admin_id'     => auth()->id(),
            'action'       => 'user_banned',
            'subject_type' => User::class,
            'subject_id'   => $user->id,
            'new_values'   => ['reason' => $request->reason],
        ]);

        return back()->with('success', "User {$user->name} has been banned.");
    }

    public function unban(User $user)
    {
        $user->update(['is_banned' => false, 'ban_reason' => null, 'banned_at' => null]);

        \App\Models\AdminLog::create([
            'admin_id'     => auth()->id(),
            'action'       => 'user_unbanned',
            'subject_type' => User::class,
            'subject_id'   => $user->id,
        ]);

        return back()->with('success', "User {$user->name} has been unbanned.");
    }

    public function verify(User $user)
    {
        $user->update(['is_verified' => !$user->is_verified]);
        $action = $user->is_verified ? 'verified' : 'unverified';
        return back()->with('success', "User {$user->name} has been {$action}.");
    }

    public function destroy(User $user)
    {
        abort_if($user->is_admin, 403);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
