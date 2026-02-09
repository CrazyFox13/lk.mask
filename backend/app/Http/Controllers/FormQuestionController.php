<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormQuestion\FormQuestionRequest;
use App\Models\FormQuestion;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormQuestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'index']);
    }

    /**
     * @param VehicleGroup $group
     * @param VehicleType $type
     * @return JsonResponse
     */
    public function index(VehicleGroup $vehicleGroup, VehicleType $vehicleType): JsonResponse
    {
        if ($vehicleGroup->id !== $vehicleType->vehicle_group_id) {
            abort(404);
        }

        $questions = FormQuestion::query()
            ->where("vehicle_type_id", $vehicleType->id)
            ->orderBy("order", "asc");

        $totalCount = $questions->count();

        $questions = $questions->get();

        return $this->resourceListResponse('formQuestions', $questions, $totalCount, 1);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @param VehicleGroup $vehicleGroup
     * @param VehicleType $vehicleType
     * @param Request $request
     * @return JsonResponse
     */
    public function store(VehicleGroup $vehicleGroup, VehicleType $vehicleType, FormQuestionRequest $request): JsonResponse
    {
        $formQuestion = new FormQuestion(array_merge($request->all('type', 'label', 'required', 'options'), [
            'vehicle_type_id' => $vehicleType->id,
        ]));
        $formQuestion->save();
        return $this->resourceItemResponse('formQuestion', $formQuestion);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FormQuestion $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function show(FormQuestion $formQuestion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FormQuestion $formQuestion
     * @return \Illuminate\Http\Response
     */
    public function edit(FormQuestion $formQuestion)
    {
        //
    }

    /**
     * @param FormQuestionRequest $request
     * @param VehicleGroup $vehicleGroup
     * @param VehicleType $vehicleType
     * @param FormQuestion $formQuestion
     * @return JsonResponse
     */
    public function update(FormQuestionRequest $request, VehicleGroup $vehicleGroup, VehicleType $vehicleType, FormQuestion $formQuestion): JsonResponse
    {
        $formQuestion->fill($request->all('type', 'label', 'required', 'options'));
        $formQuestion->save();
        return $this->resourceItemResponse('formQuestion', $formQuestion);
    }

    /**
     * @param FormQuestion $formQuestion
     * @return JsonResponse
     */
    public function destroy(VehicleGroup $vehicleGroup, VehicleType $vehicleType, FormQuestion $formQuestion): JsonResponse
    {
        $formQuestion->delete();
        return $this->emptySuccessResponse();
    }

    public function order(VehicleGroup $vehicleGroup, VehicleType $vehicleType, Request $request): JsonResponse
    {
        $totalQuestionsCount = $vehicleType->questions()->count();
        $request->validate([
            'questions' => "required|array|size:$totalQuestionsCount",
            'questions.*.order' => "numeric|min:0|max:$totalQuestionsCount",
            'questions.*.question_id' => 'integer|exists:form_questions,id'
        ]);

        DB::beginTransaction();

        $questions = $vehicleType->questions()->get();
        foreach ($questions as $question) {
            $item = array_values(array_filter($request->get('questions'), function ($i) use ($question) {
                return $question->id == $i['question_id'];
            }))[0];
            if (!$item) continue;

            $question->order = $item['order'];
            $question->save();
        }

        DB::commit();

        return $this->emptySuccessResponse();
    }
}
