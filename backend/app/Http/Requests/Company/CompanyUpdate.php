<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\ApiRequest;
use App\Models\CompanyDocument;
use App\Models\CompanyType;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class CompanyUpdate extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->route('company'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $company = request()->route('company');
        $selectedType = CompanyType::query()->find($company?->company_type_id);

        return [
            'company_type_id' => 'nullable|exists:company_types,id',
            'inn' => ['required', 'max:255', Rule::unique('companies', 'inn')->where('deleted_at', null)->ignore(request()->route('company')->id)],
            'title' => 'required|max:255',
            'full_title' => 'can_be_space|max:255',
            'ogrn' => 'can_be_space|max:255',
            'kpp' => 'can_be_space||max:255',
            'okpo' => 'can_be_space|max:255',
            'legal_address' => 'can_be_space||max:2000',
            'address' => 'required|max:2000',
            'director' => 'sometimes|can_be_space|max:255',
            'phone' => 'required|max:255',
            'email' => 'required|email|max:255',
            'website' => 'url|max:255|nullable',
            'description' => 'required|max:5000',
            'vehicle_types_id' => ['array', $selectedType && $selectedType->is_worker ? "min:1" : ""],
            'vehicle_types_id.*' => 'integer|exists:vehicle_types,id',
            'documents' => 'nullable|array',
            "documents.*.type" => "required|in:" . implode(',', CompanyDocument::TYPES),
            "documents.*.url" => "required|url",
        ];
    }

    public function messages()
    {
        return ['can_be_space' => 'The field is required'];
    }
}
