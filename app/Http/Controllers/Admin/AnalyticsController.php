<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Message;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        return view('admin.analytics');
    }

    public function data(Request $request)
    {
        $period = $request->get('period', '30');
        $from   = now()->subDays((int)$period);

        return response()->json([
            'users' => [
                'total'        => User::count(),
                'new'          => User::where('created_at', '>=', $from)->count(),
                'online'       => User::where('is_online', true)->count(),
                'premium'      => User::whereIn('subscription_plan', ['premium', 'business'])->count(),
                'daily'        => $this->dailyCounts(User::class, 'created_at', $from),
            ],
            'messages' => [
                'total'        => Message::count(),
                'today'        => Message::whereDate('created_at', today())->count(),
                'by_type'      => Message::where('created_at', '>=', $from)->groupBy('type')
                    ->selectRaw('type, COUNT(*) as count')->pluck('count', 'type'),
            ],
            'calls' => [
                'total'        => Call::count(),
                'voice'        => Call::where('type', 'voice')->where('created_at', '>=', $from)->count(),
                'video'        => Call::where('type', 'video')->where('created_at', '>=', $from)->count(),
                'avg_duration' => Call::where('status', 'ended')->avg('duration'),
            ],
            'revenue' => [
                'total'        => PaymentTransaction::where('status', 'completed')->sum('amount'),
                'monthly'      => PaymentTransaction::where('status', 'completed')->whereBetween('paid_at', [now()->startOfMonth(), now()])->sum('amount'),
                'by_gateway'   => PaymentTransaction::where('status', 'completed')->groupBy('gateway')
                    ->selectRaw('gateway, SUM(amount) as total')->pluck('total', 'gateway'),
                'daily'        => $this->dailyRevenue($from),
            ],
            'subscriptions' => [
                'by_plan' => UserSubscription::where('status', 'active')->groupBy('plan_id')
                    ->selectRaw('plan_id, COUNT(*) as count')->pluck('count', 'plan_id'),
            ],
        ]);
    }

    private function dailyCounts(string $model, string $col, $from): array
    {
        return $model::selectRaw("DATE({$col}) as date, COUNT(*) as count")
            ->where($col, '>=', $from)
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    private function dailyRevenue($from): array
    {
        return PaymentTransaction::where('status', 'completed')
            ->where('paid_at', '>=', $from)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
    }
}
