<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['reporter', 'reviewedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.reports.index', compact('reports'));
    }

    public function resolve(Request $request, Report $report)
    {
        $request->validate([
            'status'          => 'required|in:resolved,dismissed',
            'resolution_note' => 'nullable|string|max:500',
        ]);

        $report->update([
            'status'          => $request->status,
            'reviewed_by'     => auth()->id(),
            'resolution_note' => $request->resolution_note,
            'reviewed_at'     => now(),
        ]);

        return back()->with('success', 'Report resolved.');
    }
}
