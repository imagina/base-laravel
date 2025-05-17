<?php

namespace Imagina\Workshop\Providers;

use Illuminate\Support\ServiceProvider;
use Imagina\Workshop\Commands\MakeModuleCommand;
use Imagina\Workshop\Commands\MakeModelCommand;

class WorkshopServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register command here
        $this->commands([
            MakeModuleCommand::class,
            MakeModelCommand::class,
        ]);
    }
}
