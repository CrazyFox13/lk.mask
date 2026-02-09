<?php

namespace App\Traits;

use App\Models\FormQuestion;
use App\Models\Order;
use App\Models\OrderDocument;
use Illuminate\Validation\Rule;

trait OrderValidator
{
    public function orderMessages(): array
    {
        return [
            'documents.*.type.in' => 'The value must be one of: ' . implode(',', OrderDocument::TYPES)
        ];
    }

    /**
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function getRemainQuestions()
    {
        $requestQuestions = request()->get('form_answers');

        if (!request()->get('vehicle_type_id')) {
            return [
                'remainQuestionsCount' => 0,
                'questionsId' => '',
            ];
        }

        $totalQuestions = FormQuestion::query()
            ->where("vehicle_type_id", request()->get('vehicle_type_id'))
            ->get();

        $requiredQuestions = $totalQuestions->where("required", true);

        if (is_array($requestQuestions)) {
            $remainQuestionsCount = $requiredQuestions
                ->whereNotIn("id", array_column($requestQuestions, 'form_question_id'))
                ->count();
        } else {
            $remainQuestionsCount = $requiredQuestions->count();
        }

        $questionsId = $totalQuestions->pluck('id')->implode(",");
        return [
            'remainQuestionsCount' => $remainQuestionsCount,
            'questionsId' => $questionsId,
        ];
    }

    public function validateVehicles(): array
    {
        return [
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
            'vehicles_count' => 'required|integer|min:1',
        ];
    }

    public function validateFormQuestions(): array
    {
        list('remainQuestionsCount' => $remainQuestionsCount, 'questionsId' => $questionsId) = $this->getRemainQuestions();
        return [
            'form_answers' => [$remainQuestionsCount ? 'required' : 'sometimes', 'array', function ($attribute, $value, $fail) use ($remainQuestionsCount) {
                if ($remainQuestionsCount) {
                    $fail("Не все поля анкеты заполнены");
                }
            }],
            'form_answers.*' => ['array'],
            'form_answers.*.form_question_id' => "required|integer|in:$questionsId",
            'form_answers.*.value' => "required",
        ];
    }

    public function validateDates(): array
    {
        return [
            "start_date" => "required|date|before_or_equal:finish_date",
            "finish_date" => "required|date|after_or_equal:start_date",
        ];
    }

    public function validateAddresses(): array
    {
        return [
            "addresses" => "required|array|min:1",
            "addresses.*.lat" => 'required|numeric|between:-90,90',
            "addresses.*.lng" => 'required|numeric|between:-180,180',
            "addresses.*.address" => "required|max:2000",
            "addresses.*.fias_id" => "required|string|exists:geo_cities,fias_id",
        ];
    }

    public function validateBudget(): array
    {
        return [
            'payment_unit_id' => ['sometimes', 'nullable', 'integer'],
            'amount_account_vat' => ['sometimes', 'nullable', "numeric"],
            'amount_account' => ['sometimes', 'nullable', "numeric"],
            'amount_cash' => ['sometimes', 'nullable', "numeric"],
            'amount_by_agreement' => ['sometimes', 'nullable', 'boolean'],
            'no_haggling' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    public function validateDetails(): array
    {
        return [
            "description" => "required|string|min:15|max:5000",
            "communication_way" => "sometimes|string|in:" . implode(",", Order::COMMUNICATION_WAYS),
            "documents" => "sometimes|array",
            "documents.*.type" => "required|in:" . implode(',', OrderDocument::TYPES),
            "documents.*.url" => "required|url",
        ];
    }
}
