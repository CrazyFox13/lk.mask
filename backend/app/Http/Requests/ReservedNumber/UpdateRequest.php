<?php

namespace App\Http\Requests\ReservedNumber;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends ApiRequest
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
            "number" => ["required",
                "string", "size:6",
                Rule::unique("reserved_numbers", 'number')
                    ->ignore($this->route('reserved_number')->id),
                'unique:companies,reg_number'
            ]
        ];
    }
}
