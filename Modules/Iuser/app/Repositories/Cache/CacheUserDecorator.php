<?php

namespace Modules\Iuser\Repositories\Cache;

use Modules\Iuser\Repositories\UserRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheUserDecorator extends CoreCacheDecorator implements UserRepository
{
    public function __construct(UserRepository $user)
    {
        parent::__construct();
        $this->entityName = 'iuser.users';
        $this->repository = $user;
    }
}
