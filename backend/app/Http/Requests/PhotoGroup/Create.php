<?php

namespace App\Http\Requests\PhotoGroup;

use App\Http\Requests\ApiRequest;
use App\Models\PhotoGroup;
use Illuminate\Support\Facades\Gate;

class Create extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $company = $this->route('company');
        return Gate::allows('create',[PhotoGroup::class,$company]);
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
