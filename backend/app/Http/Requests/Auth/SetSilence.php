<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class SetSilence extends ApiRequest
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
            'silence' => 'required|boolean',
            'silence_from' => 'required|integer|min:0|max:23',
            'silence_from_m' => 'required|integer|min:0|max:59',
            'silence_to' => 'required|integer|min:0|max:23',
            'silence_to_m' => 'required|integer|min:0|max:59',
        ];
    }
}
