<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Modules\Finance\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): JsonResponse
    {
        $data = request()->validate([
            'per_page' => 'sometimes|integer|min:1|max:50',
        ]);

        $payments = Transaction::with(['receipt', 'fine', 'invoice'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(min((int) ($data['per_page'] ?? 20), 50))
            ->through(fn ($t) => $this->format($t));

        return $this->response->paginated($payments);
    }

    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::with(['receipt', 'fine', 'invoice'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return $this->response->success($this->format($transaction));
    }

    protected function format(Transaction $t): array
    {
        return [
            'id' => $t->id,
            'transaction_number' => $t->transaction_number,
            'type' => $t->type,
            'payment_method' => $t->payment_method,
            'amount' => (float) $t->amount,
            'currency' => $t->currency ?? 'KES',
            'reference' => $t->reference,
            'description' => $t->description,
            'status' => $t->status,
            'created_at' => $t->created_at?->toIso8601String(),
            'paid_at' => $t->paid_at?->toIso8601String(),
            'receipt_url' => $t->receipt ? url('/finance/receipt/'.$t->receipt->id) : null,
        ];
    }
}
