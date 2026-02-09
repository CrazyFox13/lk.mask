<?php

namespace App\Http\Requests\FormQuestion;

use App\Http\Requests\ApiRequest;
use App\Models\FormQuestion;
use Illuminate\Validation\Rule;

class FormQuestionRequest extends ApiRequest
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
        $needOptions = in_array(request()->get('type'), FormQuestion::getTypeWithOptionsKeys());
        $needLabel = !in_array(request()->get('type'),FormQuestion::getTypesWithDefaultLabel());
        return [
            'type' => 'required|in:' . implode(',', FormQuestion::getTypeKeys()),
            'label' => [Rule::requiredIf($needLabel),'max:255'],
            'required' => 'sometimes|boolean',
            'options' => [
                Rule::requiredIf($needOptions),
                $needOptions ? 'array' : '', $needOptions ? 'min:1' : ''
            ]
        ];
    }
}
