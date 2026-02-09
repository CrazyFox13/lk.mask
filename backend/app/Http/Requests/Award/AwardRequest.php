<?php

namespace App\Http\Requests\Award;

use App\Http\Requests\ApiRequest;

class AwardRequest extends ApiRequest
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
        return [
            'name' => 'required',
            'icon' => 'required',
            'description' => ''
        ];
    }
}
