<?php

namespace App\Http\Requests\Moderator;

use App\Helpers\Mutator;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class Create extends ApiRequest
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
            'name'=>'required|max:255',
            'surname'=>'required|max:255',
            'email'=>'required|max:255|email|unique:users,email',
            'phone'=>'required|max:255|unique:users,phone',
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
