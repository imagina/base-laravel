<?php

namespace Modules\Iuser\Http\Requests;

use Imagina\Icore\Http\Request\CoreFormRequest;

class CreateRoleRequest extends CoreFormRequest
{
    public function rules()
    {
        return [
            'name' => 'required',
            'slug' => 'required|unique:iuser__roles,slug'
        ];
    }

    public function translationRules()
    {
        return [
            'title' => 'required'
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
        return [];
    }

    public function getValidator(){
        return $this->getValidatorInstance();
    }

}
