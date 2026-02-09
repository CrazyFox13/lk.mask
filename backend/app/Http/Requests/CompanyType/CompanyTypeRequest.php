<?php

namespace App\Http\Requests\CompanyType;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class CompanyTypeRequest extends ApiRequest
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
            "title"=>"required|max:255",
            "is_worker"=>"boolean"
        ];
    }
}
