<?php

namespace App\Modules\API\Controllers;

use App\Modules\Subscriptions\Services\StripeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeService $stripeService): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (! $webhookSecret) {
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        $result = $stripeService->handleWebhook($payload, $sigHeader, $webhookSecret);

        if (! $result['success']) {
            return response()->json(['error' => $result['error']], 400);
        }

        return response()->json(['status' => 'success']);
    }
}
