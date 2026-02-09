<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use App\Traits\OrderValidator;

class CreateFullOrder extends ApiRequest
{
    use OrderValidator;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $user = auth('sanctum')->user();
        return !is_null($user->email_verified_at);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return array_merge(
            $this->validateVehicles(),
            $this->validateFormQuestions(),
            $this->validateDates(),
            $this->validateAddresses(),
            $this->validateBudget(),
            $this->validateDetails()
        );
    }

    public function messages()
    {
        return $this->orderMessages();
    }
}
