<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\ApiRequest;
use App\Models\ReportDocument;

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
            'report_type_id' => 'required|integer|exists:report_types,id',
            'amount' => 'numeric|min:0|nullable',
            'text' => 'required|string|max:5000',
            'company_id' => 'sometimes|integer|exists:companies,id',
            'order_id' => 'sometimes|integer|exists:orders,id',
            'target_user_id' => 'required|integer|exists:users,id',
            'documents' => 'array',
            'documents.*.type' => 'required|in:' . implode(',', ReportDocument::TYPES),
            'documents.*.url' => 'required|url|max:255'
        ];
    }

    public function messages()
    {
        return [
            'documents.*.type.in' => 'The value must be one of: ' . implode(',', ReportDocument::TYPES)
        ];
    }
}
