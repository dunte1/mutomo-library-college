<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\EventResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Communication\Models\Event;
use Illuminate\Routing\Controller;

class EventController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = request()->validate([
            'type' => 'nullable|string|in:workshop,seminar,exam,holiday,meeting,other',
            'upcoming' => 'nullable|boolean',
        ]);

        $query = Event::where('status', 'published');

        if (! empty($data['type'])) {
            $query->where('type', $data['type']);
        }

        if ($data['upcoming'] ?? true) {
            $query->where('start_date', '>=', now());
        }

        $events = $query->orderBy('start_date')
            ->limit(50)
            ->get();

        return $this->response->success(EventResource::collection($events));
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $event = Event::findOrFail($id);

        return $this->response->success(new EventResource($event));
    }
}
