<?php

namespace App\Modules\API\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApiResponseService
{
    /**
     * Success response with data.
     */
    public function success(mixed $data = null, ?string $message = null, int $status = 200, array $extra = []): JsonResponse
    {
        $response = [];

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
        }

        if ($data instanceof Collection) {
            $response['data'] = $data->values()->toArray();
        }

        $response = array_merge($response, $extra);

        return response()->json($response, $status);
    }

    /**
     * Resource created response.
     */
    public function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return $this->success($data, $message ?? 'Resource created successfully.', 201);
    }

    /**
     * No content response.
     */
    public function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Error response.
     */
    public function error(string $message, int $status = 400, ?array $errors = null): JsonResponse
    {
        $response = ['message' => $message];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Validation error response.
     */
    public function validationError(string $message, array $errors): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }

    /**
     * Not found response.
     */
    public function notFound(?string $message = null): JsonResponse
    {
        return $this->error($message ?? 'Resource not found.', 404);
    }

    /**
     * Unauthorized response.
     */
    public function unauthorized(?string $message = null): JsonResponse
    {
        return $this->error($message ?? 'Unauthenticated.', 401);
    }

    /**
     * Forbidden response.
     */
    public function forbidden(?string $message = null): JsonResponse
    {
        return $this->error($message ?? 'This action is unauthorized.', 403);
    }

    /**
     * Paginated response with meta.
     */
    public function paginated(LengthAwarePaginator $paginator, ?array $extraMeta = null): JsonResponse
    {
        $response = [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];

        if ($extraMeta) {
            $response['meta'] = array_merge($response['meta'], $extraMeta);
        }

        return response()->json($response);
    }
}
