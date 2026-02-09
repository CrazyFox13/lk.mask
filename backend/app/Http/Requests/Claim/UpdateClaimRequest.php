<?php

namespace App\Http\Requests\Claim;

use App\Http\Requests\ApiRequest;
use App\Models\ClaimDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateClaimRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->route('claim'));
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
            'documents' => 'sometimes|array|min:1',
            'documents.*.type' => 'in:' . implode(',', ClaimDocument::TYPES),
            'documents.*.url' => 'url'
        ];
    }
}
