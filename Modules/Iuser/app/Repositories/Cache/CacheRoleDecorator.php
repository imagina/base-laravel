<?php

namespace Modules\Iuser\Repositories\Cache;

use Modules\Iuser\Repositories\RoleRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheRoleDecorator extends CoreCacheDecorator implements RoleRepository
{
    public function __construct(RoleRepository $role)
    {
        parent::__construct();
        $this->entityName = 'iuser.roles';
        $this->repository = $role;
    }
}
