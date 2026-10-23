<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);

        if ($request->is('api/*')) {
            $status = $response->getStatusCode();
            $context = [
                'method' => $request->method(),
                'route' => $request->route()?->uri() ?? 'unmatched',
                'status' => $status,
                'user_id' => $request->user()?->getAuthIdentifier(),
                'cf_ray' => $request->header('CF-Ray'),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            ];

            $level = match (true) {
                $status >= 500 => 'error',
                $status >= 400 => 'warning',
                default => 'info',
            };

            Log::log($level, 'API request completed', $context);
        }

        return $response;
    }
}
