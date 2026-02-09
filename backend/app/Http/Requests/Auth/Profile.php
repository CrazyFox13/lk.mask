<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Mutator;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class Profile extends ApiRequest
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
        $rules = [
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'phone' => ['required', Rule::unique('users', 'phone')->ignore(auth()->id())],
            'email' => ['required', "email", Rule::unique('users', 'email')->ignore(auth()->id())],

        ];
        $user = auth()->user();
        if ($user->isBoss()) {
            $rules = array_merge($rules, [
                #'city' => 'required_without:city_id|max:255',
                'geo_city_id' => 'required|exists:geo_cities,id'
            ]);
        }
        return $rules;
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
