<?php

namespace Imagina\Iblog\Http\Controllers\Api;

use Imagina\Icrud\Http\Controllers\BaseCrudController;
use Imagina\Iblog\Entities\Category;
use Imagina\Iblog\Repositories\CategoryRepository;

class CategoryApiController extends BaseCrudController
{
    /**
     * @var CategoryRepository
     */
    public $model;

    public $modelRepository;

    public function __construct(Category $model, CategoryRepository $modelRepository)
    {
        $this->model = $model;
        $this->modelRepository = $modelRepository;
    }
}
