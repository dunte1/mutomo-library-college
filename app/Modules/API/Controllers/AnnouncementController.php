<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Resources\AnnouncementResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\Communication\Models\Announcement;
use Illuminate\Routing\Controller;

class AnnouncementController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
    ) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $announcements = Announcement::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('published_at')
            ->limit(20)
            ->get();

        return $this->response->success(AnnouncementResource::collection($announcements));
    }

    public function show(int $id): \Illuminate\Http\JsonResponse
    {
        $announcement = Announcement::where('status', 'published')
            ->findOrFail($id);

        return $this->response->success(new AnnouncementResource($announcement));
    }
}
