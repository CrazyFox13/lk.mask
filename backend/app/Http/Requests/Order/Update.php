<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\ApiRequest;
use App\Models\FormQuestion;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use function Symfony\Component\String\u;

class Update extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('update',$this->route('order'));
    }

    /**
     * @return array
     */
    public function rules()
    {
        $order = $this->route('order');

        $totalQuestions = FormQuestion::query()
            ->where("vehicle_type_id", $order->vehicle_type_id)
            ->get();

        $questionsId = $totalQuestions->pluck('id')->implode(",");

        return [
            'form_answers' => ['sometimes', 'array'],
            'form_answers.*' => ['array'],
            'form_answers.*.form_question_id' => "integer|in:$questionsId",
            'form_answers.*.value' => "required",
            'vehicles_count' => 'required|integer|min:1',
        ];
    }
}
