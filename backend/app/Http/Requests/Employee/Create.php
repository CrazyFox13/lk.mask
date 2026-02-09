<?php

namespace App\Http\Requests\Employee;

use App\Helpers\Mutator;
use App\Http\Requests\ApiRequest;
use App\Models\User;

class Create extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        $company = $this->route('company');
        return $user->company_id === $company->id && $user->isBoss();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone'
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
