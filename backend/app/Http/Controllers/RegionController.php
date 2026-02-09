<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Kernel;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'index']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $regions = Region::query();

        list($page, $skip, $take) = Paginator::get($request);

        if ($request->has('search')) {
            $search = $request->get('search');
            $regions = $regions->where("title", "like", "%$search%");
        }

        if ($request->get('with_orders')) {
            $regions = $regions->whereHas('orderAddresses', function ($q) {
                $q->active();
            });
        }

        if ($request->has('sort_by_field')) {
            $regionId = $request->get('sort_by_field');
            $regions = $regions->orderByRaw("FIELD(regions.id, $regionId) DESC");
        }

        $regions = $regions->orderByRaw('FIELD(title, "Ленинградская область", "Санкт-Петербург город",  "Московская область","Москва город") DESC' )
        ->orderBy("title");

        $totalCount = $regions->count();

        if($take>0) {
            $regions = $regions->skip($skip)->take($take);
        }

        $regions = $regions->with(['cities' => function ($q) use ($request) {
            $q->withCount('orderAddresses');
            if ($request->get('with_orders')) {
                $q->whereHas('orderAddresses', function ($q) {
                    $q->active();
                });
            }
        }])->withCount('orderAddresses', 'cities')->get();
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
     * @param Request $request
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'title' => 'required|unique:regions,id'
        ]);

        $region = new Region(['title' => $request->title]);
        $region->save();

        return $this->resourceItemResponse('region', $region);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Region $region
     * @return \Illuminate\Http\Response
     */
    public function show(Region $region)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Region $region
     * @return \Illuminate\Http\Response
     */
    public function edit(Region $region)
    {
        //
    }

    /**
     * @param Request $request
     * @param Region $region
     * @return JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, Region $region):JsonResponse
    {
        $this->validate($request, [
            'title' => 'required|unique:regions,id'
        ]);

        $region->fill(['title' => $request->title]);
        $region->save();

        return $this->resourceItemResponse('region', $region);
    }

    /**
     * @param Region $region
     * @return JsonResponse
     */
    public function destroy(Region $region):JsonResponse
    {
        $region->delete();
        return $this->emptySuccessResponse();
    }
}
