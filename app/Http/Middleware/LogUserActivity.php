<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (auth()->check() && $request->method() === 'GET') {
            // Log page visits for authenticated users
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->event('page-visit')
                ->log('Page Visit: '.$request->path());
        }

        return $response;
    }
}
