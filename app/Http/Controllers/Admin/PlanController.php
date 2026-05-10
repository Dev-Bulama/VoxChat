<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:50',
            'slug'          => 'required|string|max:50|unique:subscription_plans',
            'description'   => 'nullable|string|max:200',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
            'is_active'     => 'boolean',
            'is_popular'    => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        SubscriptionPlan::create($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:50',
            'description'   => 'nullable|string|max:200',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
            'is_active'     => 'boolean',
            'is_popular'    => 'boolean',
            'sort_order'    => 'integer|min:0',
        ]);

        $plan->update($data);
        return redirect()->route('admin.plans.index')->with('success', 'Plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        $plan->delete();
        return redirect()->route('admin.plans.index')->with('success', 'Plan deleted.');
    }
}
