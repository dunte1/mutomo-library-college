<?php

namespace App\Modules\API\Controllers;

use App\Modules\Finance\Services\MpesaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MpesaValidationController extends Controller
{
    public function __invoke(Request $request, MpesaService $mpesaService): JsonResponse
    {
        $result = $mpesaService->handleValidation($request->all());

        return response()->json($result);
    }
}
