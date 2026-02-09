<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\PushNotification\PushNotificationRequest;
use App\Models\PushNotification;
use App\Models\PushNotificationSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = PushNotification::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $notifications = $notifications->where(function ($q) use ($search) {
                $q->where("subject", "like", "%$search%")
                    ->orWhere("text", "like", "%$search%");
            });
        }

        switch ($request->get('status')) {
            case "draft":
                $notifications = $notifications->draft();
                break;
            case "process":
                $notifications = $notifications->inProccess();
                break;
            case "sent":
                $notifications = $notifications->sent();
                break;
        }

        switch ($request->get("type")) {
            case "push":
                $notifications = $notifications->push();
                break;
            case "email":
                $notifications = $notifications->email();
                break;
        }

        $totalCount = $notifications->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $notifications = $notifications->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $notifications = $notifications->skip($skip)->take($take);
        }

        $notifications = $notifications->withCount(['users'])->with("schedules")->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);
        return $this->resourceListResponse('pushNotifications', $notifications, $totalCount, $pagesCount);
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
     * @param PushNotificationRequest $request
     * @return JsonResponse
     */
    public function store(PushNotificationRequest $request): JsonResponse
    {
        $push = new PushNotification($request->all('subject', 'text', 'action', 'type'));
        $push->status = PushNotification::STATUSES[0];
        $push->save();

        if ($request->has("schedules")) {
            $times = array_unique(array_column($request->get("schedules"), "time"));
            foreach ($times as $time) {
                $sameTimeExists = $push->schedules()->where("time", "=", $time)->exists();
                if (!$sameTimeExists) {
                    $schedule = new PushNotificationSchedule([
                        'time' => $time,
                        "push_notification_id" => $push->id,
                        "sent" => false,
                    ]);
                    $schedule->save();
                }

            }
        }

        $push->loadCount(['users'])->load("schedules");
        return $this->resourceItemResponse('pushNotification', $push);
    }

    /**
     * Display the specified resource.
     *
     */
    public function show(PushNotification $pushNotification, Request $request): JsonResponse
    {
        $pushNotification->loadCount(['users']);
        $pushNotification->load(['schedules']);
        return $this->resourceItemResponse('pushNotification', $pushNotification);
    }

    public function setMaterial(PushNotification $pushNotification, Request $request): JsonResponse
    {
        $pushNotification->material = $request->get("material");
        $pushNotification->save();
        return $this->emptySuccessResponse();
    }

    public function getMaterial(PushNotification $pushNotification): JsonResponse
    {
        return $this->resourceItemResponse("material", $pushNotification->material);
    }

    public function users(PushNotification $pushNotification, Request $request): JsonResponse
    {
        list($page, $skip, $take) = Paginator::get($request);
        $totalCount = $pushNotification->users()->count();
        $users = $pushNotification->users();

        if ($schedule_id = $request->get("schedule_id")) {
            $users = $users->wherePivot("schedule_id", "=", $schedule_id);
        }

        $users = $users
            ->orderBy("sent_at")
            ->orderBy("users.id")
            ->skip($skip)->take($take)
            ->with(['company', 'device'])
            ->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);
        return $this->resourceListResponse("users", $users, $totalCount, $pagesCount);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PushNotification $pushNotification
     * @return \Illuminate\Http\Response
     */
    public function edit(PushNotification $pushNotification)
    {
        //
    }

    /**
     * @param PushNotificationRequest $request
     * @param PushNotification $pushNotification
     * @return JsonResponse
     */
    public function update(PushNotificationRequest $request, PushNotification $pushNotification): JsonResponse
    {
        if (!$pushNotification->editable) abort(401);
        $pushNotification->fill($request->all('subject', 'text', 'action'));
        $pushNotification->save();

        if (!$request->get("schedules")) {
            $pushNotification->schedules()->delete();
        } else {
            $needTimes = array_unique(array_column($request->get("schedules"), "time"));
            $pushNotification->schedules()
                ->whereNotIn("time", $needTimes)
                ->delete();

            $oldTimes = $pushNotification->schedules()->pluck("time")->map(fn($i) => Carbon::parse($i)->format("Y-m-d H:i"))->toArray();
            $newTimes = array_diff($needTimes, $oldTimes);
            foreach ($newTimes as $time) {
                $schedule = new PushNotificationSchedule([
                    'time' => $time,
                    "push_notification_id" => $pushNotification->id,
                    "sent" => false,
                ]);
                $schedule->save();
            }
        }


        $pushNotification->load("schedules")->loadCount(['users']);

        return $this->resourceItemResponse('pushNotification', $pushNotification);
    }

    /**
     * @param PushNotification $pushNotification
     * @return JsonResponse
     */
    public function destroy(PushNotification $pushNotification): JsonResponse
    {
        if (!$pushNotification->editable) abort(401);
        $pushNotification->delete();
        return $this->emptySuccessResponse();
    }

    /**
     * @param PushNotification $pushNotification
     * @param Request $request
     * @return JsonResponse
     */
    public function availableReceivers(PushNotification $pushNotification, Request $request): JsonResponse
    {
        $users = User::query()->whereDoesntHave('pushNotifications', function ($q) use ($pushNotification) {
            $q->where("push_notifications.id", $pushNotification->id);
        })->user();

        if ($pushNotification->isPush()) {
            $users = $users->has('device');
        } elseif ($pushNotification->isEmail()) {
            $users = $users->withConfirmedEmail();
        }

        $users = $users->filtered($request->all());

        $totalCount = $users->count();

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $users = $users->skip($skip)->take($take);
        }

        $users = $users->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('users', $users, $totalCount, $pagesCount);
    }

    /**
     * @param PushNotification $pushNotification
     * @param Request $request
     * @return JsonResponse
     */
    public function attachAll(PushNotification $pushNotification, Request $request): JsonResponse
    {
        $users = User::query()->whereDoesntHave('pushNotifications', function ($q) use ($pushNotification) {
            $q->where("push_notifications.id", $pushNotification->id);
        })->user();

        if ($pushNotification->isPush()) {
            $users = $users->has('device');
        } elseif ($pushNotification->isEmail()) {
            $users = $users->withConfirmedEmail();
        }

        $filter = $request->get('filter');

        $users = $users->filtered($filter);

        $pushNotification->users()->syncWithoutDetaching($users->pluck("id"));

        return $this->emptySuccessResponse();
    }

    /**
     * @param PushNotification $pushNotification
     * @param User $user
     * @return JsonResponse
     */
    public function attachUser(PushNotification $pushNotification, User $user): JsonResponse
    {
        $pushNotification->users()->syncWithoutDetaching($user->id);
        return $this->emptySuccessResponse();
    }

    /**
     * @param PushNotification $pushNotification
     * @param Request $request
     * @return JsonResponse
     */
    public function attachedReceivers(PushNotification $pushNotification, Request $request): JsonResponse
    {
        $users = User::query()->whereHas('pushNotifications', function ($q) use ($pushNotification) {
            $q->where("push_notifications.id", $pushNotification->id);
        })->user();

        $totalCount = $users->count();

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $users = $users->skip($skip)->take($take);
        }

        $users = $users->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('users', $users, $totalCount, $pagesCount);
    }

    /**
     * @param PushNotification $pushNotification
     * @return JsonResponse
     */
    public function detachAll(PushNotification $pushNotification): JsonResponse
    {
        $pushNotification->users()->detach();
        return $this->emptySuccessResponse();
    }

    /**
     * @param PushNotification $pushNotification
     * @param User $user
     * @return JsonResponse
     */
    public function detachUser(PushNotification $pushNotification, User $user): JsonResponse
    {
        $pushNotification->users()->detach($user->id);
        return $this->emptySuccessResponse();
    }

    /**
     * @param PushNotification $pushNotification
     * @return JsonResponse
     */
    public function send(PushNotification $pushNotification): JsonResponse
    {
        $scheduled = $pushNotification->schedules()->exists();
        $status = $scheduled ? PushNotification::STATUSES[1] : PushNotification::STATUSES[2];
        $pushNotification->status = $status;
        $pushNotification->save();

        if ($scheduled) {
            $receivers = $pushNotification->users()->pluck("users.id");
            foreach ($pushNotification->schedules()->get() as $schedule) {
                $pushNotification->users()->attach($receivers, [
                    "sent_at" => null,
                    "schedule_id" => $schedule->id,
                ]);
            }
            $pushNotification->users()->wherePivotNull("schedule_id")->detach();
        }

        return $this->resourceItemResponse('pushNotification', $pushNotification);
    }

    public function pause(PushNotification $pushNotification): JsonResponse
    {
        $pushNotification->status = PushNotification::STATUSES[4];
        $pushNotification->save();
        return $this->resourceItemResponse('pushNotification', $pushNotification);
    }

    public function resume(PushNotification $pushNotification): JsonResponse
    {
        $pushNotification->status = PushNotification::STATUSES[2];
        $pushNotification->save();
        return $this->resourceItemResponse('pushNotification', $pushNotification);
    }

    public function cancel(PushNotification $pushNotification): JsonResponse
    {
        $pushNotification->status = PushNotification::STATUSES[0];
        $pushNotification->save();
        return $this->resourceItemResponse('pushNotification', $pushNotification);
    }

    public function copy(PushNotification $pushNotification, Request $request): JsonResponse
    {
        $newNotification = new PushNotification($pushNotification->toArray());
        $newNotification->status = PushNotification::STATUSES[0];
        $newNotification->save();

        if ($request->get("ignore_sent")) {
            $uids = $pushNotification->users()->wherePivotNull("sent_at")->pluck("users.id");
            $newNotification->users()->attach($uids);
        } else {
            $newNotification->users()->attach($pushNotification->users()->pluck("users.id"));
        }

        $newNotification->loadCount("users")->load("schedules");
        return $this->resourceItemResponse("pushNotification", $newNotification);
    }
}
