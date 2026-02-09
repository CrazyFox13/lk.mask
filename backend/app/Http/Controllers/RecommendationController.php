<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Recommendation\Create;
use App\Http\Requests\Recommendation\RecommendationList;
use App\Http\Requests\Recommendation\Update;
use App\Models\Company;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RecommendationController extends Controller
{
    // ID типа компании "Заказчик"
    const CUSTOMER_COMPANY_TYPE_ID = 3;

    /**
     * Проверяет, является ли компания пользователя типом "Заказчик"
     * Если да, то блокирует доступ к операциям с рекомендациями
     *
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function checkCustomerCompanyType()
    {
        $user = auth("sanctum")->user();
        
        if ($user && $user->company_id) {
            $company = Company::find($user->company_id);
            
            if ($company && $company->company_type_id === self::CUSTOMER_COMPANY_TYPE_ID) {
                abort(403, 'Доступ к рекомендациям закрыт для компаний типа "Заказчик"');
            }
        }
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(RecommendationList $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $recommendations = Recommendation::query();

        switch ($request->get('filter')) {
            case "to_company":
                $recommendations = $recommendations->where('company_id', $request->get('company_id'));
                break;

            case "to_me":
                $recommendations = $recommendations->toMe();
                break;
            case "to_user":
                $recommendations = $recommendations->where("target_user_id", $request->get("user_id"));
                break;

            case "by_me":
                $recommendations = $recommendations->where("author_user_id", auth()->id());
                break;
        }

        $user = auth()->user();

        if ($user->isUser()) {
            $recommendations = $recommendations->notDeleted()->visible();
        } else {
            switch ($request->get('status')) {
                case "draft":
                    $recommendations = $recommendations->draft();
                    break;
                case "approved":
                    $recommendations = $recommendations->approved();
                    break;
                case "viewed":
                    $recommendations = $recommendations->viewed();
                    break;
                case "canceled":
                    $recommendations = $recommendations->canceled();
                    break;
                case "deleted":
                    $recommendations = $recommendations->deleted();
                    break;
            }
        }

        if ($request->has('search')) {
            $recommendations = $recommendations->search($request->get('search'));
        }

        if ($request->get('show_deleted')) {
            $recommendations = $recommendations->withTrashed();
        }

        $totalCount = $recommendations->count();
        $recommendations = $recommendations->with(['author' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'author.company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }, 'company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports']);
        }, 'targetUser' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'targetUser.company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }]);

        list($sort, $sortDir) = Paginator::getSorting($request);
        $recommendations = $recommendations->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $recommendations = $recommendations->skip($skip)->take($take);
        }

        $recommendations = $recommendations->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);

        if ($request->get("view_all")) {
            Recommendation::targetViewMany($recommendations->pluck("id")->toArray());
        }

        return $this->resourceListResponse('recommendations', $recommendations, $totalCount, $pagesCount);
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
     * @param Create $request
     * @return JsonResponse
     */
    public function store(Create $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $recommendation = new Recommendation([
            'author_user_id' => auth()->id(),
        ]);
        $recommendation->fill($request->all([
            'company_id', 'target_user_id', 'text'
        ]));

        $recommendation->save();

        return $this->resourceItemResponse('recommendation', $recommendation);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Http\Response
     */
    public function show(Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $recommendation->load(['author' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'author.company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }, 'company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports']);
        }, 'targetUser' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'targetUser.company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }]);

        if ($recommendation->isToMe()) {
            $recommendation->targetView();
        }

        return $this->resourceItemResponse('recommendation', $recommendation);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Recommendation $recommendation
     * @return \Illuminate\Http\Response
     */
    public function edit(Recommendation $recommendation)
    {
        //
    }

    /**
     * @param Request $request
     * @param Recommendation $recommendation
     * @return JsonResponse
     */
    public function update(Update $request, Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $recommendation->fill($request->all([
            'text'
        ]));
        $recommendation->save();

        return $this->resourceItemResponse('recommendation', $recommendation);
    }

    /**
     * @param Recommendation $recommendation
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Recommendation $recommendation, Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('delete', $recommendation)) abort(403);

        if ($request->get("force")) {
            $recommendation->forceDelete();
        } else {
            $recommendation->delete();
        }

        return $this->emptySuccessResponse();
    }

    public function draft(Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('moderate', $recommendation)) abort(403);

        $recommendation->makeDraft();
        return $this->emptySuccessResponse();
    }

    public function view(Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('moderate', $recommendation)) abort(403);

        $recommendation->viewed();
        return $this->emptySuccessResponse();
    }

    public function approve(Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('moderate', $recommendation)) abort(403);

        $recommendation->approve();
        return $this->emptySuccessResponse();
    }

    public function cancel(Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('moderate', $recommendation)) abort(403);

        $recommendation->cancel();
        return $this->emptySuccessResponse();
    }

    public function archive(Recommendation $recommendation): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $recommendation)) abort(403);

        $recommendation->archive();
        return $this->emptySuccessResponse();
    }

    /* DEPRECATED todo: DELETE AFTER APP UPDATE */
    public function newRecommendationsCount(): JsonResponse
    {
        $count = Recommendation::query()
            ->toMe()->new()->count();

        return $this->resourceItemResponse('new_recommendations_count', $count);
    }
}
