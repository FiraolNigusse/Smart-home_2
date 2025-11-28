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
            'role' => \App\Http\Middleware\CheckRole::class,
            'forcehttps' => \App\Http\Middleware\ForceHttps::class,
            'session.security' => \App\Http\Middleware\ValidateSessionSecurity::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
        ]);
        
        // Apply security headers globally
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
    })
    ->withSchedule(function ($schedule): void {
        // Daily backup at 2 AM
        $schedule->command('system:backup')->dailyAt('02:00');
        
        // Check for config changes daily at 3 AM
        $schedule->command('system:log-config-changes')->dailyAt('03:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
