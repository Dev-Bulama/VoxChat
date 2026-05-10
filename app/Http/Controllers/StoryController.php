<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryView;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function index()
    {
        $user = Auth::user();

        $myStories = Story::active()->where('user_id', $user->id)->with('views.viewer')->get();

        $contactIds = $user->contacts()->pluck('contact_id');

        $contactStories = Story::active()
            ->whereIn('user_id', $contactIds)
            ->with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($stories) use ($user) {
                $hasUnviewed = $stories->filter(fn($s) => !$s->hasViewedBy($user->id))->isNotEmpty();
                return ['stories' => $stories, 'has_unviewed' => $hasUnviewed];
            });

        return view('status.index', compact('myStories', 'contactStories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'             => 'required|in:image,video,text,music',
            'content'          => 'nullable|string|max:700',
            'media'            => 'nullable|file|max:51200',
            'background_color' => 'nullable|string|max:20',
            'text_color'       => 'nullable|string|max:20',
            'privacy'          => 'in:everyone,contacts,close_friends',
        ]);

        $user = Auth::user();

        $todayCount = Story::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        abort_if($todayCount >= config('voxchat.stories.max_per_day', 30), 429, 'Daily story limit reached.');

        $mediaData = [];
        if ($request->hasFile('media')) {
            $mediaData = $this->mediaService->processStoryMedia($request->file('media'), $request->type);
        }

        $story = Story::create([
            'user_id'          => $user->id,
            'type'             => $request->type,
            'content'          => $request->content,
            'background_color' => $request->background_color,
            'text_color'       => $request->text_color,
            'font_style'       => $request->font_style,
            'privacy'          => $request->privacy ?? 'everyone',
            'expires_at'       => now()->addHours(config('voxchat.stories.expiry_hours', 24)),
            ...$mediaData,
        ]);

        return response()->json([
            'story' => $story,
            'html'  => view('status.partials.my-story', compact('story'))->render(),
        ]);
    }

    public function view(Story $story)
    {
        $user = Auth::user();

        if ($story->user_id !== $user->id) {
            StoryView::firstOrCreate(
                ['story_id' => $story->id, 'viewer_id' => $user->id],
                ['viewed_at' => now()]
            );
            $story->increment('views_count');
        }

        return response()->json([
            'story'    => $story->load('user'),
            'has_next' => Story::active()
                ->where('user_id', $story->user_id)
                ->where('id', '>', $story->id)
                ->exists(),
        ]);
    }

    public function react(Request $request, Story $story)
    {
        $request->validate(['reaction' => 'required|string|max:20']);

        StoryView::updateOrCreate(
            ['story_id' => $story->id, 'viewer_id' => Auth::id()],
            ['reaction' => $request->reaction, 'viewed_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function reply(Request $request, Story $story)
    {
        $request->validate(['reply_text' => 'required|string|max:500']);

        StoryView::updateOrCreate(
            ['story_id' => $story->id, 'viewer_id' => Auth::id()],
            ['reply_text' => $request->reply_text, 'viewed_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Story $story)
    {
        abort_unless($story->user_id === Auth::id(), 403);
        $story->delete();
        return response()->json(['success' => true]);
    }

    public function viewers(Story $story)
    {
        abort_unless($story->user_id === Auth::id(), 403);
        $viewers = $story->views()->with('viewer')->orderByDesc('viewed_at')->get();
        return response()->json(['viewers' => $viewers]);
    }
}
