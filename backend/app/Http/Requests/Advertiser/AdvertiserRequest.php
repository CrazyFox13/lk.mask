<?php

namespace App\Http\Requests\Advertiser;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdvertiserRequest extends ApiRequest
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
        $model = request()->route('advertiser');
        $id = $model?->id ?? 0;
        return [
            "name" => "required|string|max:255",
            "description" => "string|max:5000|nullable",
            "inn" => ['required', Rule::unique('advertisers', 'inn')->ignore($id)],
            "is_active" => "boolean",
            "start_date" => "required|date|before:end_date",
            "end_date" => "required|date|after:start_date"
        ];
    }
}
