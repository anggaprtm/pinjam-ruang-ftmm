<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSignageApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) config('services.signage.key', '');

        // If key is not configured, keep endpoint accessible (dev/backward compatibility).
        if ($expectedKey === '') {
            return $next($request);
        }

        $providedKey = (string) ($request->header('X-SIGNAGE-KEY') ?? $request->query('signage_key', ''));

        if (!hash_equals($expectedKey, $providedKey)) {
            return response()->json([
                'message' => 'Unauthorized signage API access.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
