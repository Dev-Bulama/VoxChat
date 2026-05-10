<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function show(string $username)
    {
        $profile = User::where('username', $username)->active()->firstOrFail();
        $viewer  = Auth::user();

        $isContact = $viewer?->contacts()->where('contact_id', $profile->id)->exists();
        $isBlocked = $viewer?->contacts()->where('contact_id', $profile->id)->wherePivot('is_blocked', true)->exists();

        $stories = $profile->activeStories;

        return view('profile.show', compact('profile', 'isContact', 'isBlocked', 'stories'));
    }

    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'        => 'required|string|max:255',
            'username'    => 'required|string|max:30|alpha_dash|unique:users,username,' . $user->id,
            'bio'         => 'nullable|string|max:500',
            'status_message' => 'nullable|string|max:200',
            'gender'      => 'nullable|in:male,female,other,prefer_not_to_say',
            'birthday'    => 'nullable|date|before:today',
            'country'     => 'nullable|string|max:100',
            'website'     => 'nullable|url|max:255',
        ]);

        $user->update($request->only([
            'name', 'username', 'bio', 'status_message',
            'gender', 'birthday', 'country', 'website',
        ]));

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);

        $user = Auth::user();
        $path = $this->mediaService->uploadAvatar($request->file('avatar'), $user->id);
        $user->update(['avatar' => $path]);

        return response()->json(['avatar_url' => $user->avatar_url]);
    }

    public function updateCover(Request $request)
    {
        $request->validate(['cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:10240']);

        $user = Auth::user();
        $path = $this->mediaService->uploadCoverPhoto($request->file('cover'), $user->id);
        $user->update(['cover_photo' => $path]);

        return response()->json(['cover_url' => $user->cover_photo_url]);
    }

    public function updatePrivacy(Request $request)
    {
        $request->validate([
            'last_seen_privacy'     => 'in:everyone,contacts,nobody',
            'profile_photo_privacy' => 'in:everyone,contacts,nobody',
            'about_privacy'         => 'in:everyone,contacts,nobody',
            'status_privacy'        => 'in:everyone,contacts,nobody',
            'read_receipts_enabled' => 'boolean',
        ]);

        Auth::user()->update($request->only([
            'last_seen_privacy', 'profile_photo_privacy',
            'about_privacy', 'status_privacy', 'read_receipts_enabled',
        ]));

        return back()->with('success', 'Privacy settings updated.');
    }

    public function updateNotifications(Request $request)
    {
        $request->validate([
            'message_notifications' => 'boolean',
            'call_notifications'    => 'boolean',
            'story_notifications'   => 'boolean',
            'group_notifications'   => 'boolean',
            'email_notifications'   => 'boolean',
        ]);

        Auth::user()->update($request->only([
            'message_notifications', 'call_notifications',
            'story_notifications', 'group_notifications', 'email_notifications',
        ]));

        return back()->with('success', 'Notification preferences updated.');
    }

    public function updateTheme(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark,system']);
        Auth::user()->update(['theme' => $request->theme]);
        return response()->json(['theme' => $request->theme]);
    }

    public function settings()
    {
        return view('settings.index', ['user' => Auth::user()]);
    }

    public function addContact(User $user)
    {
        $current = Auth::user();
        abort_if($user->id === $current->id, 400);

        $current->contacts()->syncWithoutDetaching([$user->id => []]);
        return response()->json(['success' => true]);
    }

    public function removeContact(User $user)
    {
        Auth::user()->contacts()->detach($user->id);
        return response()->json(['success' => true]);
    }

    public function blockUser(User $user)
    {
        $current = Auth::user();
        abort_if($user->id === $current->id, 400);

        $current->contacts()->syncWithoutDetaching([$user->id => ['is_blocked' => true]]);
        return response()->json(['success' => true]);
    }

    public function unblockUser(User $user)
    {
        Auth::user()->contacts()->updateExistingPivot($user->id, ['is_blocked' => false]);
        return response()->json(['success' => true]);
    }
}
