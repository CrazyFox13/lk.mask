<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Mutator;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class PasswordLogin extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return !auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'phone' => 'required_without:email',
            'email' => 'required_without:phone|exists:users,email',
            'captcha_token' => "required",
            'password' => 'required'
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
