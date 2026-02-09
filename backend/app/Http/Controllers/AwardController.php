<?php

namespace App\Http\Controllers;

use App\Http\Requests\Award\AwardRequest;
use App\Models\Award;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AwardController extends Controller
{
    public function __construct()
    {
        $this->middleware('moderator', ['except' => 'index']);
    }

    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $models = Award::query();
        if ($companyId = $request->get("company_id")) {
            $models = $models->whereHas('companies', function ($q) use ($companyId) {
                $q->where("companies.id", $companyId);
            });
        }
        $models = $models->withCount('companies')->get();
        return $this->resourceListResponse('awards', $models, $models->count(), 1);
    }

    /**
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * @param AwardRequest $request
     * @return JsonResponse
     */
    public function store(AwardRequest $request): JsonResponse
    {
        $award = new Award($request->all());
        $award->save();

        $award->loadCount('companies');

        return $this->resourceItemResponse('award', $award);
    }

    /**
     * @param Award $award
     * @return void
     */
    public function show(Award $award)
    {
        //
    }

    /**
     * @param Award $award
     * @return void
     */
    public function edit(Award $award)
    {
        //
    }

    /**
     * @param AwardRequest $request
     * @param Award $award
     * @return JsonResponse
     */
    public function update(AwardRequest $request, Award $award): JsonResponse
    {
        $award->fill($request->all());
        $award->save();

        $award->loadCount('companies');

        return $this->resourceItemResponse('award', $award);
    }

    /**
     * @param Award $award
     * @return JsonResponse
     */
    public function destroy(Award $award): JsonResponse
    {
        $award->delete();

        return $this->emptySuccessResponse();
    }
}
