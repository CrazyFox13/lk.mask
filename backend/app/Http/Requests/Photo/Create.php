<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\ApiRequest;
use App\Models\Photo;
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
        $group = $this->route('photo_group');
        $company = $this->route('company');
        return Gate::allows('create', [Photo::class, $company, $group]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "url" => "required|url|max:255",
            "description" => "sometimes|max:5000"
        ];
    }
}
