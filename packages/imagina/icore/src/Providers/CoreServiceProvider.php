<?php

namespace Imagina\Icore\Providers;

use Illuminate\Support\ServiceProvider;

class CoreServiceProvider extends ServiceProvider
{
    protected array $providers = [
        \Imagina\Icrud\Providers\IcrudServiceProvider::class,
        \Imagina\User\Providers\UserServiceProvider::class,
        \Imagina\Page\Providers\PageServiceProvider::class,
        \Imagina\Iblog\Providers\IblogServiceProvider::class,
    ];

    public function register(): void
    {
        foreach ($this->providers as $provider) {
            try {
                $this->app->register($provider);
            } catch (\Throwable $e) {
                logger()->warning("Failed to register $provider: " . $e->getMessage());
            }
        }
    }

    public function boot(): void
    {
        foreach ($this->providers as $provider) {
            try {
                $instance = new $provider($this->app);
                $instance->boot();
            } catch (\Throwable $e) {
                logger()->warning("Failed to boot $provider: " . $e->getMessage());
            }
        }
    }
}
