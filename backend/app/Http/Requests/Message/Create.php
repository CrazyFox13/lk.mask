<?php

namespace App\Http\Requests\Message;

use App\Http\Requests\ApiRequest;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Create extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $chat = $this->route('chat');
        return Gate::allows('create', [Message::class, $chat]);
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
            'file_url' => 'required_without:text|url|max:255',
            'file_type' => 'required_with:file_url|string|in:' . implode(',', Message::FILE_TYPES),
            'reply_message_id' => 'sometimes|integer|exists:messages,id',
        ];
    }

    public function messages()
    {
        return [
            'file_type.in' => 'The value must be one of: ' . implode(',', Message::FILE_TYPES)
        ];
    }
}
