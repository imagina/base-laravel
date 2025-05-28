<?php

namespace Modules\Iuser\Transformers;

use Imagina\Icore\Transformers\CoreResource;

class RoleTransformer extends CoreResource
{
  /**
   * Attribute to exclude relations from transformed data
   * @var array
   */
  protected $excludeRelations = [];

  /**
  * Method to merge values with response
  *
  * @return array
  */
  public function modelAttributes($request)
  {
    return [];
  }
}
