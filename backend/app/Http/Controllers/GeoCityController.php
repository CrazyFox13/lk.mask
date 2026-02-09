<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\GeoCity;
use Dadata\DadataClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoCityController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $cities = GeoCity::query()->with("region");

        list($page, $skip, $take) = Paginator::get($request);


        if ($request->has('search')) {
            $search = $request->get('search');
            $cities = $cities->search($search);
        }

        if ($cityId = $request->get('sort_by_field')) {
            $cities = $cities->orderByRaw("FIELD(geo_cities.id, $cityId) DESC");
        }

        $totalCount = $cities->count();

        $cities = $cities->orderByRaw('FIELD(geo_cities.geo_region_id, 78, 77) DESC')
            ->orderBy("geo_region_id");

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
     * @param \App\Models\GeoCity $geoCity
     * @return \Illuminate\Http\Response
     */
    public function show(GeoCity $geoCity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\GeoCity $geoCity
     * @return \Illuminate\Http\Response
     */
    public function edit(GeoCity $geoCity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\GeoCity $geoCity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GeoCity $geoCity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\GeoCity $geoCity
     * @return \Illuminate\Http\Response
     */
    public function destroy(GeoCity $geoCity)
    {
        //
    }

    public function findByIp(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180'
        ]);

        list('lat' => $lat, 'lng' => $lng) = $request->all();

        $haversine = "(6371 * acos(cos(radians($lat))
                    * cos(radians(`lat`))
                    * cos(radians(`lng`)
                    - radians($lng))
                    + sin(radians($lat))
                    * sin(radians(`lat`))))";

        $city = GeoCity::query()
            ->with('region')
            #->selectRaw("id, name, geo_region_id, ROUND({$haversine},2) AS distance")
            ->orderByRaw("ROUND({$haversine},2)")
            ->first();

        return $this->resourceItemResponse('city', $city);
    }
}
