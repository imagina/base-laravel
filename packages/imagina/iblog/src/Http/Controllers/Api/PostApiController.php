<?php

namespace Imagina\Iblog\Http\Controllers\Api;

use Imagina\Icrud\Http\Controllers\BaseCrudController;
use Imagina\Iblog\Entities\Post;
use Imagina\Iblog\Repositories\PostRepository;

class PostApiController extends BaseCrudController
{
    public $model;

    public $modelRepository;

    public function __construct(Post $model, PostRepository $modelRepository)
    {
        $this->model = $model;
        $this->modelRepository = $modelRepository;
    }
}
