<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\ApiRequest;
use App\Models\CompanyDocument;
use App\Models\CompanyType;
use Illuminate\Validation\Rule;

class CompanyCreate extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth()->user();
        return !$user->company_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $selectedType = CompanyType::query()->find(request()->get("company_type_id"));

        return [
            'company_type_id' => 'nullable|exists:company_types,id',
            'inn' => 'required|max:255',
            'title' => 'required|max:255',
            'full_title' => 'required|max:255',
            'ogrn' => 'required|max:255',
            'kpp' => 'required|max:255',
            'okpo' => 'required|max:255',
            'legal_address' => 'required|max:2000',
            'address' => 'required|max:2000',
            'director' => 'required|max:255',
            'phone' => 'required|max:255',
            'email' => 'required|email|max:255',
            'website' => 'url|max:255|nullable',
            'description' => 'required|max:5000',
            'vehicles_types_id' => ['sometimes', 'array', $selectedType && $selectedType->is_worker ? "min:1" : ""],
            'vehicles_types_id.*' => 'integer|exists:vehicle_types,id',
            'documents' => 'nullable|array',
            "documents.*.type" => "required|in:" . implode(',', CompanyDocument::TYPES),
            "documents.*.url" => "required|url",
        ];
    }
}
