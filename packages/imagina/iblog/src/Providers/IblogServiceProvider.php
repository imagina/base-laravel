<?php

namespace Imagina\Iblog\Providers;

use Illuminate\Support\ServiceProvider;

class IblogServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/apiRoutes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'iblog');
    }

    public function register(): void
    {
        //
    }
}
