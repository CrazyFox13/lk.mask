<?php

namespace App\Http\Requests\Page;

use App\Http\Requests\ApiRequest;

class PageRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
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
            'title' => "required|min:3|max:255",
            'type' => 'required|in:faq,material',
            'path' => 'required',
            'hidden' => 'boolean',
            'content' => 'required'
        ];
    }
}
