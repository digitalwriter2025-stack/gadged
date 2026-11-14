<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The hosting platform terminates HTTPS at its proxy, so trust it to report the real scheme and client IP.
        $middleware->trustProxies(at: '*');

        // Protected by DEPLOY_TOKEN instead, so it can be called from the hosting:clear-cache command.
        $middleware->validateCsrfTokens(except: ['clear-cache', 'clean-cache']);

        // Set by the Meta Pixel script and read by the Conversions API, so they are never encrypted.
        $middleware->encryptCookies(except: ['_fbp', '_fbc']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
