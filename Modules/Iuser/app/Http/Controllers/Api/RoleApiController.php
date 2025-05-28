<?php

namespace Modules\Iuser\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Iuser\Models\Role;
use Modules\Iuser\Repositories\RoleRepository;

class RoleApiController extends CoreApiController
{
  public function __construct(Role $model, RoleRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
