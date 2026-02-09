<?php

namespace App\Http\Requests\PhotoGroup;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class Update extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $company = $this->route('company');
        $group = $this->route('photo_group');
        return Gate::allows('update',[$group,$company]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => 'required|max:255',
            'photos' => 'sometimes|array',
            'photos.*' => 'url'
        ];
    }
}
