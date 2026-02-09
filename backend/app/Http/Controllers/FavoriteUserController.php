<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\FavoriteUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteUserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $users = User::query()->favorite();

        if ($request->has('search')) {
            $users = $users->search($request->get('search'));
        }

        if ($request->has('cities_id')) {
            $users = $users->whereIn('city_id', explode(',', $request->get('cities_id')));
        }

        if ($request->has('rating')) {
            $users = $users->where("rating", ">=", $request->get('rating'));
        }

        if ($request->has('vehicle_types_id')) {
            $types = explode(',', $request->get('vehicle_types_id'));
            $users = $users->whereHas('company', function ($q) use ($types) {
                $q->withVehicles($types);
            });
        }

        if ($request->has('company_types_id')) {
            $companyTypes = explode(',', $request->get('company_types_id'));
            $users = $users->whereHas("company", function ($q) use ($companyTypes) {
                $q->whereIn("company_type_id", $companyTypes);
            });
        }

        $totalCount = $users->count();

        switch ($request->get('sort_by')) {
            case "created_at_desc":
                $users = $users->orderBy("created_at", "desc");
                break;
            case "created_at_asc":
                $users = $users->orderBy("created_at", "asc");
                break;
            case "rating_desc":
                $users = $users->orderBy("rating", "desc");
                break;
            case "rating_asc":
                $users = $users->orderBy("rating", "asc");
                break;
            default:
                // admin panel sorting
                list($sort, $sortDir) = Paginator::getSorting($request);
                $users = $users->orderBy($sort, $sortDir);
        }

        $users = $users->with(['city', 'company' => function ($q) {
            $q->published()->withCount('approvedRecommendations', 'activeReports');
        }, 'company.type'])->withCount('approvedRecommendations', 'activeReports');

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) $users = $users->skip($skip)->take($take);
        $users = $users->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);

        $users = $users->map(function (User $user) {
            $user->is_favorite = true;
            return $user;
        });

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
     * @param \App\Models\FavoriteUser $favoriteUser
     * @return \Illuminate\Http\Response
     */
    public function show(FavoriteUser $favoriteUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FavoriteUser $favoriteUser
     * @return \Illuminate\Http\Response
     */
    public function edit(FavoriteUser $favoriteUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FavoriteUser $favoriteUser
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FavoriteUser $favoriteUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FavoriteUser $favoriteUser
     * @return \Illuminate\Http\Response
     */
    public function destroy(FavoriteUser $favoriteUser)
    {
        //
    }
}
