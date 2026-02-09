<?php

namespace App\Http\Requests\Employee;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class Destroy extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $employee = $this->route("employee");
        return Gate::allows('delete', $employee);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'password' => 'required_without:code',
            'code' => 'required_without:password',
        ];
    }
}
