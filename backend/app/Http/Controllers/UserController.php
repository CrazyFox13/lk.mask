<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Employee\Update;
use App\Mail\EmailConfirmation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()->user()->withCount('activeOrders');

        if ($request->has('search')) {
            $users = $users->search($request->get('search'));
        }

        if ($request->has('company_id')) {
            $users = $users->where("company_id", $request->get('company_id'));
        }

        switch ($request->get('status')) {
            case 'without_company':
                $users = $users->whereNull("company_id");
                break;
            case 'boss':
                $users = $users->boss();
                break;
            case 'staff':
                $users = $users->staff();
                break;
        }

        switch ($request->get("personal_notifications")) {
            case "none":
                $users->whereDoesntHave("notificationTypes", function ($q) {
                    $q->personal()->where("notification_type_user.active", "=", 1);
                });
                break;
            case "email":
                $users->whereHas("notificationTypes", function ($q) {
                    $q->personal()->where("notification_type_user.way", "=", "email")->where("notification_type_user.active", "=", 1);
                });
                break;
            case "push":
                $users->whereHas("notificationTypes", function ($q) {
                    $q->personal()->where("notification_type_user.way", "=", "push")->where("notification_type_user.active", "=", 1);
                });
                break;
        }

        if ($request->has("vehicle_types_id")) {
            $vehList = explode(",", $request->get('vehicle_types_id'));
            if (in_array("-1", $vehList)) {
                $users = $users->withoutVehicleSubscribes();
            } else {
                $users = $users->subscribedOnVehicles($vehList);
            }
        }

        if ($request->get('show_deleted')) {
            $users = $users->withTrashed();
        }


        if ($request->get("unconfirmed_phone")) {
            $users = $users->whereNull("phone_verified_at");
        }
        if ($request->get("without_email")) {
            $users = $users->whereNull("email");
        } elseif ($request->get("unconfirmed_email")) {
            $users = $users->whereNotNull("email")->whereNull("email_verified_at");
        }

        $totalCount = $users->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $users = $users->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $users = $users->skip($skip)->take($take);
        }

        $users = $users->with(['city', 'company', 'notificationTypes' => function ($q) {
            $q->personal();
        }])->withCount(['subscribedVehicles', 'subscribedCities'])->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('users', $users, $totalCount, $pagesCount);
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
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $plainPassword = Str::random(12);

        $user = new User($request->all('name', 'surname', 'email', 'phone', 'company_id', 'company_role'));
        $user->password = Hash::make($plainPassword);
        $user->type = User::USER_TYPES[0];
        $user->save();

        return $this->resourceItemResponse('user', $user, [
            'plainPassword' => $plainPassword,
        ]);
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function show(User $user): JsonResponse
    {
        $user->load('company', 'city', 'subscribedVehicles', 'subscribedCities');
        $user->loadCount('approvedRecommendations', 'activeReports', 'activeOrders');
        $user->append('is_favorite');
        return $this->resourceItemResponse('user', $user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * @param Update $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Update $request, User $user): JsonResponse
    {
        $user->fill($request->all([
            'name', 'surname', 'email', 'phone', 'city_id', 'region_id', 'geo_city_id',
            'email_verified_at', 'phone_verified_at'
        ]));
        $user->save();


        if (!$user->auto_subscribe_city) {
            if ($user->subscribedCities()->count() === 0) {
                $user->subscribedCities()->attach($request->geo_city_id);
                $user->auto_subscribe_city = true;
                $user->save();
            }
        }

        return $this->resourceItemResponse('user', $user);
    }

    public function setComment(Request $request, User $user): JsonResponse
    {
        $user->comment = $request->get("comment");
        $user->save();
        return $this->emptySuccessResponse();
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function destroy(User $user, Request $request): JsonResponse
    {
        if (Gate::denies('delete', $user)) {
            abort(403);
        }

        $force = $request->get("force");

        if ($force) {
            $user->forceDelete();
        } else {
            $user->delete();
        }
        return $this->emptySuccessResponse();
    }

    /**
     * @param User $user
     * @return JsonResponse
     */
    public function favorite(User $user): JsonResponse
    {
        $inFavorite = $user->inFavorite() ? $user->removeFromFavorite() : $user->addToFavorite();
        return $this->resourceItemResponse('in_favorite', $inFavorite);
    }

    public function subscribeCities(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'geo_cities_id' => "array",
            'geo_cities_id.*' => "integer|exists:geo_cities,id",
        ]);

        $user->subscribedCities()->sync($request->get("geo_cities_id"));

        return $this->resourceListResponse('subscribed', $user->subscribedCities, $user->subscribedCities->count(), 1);
    }

    public function subscribeVehicles(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_types_id' => "array",
            'vehicle_types_id.*' => "integer|exists:geo_cities,id",
        ]);

        $user->subscribedVehicles()->sync($request->get('vehicle_types_id'));

        return $this->resourceListResponse('subscribed', $user->subscribedVehicles, $user->subscribedVehicles->count(), 1);
    }

    public function sendEmailConfirmation(User $user, Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', "max:255", "email", Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->email = $request->get("email");
        $user->email_code = random_int(1000, 9999);
        $user->email_code_sent_at = Carbon::now();
        $user->save();

        try {
            Mail::to($user)->send(new EmailConfirmation($user));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ошибка при отправке e-mail',
                'errors' => $e->getMessage(),
            ], 400);
        }
        return $this->resourceItemResponse('user', $user->only(["id", "email"]));
    }
}
