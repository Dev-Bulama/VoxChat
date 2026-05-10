<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Message;
use App\Models\PaymentTransaction;
use App\Models\Story;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'           => User::count(),
            'online_users'          => User::where('is_online', true)->count(),
            'messages_today'        => Message::whereDate('created_at', today())->count(),
            'active_subscriptions'  => UserSubscription::where('status', 'active')->count(),
            'monthly_revenue'       => PaymentTransaction::where('status', 'completed')
                ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount'),
            'active_calls'          => Call::whereIn('status', ['ringing', 'ongoing'])->count(),
            'stories_today'         => Story::whereDate('created_at', today())->count(),
            'pending_reports'       => \App\Models\Report::where('status', 'pending')->count() ?? 0,
            'users_growth'          => $this->growthPercent(User::class, 'created_at'),
            'revenue_growth'        => $this->revenueGrowthPercent(),
        ];

        $recentUsers = User::latest()->limit(10)->get();

        // Build 30-day chart data
        $charts = [
            'users'   => $this->buildDailyData(User::class, 'created_at'),
            'revenue' => $this->buildRevenueDailyData(),
        ];

        return view('admin.dashboard', compact('stats', 'recentUsers', 'charts'));
    }

    private function growthPercent(string $model, string $column): float
    {
        $thisMonth = $model::whereBetween($column, [now()->startOfMonth(), now()])->count();
        $lastMonth = $model::whereBetween($column, [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->count();
        if ($lastMonth === 0) return 100;
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function revenueGrowthPercent(): float
    {
        $thisMonth = PaymentTransaction::where('status', 'completed')->whereBetween('paid_at', [now()->startOfMonth(), now()])->sum('amount');
        $lastMonth = PaymentTransaction::where('status', 'completed')->whereBetween('paid_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('amount');
        if ($lastMonth == 0) return 100;
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function buildDailyData(string $model, string $column): array
    {
        $data = $model::selectRaw("DATE({$column}) as date, COUNT(*) as count")
            ->where($column, '>=', now()->subDays(29))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[$date] = $data[$date] ?? 0;
        }
        return $result;
    }

    private function buildRevenueDailyData(): array
    {
        $data = PaymentTransaction::where('status', 'completed')
            ->where('paid_at', '>=', now()->subDays(29))
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $result[$date] = $data[$date] ?? 0;
        }
        return $result;
    }
}
