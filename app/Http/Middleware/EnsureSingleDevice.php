<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'student') {
            $deviceId = $request->header('X-Device-ID') ?: $request->input('device_id');

            if (!$deviceId || $user->active_device_id !== $deviceId) {
                // Instantly invalidate token to force logout on breach
                $user->tokens()->delete();
                // Note: User active_device_id remains locked (not cleared) as per spec, to prevent them from binding to a new device on their own.

                return response()->json([
                    'success' => false,
                    'message' => 'هذا الحساب مسجل على جهاز آخر، يرجى التواصل مع الإدارة'
                ], 403);
            }
        }

        return $next($request);
    }
}
