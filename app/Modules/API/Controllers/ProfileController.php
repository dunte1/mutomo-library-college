<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Requests\UpdateProfileRequest;
use App\Modules\API\Resources\UserResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\API\Services\AuthenticationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService,
        protected ApiResponseService $response,
    ) {}

    /**
     * Get the authenticated user's full profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->authService->getProfile($request->user());

        return $this->response->success(new UserResource($user));
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $fillable = collect($data)->only([
            'name', 'phone', 'department_id', 'program_id',
            'notification_preferences',
        ])->filter(fn ($value) => $value !== null)->toArray();

        if (! empty($fillable)) {
            $user->update($fillable);
        }

        $user->load(['roles', 'department', 'program']);

        return $this->response->success(
            new UserResource($user),
            'Profile updated successfully.'
        );
    }

    /**
     * Upload profile avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Delete old avatar
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return $this->response->success([
            'avatar' => url('storage/'.$path),
        ], 'Avatar updated successfully.');
    }
}
