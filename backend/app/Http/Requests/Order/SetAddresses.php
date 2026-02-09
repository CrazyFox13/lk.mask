<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use App\Models\User;
use App\Traits\OrderValidator;
use App\Traits\ValidateOnly;
use Illuminate\Support\Facades\Gate;

class SetAddresses extends ApiRequest
{
    use ValidateOnly;
    use OrderValidator;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->validateOnly()) return true;
        return Gate::allows('update',$this->route('order'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->validateAddresses();
    }
}
