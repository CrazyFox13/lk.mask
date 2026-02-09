<?php

namespace App\Http\Requests\Claim;

use App\Http\Requests\ApiRequest;
use App\Models\ClaimDocument;

class CreateClaimRequest extends ApiRequest
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
            'order_id' => 'required|integer|exists:orders,id',
            'text' => 'required|max:5000',
            'documents' => 'sometimes|array',
            'documents.*.type' => 'in:' . implode(',', ClaimDocument::TYPES),
            'documents.*.url' => 'url'
        ];
    }
}
