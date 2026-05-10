<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Call;
use Illuminate\Http\Request;

class CallController extends Controller
{
    public function index(Request $request)
    {
        $query = Call::with(['initiator', 'participants.user', 'chat']);

        if ($request->type) $query->where('type', $request->type);
        if ($request->status) $query->where('status', $request->status);

        $calls = $query->latest()->paginate(20)->withQueryString();

        $activeCalls = Call::whereIn('status', ['ringing', 'ongoing'])
            ->withCount('participants')
            ->get();

        return view('admin.calls.index', compact('calls', 'activeCalls'));
    }
}
