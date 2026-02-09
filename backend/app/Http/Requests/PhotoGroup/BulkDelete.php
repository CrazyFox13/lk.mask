<?php

namespace App\Http\Requests\PhotoGroup;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Facades\Gate;

class BulkDelete extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $company = $this->route('company');
        $group = $this->route('photoGroup');
        return Gate::allows('delete',[$group,$company]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'photos_id'=>'required|array',
            'photos_id.*'=>'exists:photos,id'
        ];
    }
}
