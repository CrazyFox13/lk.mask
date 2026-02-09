<?php

namespace App\Http\Requests\SubscribeGeo;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class SubscribeRequest extends ApiRequest
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
            'geo_cities_id' => 'array',
            'geo_cities_id.*' => 'integer|exists:geo_cities,id',
            'geo_regions_id' => 'array',
            'geo_regions_id.*' => 'integer|exists:geo_regions,id',
        ];
    }
}
