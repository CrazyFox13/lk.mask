<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\GeoRegion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoRegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'index']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $regions = GeoRegion::query();

        list($page, $skip, $take) = Paginator::get($request);

        if ($request->has('search')) {
            $search = $request->get('search');
            $regions = $regions->where(function ($q) use ($search) {
                $q->where("name", "like", "%$search%");
            });
            $regions = $regions->with(['cities'])->withCount('cities');
        }

        if ($request->has('city')) {
            $city = $request->get('city');
            $regions = $regions->search($city)->with(['cities' => function ($q) use ($city) {
                $q->search($city);
            }])->withCount(['cities' => function ($q) use ($city) {
                $q->search($city);
            }]);
        }

        if ($request->has('sort_by_field')) {
            $regionId = $request->get('sort_by_field');
            $regions = $regions->orderByRaw("FIELD(regions.id, $regionId) DESC");
        }

        $regions = $regions->orderByRaw('FIELD(name, "Ленинградская", "Московская","Санкт-Петербург", "Москва") DESC')
            ->orderBy("name");

        $totalCount = $regions->count();

        if ($take > 0) {
            $regions = $regions->skip($skip)->take($take);
        }

        $regions = $regions->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);
        return $this->resourceListResponse('regions', $regions, $totalCount, $pagesCount);
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
     * @param \App\Models\GeoRegion $geoRegion
     * @return \Illuminate\Http\Response
     */
    public function show(GeoRegion $geoRegion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\GeoRegion $geoRegion
     * @return \Illuminate\Http\Response
     */
    public function edit(GeoRegion $geoRegion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\GeoRegion $geoRegion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GeoRegion $geoRegion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\GeoRegion $geoRegion
     * @return \Illuminate\Http\Response
     */
    public function destroy(GeoRegion $geoRegion)
    {
        //
    }
}
