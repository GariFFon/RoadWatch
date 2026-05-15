<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsurePasswordIsSet;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust Railway's load balancer proxy (handles SSL termination)
        $middleware->trustProxies(at: '*');

        // Required for auth:web to work on API routes:
        // 1. EncryptCookies   → decrypts the laravel_session cookie
        // 2. StartSession     → loads the session from the decrypted cookie
        $middleware->api(prepend: [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);
        $middleware->alias([
            'password.setup' => EnsurePasswordIsSet::class,
            'role'           => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();


