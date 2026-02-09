<?php

namespace App\Http\Requests\Moderator;

use App\Helpers\Mutator;
use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class Update extends ApiRequest
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

    public function rules()
    {
        $user = $this->route('moderator');
        return [
            'name' => 'required|max:255',
            'surname' => 'required|max:255',
            'phone' => ['required', "max:255", Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['required', "max:255", "email", Rule::unique('users', 'email')->ignore($user->id)],
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
