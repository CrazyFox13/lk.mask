<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Claim\CreateClaimRequest;
use App\Http\Requests\Claim\UpdateClaimRequest;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClaimController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $claims = Claim::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $claims = $claims->where(function ($q) use ($search) {
                $q->where("text", "like", "%$search%")
                    ->orWhereHas("author", fn($q) => $q->search($search));
            });
        }

        $user = auth()->user();

        if ($user->isUser()) {
            $claims = $claims->approved();
        } else {
            switch ($request->get('status')) {
                case "draft":
                    $claims = $claims->draft();
                    break;
                case "viewed":
                    $claims = $claims->viewed();
                    break;
                case "approved":
                    $claims = $claims->approved();
                    break;
                case "canceled":
                    $claims = $claims->canceled();
                    break;
            }
        }

        if ($request->has('order_id')) {
            $claims = $claims->where('order_id', $request->get('order_id'));
        }

        if ($request->get('show_deleted')) {
            $claims = $claims->withTrashed();
        }

        $totalCount = $claims->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $claims = $claims->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        $claims = $claims->skip($skip)->take($take);

        $claims = $claims->with(['order', 'author' => function ($q) {
            $q->select(User::PUBLIC_FIELDS);
        }, "documents"])->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);
        return $this->resourceListResponse('claims', $claims, $totalCount, $pagesCount);
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateClaimRequest $request): JsonResponse
    {
        $claim = new Claim($request->all());
        $claim->user_id = auth()->id();
        $claim->save();

        if ($request->has('documents')) {
            $claim->setDocuments($request->get('documents'));
        }
        $claim->load('documents');
        return $this->resourceItemResponse('claim', $claim);
    }

    /**
     * @param Claim $claim
     * @return JsonResponse
     */
    public function show(Claim $claim): JsonResponse
    {
        $claim->load(['order', 'documents', 'author' => fn($q) => $q->select(User::PUBLIC_FIELDS)]);
        return $this->resourceItemResponse('claim', $claim);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Claim $claim
     * @return \Illuminate\Http\Response
     */
    public function edit(Claim $claim)
    {
        //
    }

    /**
     * @param UpdateClaimRequest $request
     * @param Claim $claim
     * @return JsonResponse
     */
    public function update(UpdateClaimRequest $request, Claim $claim): JsonResponse
    {
        $claim->fill($request->all());
        $claim->save();

        if ($request->has('documents')) {
            $claim->setDocuments($request->get('documents'));
        }
        $claim->load('documents');

        return $this->resourceItemResponse('claim', $claim);
    }

    /**
     * @param Claim $claim
     * @return JsonResponse
     */
    public function destroy(Claim $claim, Request $request): JsonResponse
    {
        if (Gate::denies('delete', $claim)) abort(403);

        if ($request->get("force")) {
            $claim->forceDelete();
        } else {
            $claim->delete();
        }

        return $this->emptySuccessResponse();
    }

    public function draft(Claim $claim): JsonResponse
    {
        if (Gate::denies('moderate', $claim)) abort(403);

        $claim->makeDraft();
        return $this->emptySuccessResponse();
    }

    public function view(Claim $claim): JsonResponse
    {
        if (Gate::denies('moderate', $claim)) abort(403);

        $claim->viewed();
        return $this->emptySuccessResponse();
    }

    public function approve(Claim $claim): JsonResponse
    {
        if (Gate::denies('moderate', $claim)) abort(403);

        $claim->approve();
        return $this->emptySuccessResponse();
    }

    public function cancel(Claim $claim): JsonResponse
    {
        if (Gate::denies('moderate', $claim)) abort(403);

        $claim->cancel();
        return $this->emptySuccessResponse();
    }
}
