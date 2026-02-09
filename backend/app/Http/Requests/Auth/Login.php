<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Mutator;
use App\Http\Requests\ApiRequest;

class Login extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone' => ['required', 'exists:users,phone'],
            'captcha_token' => ["required"]
        ];
    }

    protected function prepareForValidation()
    {
        if ($phone = request()->input("phone")) {
            $this->request->set("phone", Mutator::numberToDigits($phone));
        }
    }

    public function validationData()
    {
        return $this->request->all();
    }
}
