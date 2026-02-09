<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\ApiRequest;
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
        $photo = $this->route('photo');
        return Gate::allows('update', [$photo, $company, $group]);
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
