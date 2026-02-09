<?php

namespace App\Http\Requests\OrderOffer;

use App\Http\Requests\ApiRequest;
use App\Models\OrderOffer;
use Illuminate\Support\Facades\Gate;

class OrderOfferRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $order = $this->route('order');
        $offer = $this->route('order_offer');

        if (!$offer) {
            return Gate::allows("create", [OrderOffer::class, $order]);
        }
        return Gate::allows("update", $offer);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "amount_account_vat" => "",
            "amount_account" => "",
            "amount_cash" => "",
            "date_start" => "required",
            "comment" => "",
        ];
    }
}
