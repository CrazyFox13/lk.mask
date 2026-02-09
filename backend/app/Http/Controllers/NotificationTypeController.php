<?php

namespace App\Http\Controllers;

use App\Models\NotificationType;
use App\Models\NotificationTypeUser;
use App\Models\OrderFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationTypeController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = auth("sanctum")->user();
        $types = NotificationType::query()
            ->with(['notificationTypeUser' => function ($q) use ($user) {
                $q->where("user_id", $user->id);
            }])
            ->orderByRaw("FIELD(`key`,'vehicle_orders') DESC")
            ->orderBy("id")
            ->get();

        $subscribedVehicles = $user->subscribedVehicles()->select(["vehicle_types.id","vehicle_types.title"])->get();
        $subscribedCities = $user->subscribedCities()->select(["geo_cities.id","geo_cities.name as title"])->get();
        return $this->resourceListResponse('notificationTypes', $types, $types->count(), 1, [
            'subscribedVehicles' => $subscribedVehicles,
            'subscribedCities' => $subscribedCities
        ]);
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
     * @param \App\Models\NotificationType $notificationType
     * @return \Illuminate\Http\Response
     */
    public function show(NotificationType $notificationType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\NotificationType $notificationType
     * @return \Illuminate\Http\Response
     */
    public function edit(NotificationType $notificationType)
    {
        //
    }

    /**
     * @param Request $request
     * @param NotificationType $notificationType
     * @return JsonResponse
     */
    public function update(Request $request, NotificationType $notificationType): JsonResponse
    {
        $request->validate([
            'way' => 'required|in:push,email',
            'active' => 'required|boolean'
        ]);

        $user = auth("sanctum")->user();

        NotificationTypeUser::query()
            ->where("notification_type_id", $notificationType->id)
            ->where("user_id", $user->id)
            ->where("way", $request->get('way'))
            ->update([
                'active' => $request->get('active')
            ]);


        if ($notificationType->key === "filters") {
            if (!$request->get("active")) {
                // disable all filters
                switch ($request->get('way')) {
                    case "email":
                        OrderFilter::query()->where("user_id", $user->id)
                            ->update([
                                'active_email' => false,
                            ]);
                        break;
                    case "push":
                        OrderFilter::query()->where("user_id", $user->id)
                            ->update([
                                'active_push' => false,
                            ]);
                        break;
                }
            }
        }

        return $this->emptySuccessResponse();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\NotificationType $notificationType
     * @return \Illuminate\Http\Response
     */
    public function destroy(NotificationType $notificationType)
    {
        //
    }
}
