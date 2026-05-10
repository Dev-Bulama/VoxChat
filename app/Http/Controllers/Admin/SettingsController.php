<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            SystemSetting::set($key, $value);
        }

        \App\Models\AdminLog::create([
            'admin_id' => auth()->id(),
            'action'   => 'settings_updated',
            'new_values' => array_keys($request->settings),
        ]);

        return back()->with('success', 'Settings saved successfully.');
    }
}
