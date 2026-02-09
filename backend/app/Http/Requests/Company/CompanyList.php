<?php

namespace App\Http\Requests\Company;

use App\Http\Requests\ApiRequest;

class CompanyList extends ApiRequest
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
        $adminFilter = $user && ($user->isAdmin() || $user->isModerator());
        return [
            'sort_by' => ['sometimes', $adminFilter ? '' : 'in:created_at_desc,created_at_asc,rating_desc,rating_asc'],
            'cities_id' => 'sometimes|array',
            'cities_id.*' => 'required|integer|exists:cities,id',
            'rating' => 'sometimes|integer|min:0',
            'vehicle_types_id' => 'sometimes|array',
            'vehicle_types_id.*' => 'required|integer',

        ];
    }

    public function messages()
    {
        return [
            'sort_by.in' => 'The value must be one of: created_at_desc,created_at_asc,rating_desc,rating_asc'
        ];
    }

    protected function prepareForValidation()
    {
        if ($typesId = request()->input("vehicle_types_id")) {
            $this->request->set("vehicle_types_id", explode(',', $typesId));
        }

        if ($citiesId = request()->input("cities_id")) {
            $this->request->set("cities_id", explode(',', $citiesId));
        }
    }

    public function validationData(): array
    {
        return array_merge($this->all(), $this->request->all());
    }
}
