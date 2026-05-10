<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Support\Facades\Auth;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::active()
            ->forUser(Auth::user())
            ->with('user:id,name,username,avatar')
            ->latest()
            ->get();

        return response()->json($stories);
    }
}
