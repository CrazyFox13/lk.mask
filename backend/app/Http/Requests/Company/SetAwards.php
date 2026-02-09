<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\ApiRequest;

class SetAwards extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth('sanctum')->user();
        return $user->isModerator() || $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'awards' => 'array',
            'awards.*' => 'integer|exists:awards,id'
        ];
    }
}
