<?php

namespace Imagina\Iblog\Providers;

use Illuminate\Support\ServiceProvider;

use Imagina\Iblog\Repositories\PostRepository;
use Imagina\Iblog\Repositories\Eloquent\EloquentPostRepository;
use Imagina\Iblog\Entities\Post;

use Imagina\Iblog\Repositories\CategoryRepository;
use Imagina\Iblog\Repositories\Eloquent\EloquentCategoryRepository;
use Imagina\Iblog\Entities\Category;

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
        $this->app->bind(PostRepository::class, function () {
            return new EloquentPostRepository(new Post());
        });

        $this->app->bind(CategoryRepository::class, function () {
            return new EloquentCategoryRepository(new Category());
        });
    }
}
