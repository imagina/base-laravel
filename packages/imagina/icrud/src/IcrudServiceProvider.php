<?php

namespace Imagina\Icrud;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Imagina\Icrud\Routing\RouterGenerator;

class IcrudServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register apiCrud as a router macro
        app(Router::class)::macro('apiCrud', function ($params) {
            app(RouterGenerator::class)->apiCrud($params);
        });
    }

    public function register(): void
    {
        //
    }
}
