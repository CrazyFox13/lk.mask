<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use App\Models\Order;
use App\Models\User;
use App\Traits\OrderValidator;
use App\Traits\ValidateOnly;
use Illuminate\Support\Facades\Gate;

class SetBudget extends ApiRequest
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
        return Gate::allows('update', $this->route('order'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->validateBudget();
    }

    public function messages()
    {
        return $this->orderMessages();
    }
}
