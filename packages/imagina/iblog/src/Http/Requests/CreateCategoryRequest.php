<?php

namespace Imagina\Iblog\Http\Requests;

use Imagina\Icrud\Rules\UniqueSlugRule;

class CreateCategoryRequest
{
  public function rules()
  {
    return [];
  }

  public function translationRules()
  {
    return [
      'title' => 'required|min:1',
      'slug' => ['required', new UniqueSlugRule('iblog__category_translations', null, null,
        trans('iblog::category.messages.sameSlug', ['slug' => $this->input(locale() . '.slug')])), 'min:1', "alpha_dash:ascii"],
      'description' => 'min:1',
    ];
  }

  public function authorize()
  {
    return true;
  }

  public function messages()
  {
    return [];
  }

  public function translationMessages()
  {
    return [
      'title.required' => trans('iblog::common.messages.title is required'),
      'title.min:1' => trans('iblog::common.messages.title min 2 '),
      // slug
      'slug.required' => trans('iblog::common.messages.field required'),
      'slug.min:1' => trans('iblog::common.messages.min 2 characters'),

      // description
      'description.required' => trans('iblog::common.messages.field required'),
      'description.min:1' => trans('iblog::common.messages.min 2 characters'),
    ];
  }

  public function getValidator()
  {
    return $this->getValidatorInstance();
  }
}
