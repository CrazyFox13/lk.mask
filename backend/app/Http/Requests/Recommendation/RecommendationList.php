<?php

namespace App\Http\Requests\Recommendation;

use App\Http\Requests\ApiRequest;

class RecommendationList extends ApiRequest
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
        $user = auth()->user();
        if ($user->isAdmin() || $user->isModerator()) return [];
        return [
            'filter' => 'required|in:by_me,to_me,to_company,to_user',
            'company_id' => 'required_if:filter,to_company|integer|exists:companies,id',
            'user_id' => 'required_if:filter,to_user|integer|exists:users,id'
        ];
    }

    public function messages()
    {
        return [
            'filter.in' => 'The value must be one of: by_me,to_me,to_company,to_user',
        ];
    }
}
