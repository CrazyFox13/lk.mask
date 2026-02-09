<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderFilter\OrderFilterStore;
use App\Http\Requests\OrderFilter\OrderFilterUpdate;
use App\Models\NotificationType;
use App\Models\NotificationTypeUser;
use App\Models\OrderFilter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderFilterController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth("sanctum")->user();
        if ($user->isModerator() || $user->isAdmin()) {
            if ($request->has("user_id")) {
                $user = User::query()->find($request->get("user_id"));
            }
        }
        $filters = OrderFilter::query()
            ->where("user_id", $user->id)
            ->orderBy("created_at", "desc")
            ->get();
        $totalCount = $filters->count();
        return $this->resourceListResponse('orderFilters', $filters, $totalCount, 1);
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
     * @param OrderFilterStore $request
     * @return JsonResponse
     */
    public function store(OrderFilterStore $request): JsonResponse
    {
        $user = auth("sanctum")->user();
        $orderFilter = new OrderFilter($request->all());
        $orderFilter->user_id = $user->id;
        if ($user->isAdmin() || $user->isModerator()) {
            if ($uid = $request->get("user_id")) {
                $orderFilter->user_id = $uid;
            }
        }
        $orderFilter->save();

        NotificationType::enableFiltersForAuthUser($orderFilter->active_email, $orderFilter->active_push);

        return $this->resourceItemResponse('orderFilter', $orderFilter);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\OrderFilter $orderFilter
     * @return \Illuminate\Http\Response
     */
    public function show(OrderFilter $orderFilter)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\OrderFilter $orderFilter
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderFilter $orderFilter)
    {
        //
    }

    /**
     * @param OrderFilterUpdate $request
     * @param OrderFilter $orderFilter
     * @return JsonResponse
     */
    public function update(OrderFilterUpdate $request, OrderFilter $orderFilter): JsonResponse
    {
        $orderFilter = $orderFilter->fill($request->all());
        $orderFilter->save();

        NotificationType::enableFiltersForAuthUser($orderFilter->active_email, $orderFilter->active_push);

        return $this->resourceItemResponse('orderFilter', $orderFilter);
    }

    /**
     * @param OrderFilter $orderFilter
     * @return JsonResponse
     */
    public function destroy(OrderFilter $orderFilter): JsonResponse
    {
        if (Gate::denies('delete', $orderFilter)) abort(403);
        $orderFilter->delete();
        return $this->emptySuccessResponse();
    }
}
