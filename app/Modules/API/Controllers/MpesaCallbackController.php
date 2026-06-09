<?php

namespace App\Modules\API\Controllers;

use App\Modules\Finance\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MpesaCallbackController extends Controller
{
    public function __invoke(Request $request, MpesaService $mpesaService): JsonResponse
    {
        $mpesaService->processCallback($request->all());

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);
    }
}
