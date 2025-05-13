<?php

namespace Imagina\Iblog\Http\Requests;

class CreatePostRequest
{
  public function rules()
  {
    return [
      'category_id' => 'required',
    ];
  }

  public function translationRules()
  {
    return [
      'title' => 'required|min:1',
      'summary' => 'required|min:1',
      'slug' => ['required', 'min:1', "alpha_dash:ascii"],
      'description' => 'required|min:1',
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
      'summary.required' => trans('iblog::common.messages.summary is required'),
      'summary.min:1' => trans('iblog::common.messages.summary min 2 '),
      'description.required' => trans('iblog::common.messages.description is required'),
      'description.min:1' => trans('iblog::common.messages.description min 2 '),
      // slug
      'slug.required' => trans('iblog::common.messages.field required'),
      'slug.min:1' => trans('iblog::common.messages.min 2 characters'),

    ];
  }
}
