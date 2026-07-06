<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Routing\Controller;

class SubscriptionPlanController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $plans = Plan::active()->orderBy('sort_order')->get()->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'description' => $p->description,
            'price' => (float) $p->price,
            'currency' => $p->currency,
            'duration_days' => $p->isMonthly() ? 30 : 365,
            'features' => $p->features ?? [],
            'is_popular' => $p->is_active && $p->price > 0,
        ]);

        return $this->response->success($plans);
    }
}
