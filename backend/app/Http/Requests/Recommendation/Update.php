<?php

namespace App\Http\Requests\Recommendation;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Facades\Gate;

class Update extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update', $this->route('recommendation'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'text' => 'required|min:15|max:5000'
        ];
    }
}
