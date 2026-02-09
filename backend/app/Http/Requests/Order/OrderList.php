<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Validation\Rule;

class OrderList extends ApiRequest
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
            'company_id' => 'sometimes|integer|exists:companies,id|nullable',
            'user_id' => 'sometimes|integer|exists:users,id',
            'not_mine' => 'sometimes|boolean',
            'lat' => 'sometimes|numeric|between:-90,90',
            'lng' => 'sometimes|numeric|between:-180,180',
            'radius' => 'sometimes|numeric',
            'vehicle_types_id' => 'sometimes|array',
            'vehicle_types_id.*' => 'integer|exists:vehicle_types,id',
            'cities_id' => 'sometimes|array',
            'cities_id.*' => 'integer|exists:cities,id',
            'regions_id' => 'sometimes|array',
            'regions_id.*' => 'integer|exists:regions,id',
            'shifts' => 'sometimes|in:one,two,less_five,more_five',
            'date_range' => ['sometimes', 'array', 'size:2', function ($attribute, $value, $fail) {
                try {
                    Carbon::parse($value[0]) && Carbon::parse($value[1]);
                } catch (InvalidFormatException $e) {
                    return $fail('The ' . $attribute . ' is invalid.');
                }
                if (Carbon::parse($value[1])->isBefore(Carbon::parse($value[0]))) {
                    return $fail('The ' . $attribute . ' is invalid.');
                }
            }],
            'date' => 'sometimes|date',
            'amount_by_agreement' => 'sometimes|boolean',
            'amount_with_vat' => 'sometimes|boolean',
            'amount_cash' => 'sometimes|boolean',
            'with_company' => 'sometimes|boolean',
            'liked' => 'sometimes|boolean'
        ];
    }

    protected function prepareForValidation()
    {

        if ($dateRange = request()->input("date_range")) {
            $this->request->set("date_range", explode(',', $dateRange));
        }

        if ($typesId = request()->input("vehicle_types_id")) {
            $this->request->set("vehicle_types_id", array_map('intval', explode(',', $typesId)));
        }

        if ($citiesId = request()->input("cities_id")) {
            $this->request->set("cities_id", array_map('intval',explode(',', $citiesId)));
        }

        if ($regionsId = request()->input("regions_id")) {
            $this->request->set("regions_id", array_map('intval',explode(',', $regionsId)));
        }

        if ($this->has('lat') && $this->has('lng') && $this->has('radius')) {
            $this->request->set('geo', [$this->get('lat'), $this->get('lng'), $this->get('radius')]);
        }
    }

    public function validationData(): array
    {
        return array_merge($this->all(), $this->request->all());
    }

    public function messages()
    {
        return [
            'shifts.in' => 'The value must be one of: one,two,less_five,more_five'
        ];
    }
}
