<?php

namespace App\Modules\API\Controllers;

use App\Modules\API\Services\ApiResponseService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    public function __construct(
        protected ApiResponseService $response,
        protected Google2FA $google2fa,
    ) {}

    /**
     * Enable two-factor authentication.
     * Generates secret + QR code + recovery codes, but does NOT activate 2FA yet.
     * The user must verify a valid TOTP code first via /auth/2fa/verify-setup.
     */
    public function enable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return $this->response->error('Two-factor authentication is already enabled.', 400);
        }

        $secret = $this->google2fa->generateSecretKey();

        // Store secret and recovery codes but do NOT enable yet — pending verification
        $user->update([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $this->generateRecoveryCodes(),
        ]);

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret,
        );

        return $this->response->success([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $user->two_factor_recovery_codes,
        ], 'Scan the QR code with your authenticator app, then verify with a code to activate 2FA.');
    }

    /**
     * Verify a TOTP code to activate 2FA setup.
     * Called after /auth/2fa/enable — the user scans the QR, generates a code,
     * and this endpoint confirms it works before marking 2FA as active.
     */
    public function verifySetup(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if ($user->two_factor_enabled) {
            return $this->response->error('Two-factor authentication is already enabled.', 400);
        }

        if (! $user->two_factor_secret) {
            return $this->response->error('No pending 2FA setup. Please start setup first.', 400);
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (! $valid) {
            return $this->response->error('Invalid verification code. Please try again.', 422);
        }

        // Code is valid — activate 2FA
        $user->update([
            'two_factor_enabled' => true,
        ]);

        return $this->response->success(null, 'Two-factor authentication has been activated successfully.');
    }

    /**
     * Verify a two-factor authentication code.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'code' => ['required', 'string', 'size:6'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::findOrFail($request->user_id);

        if (! $user->two_factor_enabled) {
            return $this->response->error('Two-factor authentication is not enabled for this account.', 400);
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (! $valid) {
            return $this->response->error('Invalid verification code. Please try again.', 422);
        }

        $token = $user->createToken($request->device_name ?? 'mobile-api')->plainTextToken;

        return $this->response->success([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Two-factor authentication verified successfully.');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_enabled) {
            return $this->response->error('Two-factor authentication is not enabled.', 400);
        }

        $valid = $this->google2fa->verifyKey(
            $user->two_factor_secret,
            $request->code
        );

        if (! $valid) {
            return $this->response->error('Invalid verification code.', 422);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_enabled' => false,
        ]);

        return $this->response->success(null, 'Two-factor authentication has been disabled.');
    }

    /**
     * Generate recovery codes for 2FA.
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];

        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(
                implode('-', [
                    substr(bin2hex(random_bytes(10)), 0, 4),
                    substr(bin2hex(random_bytes(10)), 0, 4),
                    substr(bin2hex(random_bytes(10)), 0, 4),
                ])
            );
        }

        return $codes;
    }
}
