<?php

namespace App\Http\Requests\Employee;

use App\Helpers\Mutator;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class Update extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = $this->route('employee') ?: $this->route('user');
        return Gate::allows('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $user = $this->route('employee') ?: $this->route('user');
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
