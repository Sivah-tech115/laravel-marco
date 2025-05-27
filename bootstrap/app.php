<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Console\Commands\ImportKelkooProducts;
use App\Console\Commands\ProductexistCheck;
use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\FetchProduct;
use App\Console\Commands\FetchCategory;
use App\Console\Commands\MigrateTables;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        ImportKelkooProducts::class,
        ProductexistCheck::class,
        FetchProduct::class,
        GenerateSitemap::class,
        FetchCategory::class,
        MigrateTables::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
