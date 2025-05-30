<?php

namespace Modules\Iuser\Http\Requests;

use Imagina\Icore\Http\Request\CoreFormRequest;
use Imagina\Icore\Http\Rules\UniqueRule;

class CreateUserRequest extends CoreFormRequest
{
    public function rules()
    {
        return [
            'email' => ['required', 'email',new UniqueRule('iuser__users', null, null, trans('iuser::users.messages.unavailableUserName'))],
            'password' => 'required|confirmed|min:8',
            'roles' => 'required|array'
        ];
    }

    public function translationRules()
    {
        return [];
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
