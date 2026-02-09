<?php

namespace App\Http\Requests\Upload;

use Illuminate\Foundation\Http\FormRequest;

class SimpleUpload extends FormRequest
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
            'file'=>'required|file|max:5000'
        ];
    }

    public function messages()
    {
        return [
            'file.uploaded'=>"The file must be less 5 Mb or use chunk upload"
        ];
    }
}
