<?php

namespace App\Http\Requests\Message;

use App\Http\Requests\ApiRequest;
use App\Models\Message;
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
        $message = $this->route('message');
        return Gate::allows('update',$message);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'text' => 'required_without:file_url|string|max:5000',
            'file_url'=>'required_without:text|url|max:255',
            'file_type'=>'required_with:file_url|string|in:'.implode(',',Message::FILE_TYPES),
        ];
    }

    public function messages()
    {
        return [
            'file_type.in' => 'The value must be one of: ' . implode(',', Message::FILE_TYPES)
        ];
    }
}
