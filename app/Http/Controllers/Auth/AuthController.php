<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('chats.index');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$loginField => $request->login, 'password' => $request->password];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['login' => 'The provided credentials do not match our records.'])->onlyInput('login');
        }

        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            return back()->withErrors(['login' => 'Your account has been suspended. Reason: ' . $user->ban_reason]);
        }

        $user->update(['is_online' => true, 'last_seen_at' => now()]);
        $request->session()->regenerate();

        return redirect()->intended(route('chats.index'));
    }

    public function showRegister()
    {
        if (Auth::check()) return redirect()->route('chats.index');
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:30|unique:users|alpha_dash',
            'email'    => 'required|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'username' => strtolower($request->username),
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        $user->update(['is_online' => true, 'last_seen_at' => now()]);

        try {
            $user->notify(new WelcomeNotification());
        } catch (\Throwable) {}

        return redirect()->route('chats.index')->with('success', 'Welcome to VoxChat, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            $user->update(['is_online' => false, 'last_seen_at' => now()]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
