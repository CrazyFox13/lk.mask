<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\City;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'index']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Region $region, Request $request): JsonResponse
    {
        $cities = City::query()->where('region_id', $region->id);

        list($page, $skip, $take) = Paginator::get($request);


        if ($request->has('search')) {
            $search = $request->get('search');
            $cities = $cities->where("title", "like", "%$search%");
        }

        if ($request->has('sort_by_field')) {
            $cityId = $request->get('sort_by_field');
            $cities = $cities->orderByRaw("FIELD(cities.id, $cityId) DESC");
        }

        if ($request->has("with_orders")) {
            $cities = $cities->whereHas("orderAddresses", function ($q) {
                $q->active();
            });
        }

        $totalCount = $cities->count();

        $cities = $cities->orderBy('title');
        if ($take >= 0) {
            $cities = $cities->skip($skip)->take($take);
        }
        $cities = $cities->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);
        return $this->resourceListResponse('cities', $cities, $totalCount, $pagesCount);
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
     * @param Region $region
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Region $region, Request $request): JsonResponse
    {
        $this->validate($request, [
            'title' => 'required|unique:cities,id',
        ]);

        $city = new City(['title' => $request->title, 'region_id' => $region->id]);
        $city->save();

        return $this->resourceItemResponse('city', $city);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\City $city
     * @return \Illuminate\Http\Response
     */
    public function show(City $city)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\City $city
     * @return \Illuminate\Http\Response
     */
    public function edit(City $city)
    {
        //
    }

    /**
     * @param Request $request
     * @param Region $region
     * @param City $city
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Region $region, City $city): JsonResponse
    {
        $this->validate($request, [
            'title' => 'required|unique:cities,id',
        ]);

        $city->fill(['title' => $request->title]);
        $city->save();

        return $this->resourceItemResponse('city', $city);
    }

    /**
     * @param Region $region
     * @param City $city
     * @return JsonResponse
     */
    public function destroy(Region $region, City $city): JsonResponse
    {
        $city->delete();
        return $this->emptySuccessResponse();
    }
}
