<?php

namespace Imagina\Iblog\Http\Requests;


class IblogRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // only allow updates if the user is logged in
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|min:2',
            'description' => 'required|min:2',
        ];
    }

    /**
     * Get the validation attributes that apply to the request.
     */
    public function attributes(): array
    {
        return [
            //
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     */
    public function messages(): array
    {
        return [
            'title.required' => trans('iblog::common.messages.title is required'),
            'title.min:2' => trans('iblog::common.messages.title min 2 '),
            'description.required' => trans('iblog::common.messages.description is required'),
            'description.min:2' => trans('iblog::common.messages.description min 2 '),
        ];
    }
}
