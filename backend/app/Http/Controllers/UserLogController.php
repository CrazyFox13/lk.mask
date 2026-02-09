<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    /**
     * @param User $customer
     * @param Request $request
     * @return JsonResponse
     */
    public function index(User $customer, Request $request): JsonResponse
    {
        list($page, $skip, $take) = Paginator::get($request);

        $totalCount = UserLog::query()
            ->where('user_id', $customer->id)
            ->count();

        $users = UserLog::query()
            ->where('user_id', $customer->id)
            ->orderBy('id', 'desc')
            ->skip($skip)
            ->take($take)
            ->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('userLogs', $users, $totalCount, $pagesCount);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\UserLog $userLog
     * @return \Illuminate\Http\Response
     */
    public function show(UserLog $userLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\UserLog $userLog
     * @return \Illuminate\Http\Response
     */
    public function edit(UserLog $userLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\UserLog $userLog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, UserLog $userLog)
    {
        //
    }

    /**
     * @param User $customer
     * @param UserLog $userLog
     * @return JsonResponse
     */
    public function destroy(User $customer, UserLog $log): JsonResponse
    {
        $log->delete();
        return $this->emptySuccessResponse();
    }
}
