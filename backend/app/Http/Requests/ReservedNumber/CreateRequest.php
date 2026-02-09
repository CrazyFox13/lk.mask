<?php

namespace App\Http\Requests\ReservedNumber;

use App\Http\Requests\ApiRequest;

class CreateRequest extends ApiRequest
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
            "number" => "required|string|size:6|unique:reserved_numbers,number|unique:companies,reg_number"
        ];
    }
}
