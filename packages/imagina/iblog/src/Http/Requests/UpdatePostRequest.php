<?php

namespace Imagina\Iblog\Http\Requests;

use Imagina\Icrud\Rules\UniqueSlugRule;

class UpdatePostRequest
{
  public function rules()
  {
    return [
      //'category_id' => 'required',
    ];
  }

  public function translationRules()
  {
    return [
      'name' => 'min:1',
      'slug' => ['min:1', "alpha_dash:ascii"],
      'description' => 'min:1',
    ];
  }

  public function authorize()
  {
    return true;
  }

  public function messages()
  {
    return [

    ];
  }

  public function translationMessages()
  {
    return [
      // title
      'name.required' => trans('icommerce::common.messages.field required'),
      'name.min:1' => trans('icommerce::common.messages.min 2 characters'),

      // slug
      'slug.required' => trans('icommerce::common.messages.field required'),
      'slug.min:1' => trans('icommerce::common.messages.min 2 characters'),

      // description
      'description.required' => trans('icommerce::common.messages.field required'),
      'description.min:1' => trans('icommerce::common.messages.min 2 characters'),
    ];
  }
}
