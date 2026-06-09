<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    private const VITE_DEV_ORIGINS = ['http://localhost:5173', 'http://127.0.0.1:5173'];

    private array $cspPolicies = [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
        "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://cdn.jsdelivr.net",
        "font-src 'self' https://fonts.bunny.net",
        "img-src 'self' data: blob:",
        "connect-src 'self'",
        "frame-src 'self'",
        "object-src 'none'",
        "base-uri 'self'",
        "form-action 'self'",
    ];

    public function __construct()
    {
        if (app()->environment('local')) {
            $httpOrigins = implode(' ', self::VITE_DEV_ORIGINS);
            $wsOrigins = collect(self::VITE_DEV_ORIGINS)
                ->map(fn ($o) => preg_replace('/^http/', 'ws', $o))
                ->implode(' ');
            $this->addToCsp('script-src', $httpOrigins);
            $this->addToCsp('style-src', $httpOrigins);
            $this->addToCsp('connect-src', "$httpOrigins $wsOrigins");
        }
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if (! $request->is('api/*')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $this->cspPolicies));
        }

        return $response;
    }

    private function addToCsp(string $directive, string $value): void
    {
        foreach ($this->cspPolicies as $i => $policy) {
            if (str_starts_with($policy, $directive)) {
                $this->cspPolicies[$i] = $policy . ' ' . $value;

                return;
            }
        }
    }
}
