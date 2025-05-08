<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ImportKelkooProducts;
use App\Console\Commands\ProductexistCheck;
use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\FetchProduct;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        ImportKelkooProducts::class,
        ProductexistCheck::class,
        FetchProduct::class,
        GenerateSitemap::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
