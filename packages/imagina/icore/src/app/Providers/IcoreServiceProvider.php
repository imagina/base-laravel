<?php

namespace Imagina\Icore\App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Imagina\Icore\Routes\RouterGenerator;

class IcoreServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register apiCrud as a router macro
        Route::macro('apiCrud', function ($params) {
            app(RouterGenerator::class)->apiCrud($params);
        });
    }

    public function register(): void
    {
        //
    }
}
