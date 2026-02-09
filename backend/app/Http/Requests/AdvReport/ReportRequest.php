<?php

namespace App\Http\Requests\AdvReport;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends ApiRequest
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
            'group' => 'required|in:day,month,advertiser,banner',
            'date_from' => 'required|date|before:date_to',
            'date_to' => 'required|date|after:date_from',
        ];
    }
}
