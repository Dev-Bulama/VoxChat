<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('sort_order')->get();
        $currentSubscription = Auth::user()->subscription()->with('plan')->first();

        return view('subscription.index', compact('plans', 'currentSubscription'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan_id'      => 'required|exists:subscription_plans,id',
            'billing_cycle'=> 'required|in:monthly,yearly',
            'gateway'      => 'required|in:stripe,paystack,flutterwave,paypal',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        $user = Auth::user();

        $amount = $request->billing_cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        if ($amount == 0) {
            // Free plan — activate directly
            $this->activateSubscription($user, $plan, $request->billing_cycle, 'free', 'free_' . Str::random(16));
            return redirect()->route('subscription.success')->with('success', 'Free plan activated!');
        }

        return match ($request->gateway) {
            'stripe'      => $this->stripeCheckout($user, $plan, $request->billing_cycle, $amount),
            'paystack'    => $this->paystackCheckout($user, $plan, $request->billing_cycle, $amount),
            'flutterwave' => $this->flutterwaveCheckout($user, $plan, $request->billing_cycle, $amount),
            default       => back()->with('error', 'Invalid payment gateway.'),
        };
    }

    public function success(Request $request)
    {
        return view('subscription.success');
    }

    public function cancel()
    {
        return view('subscription.cancel');
    }

    public function cancelSubscription(Request $request)
    {
        $subscription = Auth::user()->subscription()->first();

        if (!$subscription) {
            return back()->with('error', 'No active subscription found.');
        }

        $subscription->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
            'auto_renew'   => false,
        ]);

        return back()->with('success', 'Subscription cancelled. You will have access until ' . $subscription->current_period_end->format('d M Y'));
    }

    public function billing()
    {
        $transactions = PaymentTransaction::where('user_id', Auth::id())
            ->with('subscription.plan')
            ->latest()
            ->paginate(10);

        return view('subscription.billing', compact('transactions'));
    }

    public function stripeWebhook(Request $request)
    {
        // Handle Stripe webhook events
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            \Stripe\Webhook::constructEvent($payload, $sigHeader, config('services.stripe.webhook.secret'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->json()->all();

        if ($event['type'] === 'checkout.session.completed') {
            // Handle successful payment
        }

        return response()->json(['received' => true]);
    }

    public function paystackWebhook(Request $request)
    {
        $hash = hash_hmac('sha512', $request->getContent(), config('services.paystack.secret_key'));

        if ($hash !== $request->header('X-Paystack-Signature')) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($request->event === 'charge.success') {
            // Handle successful payment
        }

        return response()->json(['received' => true]);
    }

    private function stripeCheckout($user, SubscriptionPlan $plan, string $cycle, float $amount)
    {
        // Stripe Checkout Session creation
        return redirect()->route('subscription.index')->with('info', 'Stripe checkout coming soon. Configure STRIPE_KEY first.');
    }

    private function paystackCheckout($user, SubscriptionPlan $plan, string $cycle, float $amount)
    {
        return redirect()->route('subscription.index')->with('info', 'Paystack checkout coming soon. Configure PAYSTACK_SECRET_KEY first.');
    }

    private function flutterwaveCheckout($user, SubscriptionPlan $plan, string $cycle, float $amount)
    {
        return redirect()->route('subscription.index')->with('info', 'Flutterwave checkout coming soon. Configure FLW_SECRET_KEY first.');
    }

    private function activateSubscription($user, SubscriptionPlan $plan, string $cycle, string $gateway, string $transactionId): UserSubscription
    {
        $subscription = UserSubscription::updateOrCreate(
            ['user_id' => $user->id, 'status' => 'active'],
            [
                'plan_id'               => $plan->id,
                'billing_cycle'         => $cycle,
                'status'                => 'active',
                'payment_gateway'       => $gateway,
                'current_period_start'  => now(),
                'current_period_end'    => $cycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                'auto_renew'            => true,
            ]
        );

        $user->update(['subscription_plan' => $plan->slug, 'subscription_expires_at' => $subscription->current_period_end]);

        return $subscription;
    }
}
