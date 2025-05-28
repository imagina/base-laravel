<?php

namespace Modules\Iuser\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Iuser\Models\User;
use Modules\Iuser\Repositories\UserRepository;

class UserApiController extends CoreApiController
{
  public function __construct(User $model, UserRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
