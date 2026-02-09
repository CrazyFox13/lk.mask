<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeGeo\SubscribeRequest;
use App\Models\GeoCity;
use App\Models\SubscribedGeo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscribedGeoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(SubscribeRequest $request): JsonResponse
    {
        $user = auth("sanctum")->user();
        $citiesId = $request->get("geo_cities_id") ?: [];
        if ($regionsId = $request->get("geo_regions_id")) {
            $regionCities = GeoCity::query()->whereIn("geo_region_id", $regionsId)->pluck("id")->toArray();
            $citiesId = array_merge($citiesId, $regionCities);
        }
        $user->subscribedCities()->sync($citiesId);
        return $this->emptySuccessResponse();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\SubscribedGeo $subscribedGeo
     * @return \Illuminate\Http\Response
     */
    public function show(SubscribedGeo $subscribedGeo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\SubscribedGeo $subscribedGeo
     * @return \Illuminate\Http\Response
     */
    public function edit(SubscribedGeo $subscribedGeo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\SubscribedGeo $subscribedGeo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubscribedGeo $subscribedGeo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\SubscribedGeo $subscribedGeo
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubscribedGeo $subscribedGeo)
    {
        //
    }
}
