<?php

namespace App\Http\Middleware;

use App\Models\RfidLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ThrottleRfidScans
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 10, int $decaySeconds = 60): Response
    {
        $rfidUid = $request->input('rfid_uid');

        if (!$rfidUid) {
            return $next($request);
        }

        // Check rate limiting per UID
        if (RfidLog::isRateLimited($rfidUid, $maxAttempts, $decaySeconds)) {
            return response()->json([
                'message' => 'Too many scan attempts. Please wait before trying again.',
                'status' => 'rate_limited',
            ], 429);
        }

        // Check rate limiting per IP
        $recentScansFromIp = RfidLog::where('ip_address', $request->ip())
            ->where('created_at', '>=', now()->subSeconds($decaySeconds))
            ->count();

        if ($recentScansFromIp >= ($maxAttempts * 2)) {
            return response()->json([
                'message' => 'Too many requests from your device. Please wait.',
                'status' => 'rate_limited',
            ], 429);
        }

        return $next($request);
    }
}
