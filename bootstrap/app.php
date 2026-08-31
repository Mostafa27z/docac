<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'course.enrollment' => \App\Http\Middleware\EnsureCourseEnrollment::class,
            'single.device' => \App\Http\Middleware\EnsureSingleDevice::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'انتهت صلاحية الجلسة، يرجى إعادة المحاولة.'
                ], 419);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'انتهت صلاحية الصفحة أو الجلسة، يرجى إعادة المحاولة.'
            ]);
        });
    })->create();
