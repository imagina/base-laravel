<?php

namespace Imagina\Iblog\Transformers;

use Imagina\Icrud\Transformers\CrudResource;

class PostTransformer extends CrudResource
{
  /**
   * Method to merge values with response
   *
   * @return array
   */
  public function modelAttributes($request)
  {
    return [
      'statusName' => $this->present()->status,
      'url' => $this->url ?? '#',
      'mainImage' => $this->main_image,
      'secondaryImage' => $this->when($this->secondary_image, $this->secondary_image),
      'gallery' => $this->gallery,
      'layoutId' => $this->layoutId,
      'tags' => $this->getNameTags()
    ];
  }
}
