<?php

namespace App\Http\Requests\Report;

use App\Http\Requests\ApiRequest;
use App\Models\ReportDocument;
use Illuminate\Support\Facades\Gate;

class Update extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->route('report'));
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
            'documents' => 'array',
            'documents.*.type' => 'required|in:' . implode(',', ReportDocument::TYPES),
            'documents.*.url' => 'required|url|max:255'
        ];
    }

    public function messages()
    {
        return [
            'documents.*.type.in' => 'Must be one of the values:' . implode(',', ReportDocument::TYPES)
        ];
    }
}
