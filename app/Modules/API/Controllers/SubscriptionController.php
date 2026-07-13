<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Finance\Services\MpesaService;
use App\Modules\Subscriptions\Models\Plan;
use App\Modules\Subscriptions\Models\Subscription;
use App\Modules\Subscriptions\Services\SubscriptionService;
use Illuminate\Routing\Controller;

class SubscriptionController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
        protected SubscriptionService $subscriptionService,
        protected MpesaService $mpesaService,
    ) {}

    public function my(): \Illuminate\Http\JsonResponse
    {
        $sub = Subscription::with('plan')
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();

        if (!$sub) {
            return $this->response->success(null);
        }

        return $this->response->success($this->format($sub));
    }

    public function store(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'plan_id' => 'required|integer|exists:plans,id',
            'payment_method' => 'nullable|string|max:30',
        ]);

        $plan = Plan::active()->findOrFail($data['plan_id']);

        $sub = $this->subscriptionService->createSubscription(
            auth()->user(),
            $plan,
            ['payment_method' => $data['payment_method'] ?? null],
        );

        // Initiate M-Pesa STK push if payment method is mpesa
        if (($data['payment_method'] ?? null) === 'mpesa' && $plan->price > 0) {
            $user = auth()->user();
            $phone = $user->phone ?? request('phone');
            if ($phone) {
                try {
                    $mpesaResult = $this->mpesaService->stkPush(
                        amount: (float) $plan->price,
                        phone: $phone,
                        reference: "Subscription: {$plan->name}",
                        userId: $user->id,
                    );
                    return $this->response->created(
                        array_merge($this->format($sub), ['mpesa_stk' => $mpesaResult]),
                        'Subscription created. Please complete M-Pesa payment.',
                    );
                } catch (\Exception $e) {
                    return $this->response->created(
                        $this->format($sub),
                        'Subscription created but M-Pesa payment initiation failed. Please try again.',
                    );
                }
            }
        }

        return $this->response->created($this->format($sub), 'Subscription created successfully.');
    }

    public function cancel(int $id): \Illuminate\Http\JsonResponse
    {
        $sub = Subscription::where('user_id', auth()->id())
            ->whereIn('status', ['active', 'trial'])
            ->findOrFail($id);

        $this->subscriptionService->cancelSubscription($sub, request('reason'));

        return $this->response->success($this->format($sub->fresh()), 'Subscription cancelled.');
    }

    protected function format(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'plan_id' => $subscription->plan_id,
            'plan' => $subscription->plan ? [
                'id' => $subscription->plan->id,
                'name' => $subscription->plan->name,
            ] : null,
            'status' => $subscription->status,
            'start_at' => $subscription->start_date?->toIso8601String(),
            'end_at' => $subscription->end_date?->toIso8601String(),
            'auto_renew' => $subscription->auto_renew,
            'payment_method' => $subscription->payment_method,
            'billing_cycle' => $subscription->billing_cycle,
        ];
    }
}
