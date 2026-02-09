<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Report\ConclusionRequest;
use App\Http\Requests\Report\Create;
use App\Http\Requests\Report\ReportList;
use App\Http\Requests\Report\Update;
use App\Models\Company;
use App\Models\Order;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ReportList $request): JsonResponse
    {
        $reports = Report::query();

        $filter = $request->get('filter');

        switch ($filter) {
            case "not_closed":
                $reports = $reports->byMe()->notClosed();
                break;
            case "closed":
                $reports = $reports->byMe()->closed();
                break;
            case"to_me":
                $reports = $reports->toMe();
                break;
            case "to_company":
                $reports = $reports->where("company_id", $request->get('company_id'));
                break;
            case "to_user":
                $reports = $reports->where("target_user_id", $request->get('user_id'));
                break;

        }

        if ($request->get('show_deleted')) {
            $reports = $reports->withTrashed();
        }

        $user = auth()->user();
        if ($user->isUser()) {
            $reports = $reports->visible();

            if ($request->has('statuses')) {
                $statuses = explode(",", $request->get("statuses"));
                if (count($statuses) > 0) {
                    $reports = $reports->whereIn("status", $statuses);
                }
            }
        } else {
            switch ($request->get('status')) {
                case "draft":
                    $reports = $reports->draft();
                    break;
                case "active":
                    $reports = $reports->active();
                    break;
                case "referee":
                    $reports = $reports->referee();
                    break;
                case "resolved":
                    $reports = $reports->resolved();
                    break;
                case "canceled":
                    $reports = $reports->canceled();
                    break;
                case "not_canceled":
                    $reports = $reports->notCanceled();

            }
        }

        if ($request->has('search')) {
            $reports = $reports->search($request->get('search'));
        }

        $totalCount = $reports->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $reports = $reports->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $reports = $reports->skip($skip)->take($take);
        }

        $reports = $reports->with(['author' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'author.company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }, 'targetUser' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports']);
        }, 'type', 'documents'])
            // todo: with new messagesCount
            ->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('reports', $reports, $totalCount, $pagesCount);
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
        $report = new Report(['author_user_id' => auth()->id()]);
        $report->fill($request->all());
        $report->save();

        if ($request->has('documents')) {
            $report->setDocuments($request->get('documents'));
        }

        // instant moderate?
        $report->moderate();

        $report->load('documents');

        return $this->resourceItemResponse('report', $report);
    }

    /**
     * @param Report $report
     * @return JsonResponse
     */
    public function show(Report $report): JsonResponse
    {
        $report->load(['type', 'documents', 'author' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'targetUser' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'author.company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }, 'company' => function ($q) {
            $q->withTrashed()->withCount(['approvedRecommendations', 'activeReports'])->published();
        }, 'chat' => function ($q) {
            $q->leftJoin('chat_user', function ($q) {
                $q->on('chat_user.chat_id', '=', 'chats.id')
                    ->where('chat_user.user_id', '=', auth()->id());
            })->selectRaw('chats.*,chat_user.user_id,chat_user.chat_id')->withCount("newMessages");
        }]);
        return $this->resourceItemResponse('report', $report);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Report $report
     * @return \Illuminate\Http\Response
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * @param Update $request
     * @param Report $report
     * @return JsonResponse
     */
    public function update(Update $request, Report $report): JsonResponse
    {
        $report->fill($request->all([
            'report_type_id', 'amount', 'text'
        ]));
        $report->save();

        if ($request->has('documents')) {
            $report->setDocuments($request->get('documents'));
        }

        $report->load('documents');

        return $this->resourceItemResponse('report', $report);

    }

    /**
     * @param Report $report
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Report $report, Request $request): JsonResponse
    {
        if (Gate::denies('delete', $report)) abort(403);

        if ($request->get("force")) {
            $report->forceDelete();
        } else {
            $report->delete();
        }

        return $this->emptySuccessResponse();
    }

    public function draft(Report $report): JsonResponse
    {
        if (Gate::denies('update', $report)) abort(403);

        $report->makeDraft();
        return $this->emptySuccessResponse();
    }

    public function moderate(Report $report): JsonResponse
    {
        if (Gate::denies('moderate', $report)) abort(403);

        $report->moderate();
        return $this->emptySuccessResponse();
    }

    public function referee(Report $report): JsonResponse
    {
        if (Gate::denies('referee', $report)) abort(403);

        $report->referee();
        return $this->emptySuccessResponse();
    }

    public function confirm(Report $report): JsonResponse
    {
        if (Gate::denies('moderate', $report)) abort(403);

        $report->confirm();
        return $this->emptySuccessResponse();
    }

    public function reject(Report $report): JsonResponse
    {
        if (Gate::denies('moderate', $report)) abort(403);

        $report->reject();
        return $this->emptySuccessResponse();
    }

    public function resolve(Report $report): JsonResponse
    {
        if (Gate::denies('update', $report)) abort(403);

        $report->resolve();
        return $this->emptySuccessResponse();
    }

    public function cancel(Report $report): JsonResponse
    {
        if (Gate::denies('update', $report)) abort(403);

        $report->cancel();
        return $this->emptySuccessResponse();
    }

    public function setConclusion(Report $report, ConclusionRequest $request): JsonResponse
    {
        $report->referee_conclusion = $request->get("referee_conclusion");
        $report->save();
        return $this->emptySuccessResponse();
    }
}
