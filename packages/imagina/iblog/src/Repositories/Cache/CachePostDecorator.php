<?php

namespace Imagina\Iblog\Repositories\Cache;

use Imagina\Icrud\Repositories\Cache\BaseCacheCrudDecorator;
use Imagina\Iblog\Repositories\PostRepository;

class CachePostDecorator extends BaseCacheCrudDecorator implements PostRepository
{
  public function __construct(PostRepository $post)
  {
    parent::__construct();
    $this->entityName = 'iblog.posts';
    $this->repository = $post;
  }

}
