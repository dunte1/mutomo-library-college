<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Requests\ChangePasswordRequest;
use App\Modules\API\Requests\ForgotPasswordRequest;
use App\Modules\API\Requests\LoginRequest;
use App\Modules\API\Requests\RegisterRequest;
use App\Modules\API\Requests\ResetPasswordRequest;
use App\Modules\API\Resources\UserResource;
use App\Modules\API\Services\ApiResponseService;
use App\Modules\API\Services\AuthenticationService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthenticationService $authService,
        protected ApiResponseService $response,
    ) {}

    /**
     * Login and receive an API token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (! $user->is_active) {
            return $this->response->error(
                'Your account has been deactivated. Please contact the library administrator.',
                403
            );
        }

        // Check if 2FA is enabled
        if ($user->two_factor_enabled) {
            $oneTimeToken = $user->createToken('2fa-pending')->plainTextToken;

            return $this->response->success([
                'requires_two_factor' => true,
                'temp_token' => $oneTimeToken,
                'user_id' => $user->id,
            ], 'Please verify your two-factor authentication code.');
        }

        $token = $this->authService->createToken($user, $request->device_name);

        $user->load(['roles', 'department', 'program', 'member.libraryCard', 'activeSubscription.plan']);

        return $this->response->success([
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 1440) * 60,
        ]);
    }

    /**
     * Register a new user account.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        $token = $this->authService->createToken($user, $request->device_name);

        $user->load(['roles', 'member']);

        return $this->response->success([
            'user' => new UserResource($user),
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 1440) * 60,
        ], 'Registration successful. Please check your email to verify your account.', 201);
    }

    /**
     * Logout and revoke the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeCurrentToken($request->user());

        return $this->response->success(null, 'Logged out successfully.');
    }

    /**
     * Get the authenticated user's profile.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $this->authService->getProfile($request->user());

        return $this->response->success(new UserResource($user));
    }

    /**
     * Refresh the current token (revoke old, issue new).
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $deviceName = $user->currentAccessToken()->name;

        $this->authService->revokeCurrentToken($user);

        $newToken = $this->authService->createToken($user, $deviceName);

        return $this->response->success([
            'token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration', 1440) * 60,
        ]);
    }

    /**
     * Send password reset link to email.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordResetLink($request->email);

        if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            return $this->response->success(null, 'Password reset link has been sent to your email.');
        }

        return $this->response->error('Unable to send password reset link. Please try again later.', 500);
    }

    /**
     * Reset password using token.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->resetPassword($request->validated());

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            return $this->response->success(null, 'Password has been reset successfully.');
        }

        return $this->response->error(
            $status === \Illuminate\Support\Facades\Password::INVALID_TOKEN
                ? 'Invalid or expired password reset token.'
                : 'Unable to reset password. Please try again.',
            400
        );
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $success = $this->authService->changePassword(
            $request->user(),
            $request->current_password,
            $request->new_password
        );

        if (! $success) {
            return $this->response->error('Current password is incorrect.', 422);
        }

        return $this->response->success(null, 'Password changed successfully. Other sessions have been logged out.');
    }

    /**
     * Verify email address.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->response->success(null, 'Email is already verified.');
        }

        $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'hash' => ['required', 'string'],
        ]);

        if ((int) $request->id !== $user->id) {
            return $this->response->error('Invalid verification link.', 400);
        }

        if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return $this->response->error('Invalid verification hash.', 400);
        }

        $this->authService->verifyEmail($user);

        return $this->response->success(null, 'Email verified successfully.');
    }

    /**
     * Resend email verification link.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->response->success(null, 'Email is already verified.');
        }

        $this->authService->resendVerification($user);

        return $this->response->success(null, 'Verification link has been sent to your email.');
    }
}
