<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if ($user && $user->two_factor_enabled && !session('two_factor_verified')) {
            return redirect()->route('two-factor.verify');
        }

        return $next($request);
    }
}
