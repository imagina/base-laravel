<?php

namespace Imagina\Iblog\Repositories\Cache;

use Imagina\Icrud\Repositories\Cache\BaseCacheCrudDecorator;
use Imagina\Iblog\Repositories\CategoryRepository;

class CacheCategoryDecorator extends BaseCacheCrudDecorator implements CategoryRepository
{
    public function __construct(CategoryRepository $category)
    {
        parent::__construct();
        $this->entityName = 'iblog.categories';
        $this->repository = $category;
    }
}
