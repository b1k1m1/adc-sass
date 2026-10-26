<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withMiddleware(function (Middleware $middleware) {

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'perm'   => \App\Http\Middleware\CheckMenuPermission::class,
            'tenant' => \App\Http\Middleware\TenantDatabase::class,
            'platform.admin' => \App\Http\Middleware\PlatformAdmin::class,
        ]);

        $middleware->prependToPriorityList(
            before: \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            prepend: \App\Http\Middleware\TenantDatabase::class,
        );

        $middleware->appendToGroup(
            'web',
            \App\Http\Middleware\TenantDatabase::class
        );

        $middleware->appendToGroup(
            'web',
            \App\Http\Middleware\SetLocale::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
