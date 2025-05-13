<?php

namespace Imagina\Icrud\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Imagina\Icrud\Routing\RouterGenerator;

class IcrudServiceProvider extends ServiceProvider
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
