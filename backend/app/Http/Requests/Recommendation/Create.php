<?php

namespace App\Http\Requests\Recommendation;

use App\Http\Requests\ApiRequest;

class Create extends ApiRequest
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
            'company_id'=>'sometimes|integer|exists:companies,id',
            'target_user_id'=>'required|integer|exists:users,id',
            'text'=>'required|string|min:15|max:5000'
        ];
    }
}
