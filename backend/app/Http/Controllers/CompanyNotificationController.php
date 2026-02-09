<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\CompanyNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyNotificationController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        list($page, $skip, $take) = Paginator::get($request);

        $notifications = CompanyNotification::query()->visible();

        $totalCount = $notifications->count();

        $notifications = $notifications->orderBy('created_at', 'desc')
            ->skip($skip)
            ->take($take)
            ->get();

        #CompanyNotification::view($notifications->pluck('id')->toArray());

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('companyNotifications', $notifications, $totalCount, $pagesCount);
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
     * @param \App\Models\CompanyNotification $companyNotification
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyNotification $companyNotification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\CompanyNotification $companyNotification
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyNotification $companyNotification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\CompanyNotification $companyNotification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CompanyNotification $companyNotification)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\CompanyNotification $companyNotification
     * @return \Illuminate\Http\Response
     */
    public function destroy(CompanyNotification $companyNotification)
    {
        //
    }

    /* DEPRECATED todo: DELETE AFTER APP UPDATE */
    public function newNotificationsCount(): JsonResponse
    {
        $count = CompanyNotification::query()->visible()->new()->count();
        return $this->resourceItemResponse('new_notifications_count', $count);
    }

    public function readOne(CompanyNotification $companyNotification): JsonResponse
    {
        $user = auth()->user();
        if ($companyNotification->user_id !== $user->id) abort(403);
        $companyNotification->viewed_at = Carbon::now();
        $companyNotification->save();
        return $this->emptySuccessResponse();
    }

    public function readAll(): JsonResponse
    {
        CompanyNotification::query()
            ->where("user_id", auth()->id())
            ->whereNull('viewed_at')
            ->update([
                'viewed_at' => Carbon::now()
            ]);

        return $this->emptySuccessResponse();
    }
}
