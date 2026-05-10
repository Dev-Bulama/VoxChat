<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $myCommunities = $user->communities()->with('creator')->get();

        $discoverCommunities = Community::where('is_public', true)
            ->whereDoesntHave('members', fn($q) => $q->where('user_id', $user->id))
            ->withCount('members')
            ->latest()
            ->paginate(12);

        return view('communities.index', compact('myCommunities', 'discoverCommunities'));
    }

    public function create()
    {
        return view('communities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public'   => 'boolean',
            'avatar'      => 'nullable|image|max:5120',
        ]);

        $user = Auth::user();

        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('communities', 'public')
            : null;

        $community = Community::create([
            'created_by'  => $user->id,
            'name'        => $request->name,
            'description' => $request->description,
            'is_public'   => $request->boolean('is_public', true),
            'avatar'      => $avatarPath,
        ]);

        $community->members()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        return redirect()->route('communities.show', $community)->with('success', 'Community created!');
    }

    public function show(Community $community)
    {
        $community->load('creator');
        $members = $community->members()->paginate(20);
        return view('communities.show', compact('community', 'members'));
    }

    public function edit(Community $community)
    {
        $this->authorize('update', $community);
        return view('communities.edit', compact('community'));
    }

    public function update(Request $request, Community $community)
    {
        $this->authorize('update', $community);
        $request->validate(['name' => 'required|string|max:100', 'description' => 'nullable|string|max:500']);
        $community->update($request->only(['name', 'description', 'is_public']));
        return back()->with('success', 'Community updated.');
    }

    public function destroy(Community $community)
    {
        $this->authorize('delete', $community);
        $community->delete();
        return redirect()->route('communities.index')->with('success', 'Community deleted.');
    }

    public function join(Community $community)
    {
        $user = Auth::user();
        if (!$community->members()->where('user_id', $user->id)->exists()) {
            $community->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
            $community->increment('members_count');
        }
        return back()->with('success', 'Joined ' . $community->name);
    }

    public function leave(Community $community)
    {
        $community->members()->detach(Auth::id());
        $community->decrement('members_count');
        return redirect()->route('communities.index')->with('success', 'Left ' . $community->name);
    }
}
