<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Providers\FirebaseServiceProvider;
use App\Providers\ModuleServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // لو عندك API routes
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // هنا تقدر تضيف middleware لو محتاج
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // هنا تقدر تضيف custom exception handling
    })
    ->withProviders([
        FirebaseServiceProvider::class,
        ModuleServiceProvider::class,
    ])
    ->create();
