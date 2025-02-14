<?php

use App\Http\Middleware\RestrictFrontendAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php', //Necessary for APIs to work
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
        //$middleware->append(HandleCors::class); // Add CORS Middleware
        //$middleware->append(RestrictFrontendAccess::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
