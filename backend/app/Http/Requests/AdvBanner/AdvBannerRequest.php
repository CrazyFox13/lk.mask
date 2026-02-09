<?php

namespace App\Http\Requests\AdvBanner;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class AdvBannerRequest extends ApiRequest
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
            "advertiser_id" => "required|exists:advertisers,id",
            "adv_place_id" => "required|exists:adv_places,id",
            "is_active" => "boolean",
            "title" => "required|max:255",
            "tooltip" => "string|max:5000|nullable",
            "start_date" => "required|date|before:end_date",
            "end_date" => "required|date|after:start_date",
            "img_url" => "required|url",
            "endpoint_url" => "required|url",
            "comment" => "string|max:5000|nullable",
            "vehicle_types_id"=>"array|nullable",
            "vehicle_types_id.*"=>"integer|exists:vehicle_types,id"
        ];
    }
}
