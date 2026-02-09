<?php

namespace App\Http\Requests\OrderFilter;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Facades\Gate;

class OrderFilterUpdate extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->route("order_filter"));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'active_push' => 'required|boolean',
            'active_email' => 'required|boolean'
        ];
    }
}
