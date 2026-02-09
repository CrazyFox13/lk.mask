<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Advertiser\AdvertiserRequest;
use App\Models\Advertiser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvertiserController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $advertisers = Advertiser::query();

        if ($search = $request->get('search')) {
            $advertisers = $advertisers->search($search);
        }

        $totalCount = $advertisers->count();

        if ($id = $request->get("field")) {
            $advertisers = $advertisers->orderByRaw("FIELD(id,$id) DESC");
        }

        list($sort, $sortDir) = Paginator::getSorting($request);
        $advertisers = $advertisers->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $advertisers = $advertisers->skip($skip)->take($take);
        }

        $advertisers = $advertisers->withCount(['banners'])->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('advertisers', $advertisers, $totalCount, $pagesCount);
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
     * @param AdvertiserRequest $request
     * @return JsonResponse
     */
    public function store(AdvertiserRequest $request): JsonResponse
    {
        $model = new Advertiser($request->all());
        $model->save();
        return $this->resourceItemResponse('advertiser', $model);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Advertiser $advertiser
     * @return \Illuminate\Http\Response
     */
    public function show(Advertiser $advertiser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Advertiser $advertiser
     * @return \Illuminate\Http\Response
     */
    public function edit(Advertiser $advertiser)
    {
        //
    }

    /**
     * @param AdvertiserRequest $request
     * @param Advertiser $advertiser
     * @return JsonResponse
     */
    public function update(AdvertiserRequest $request, Advertiser $advertiser): JsonResponse
    {
        $advertiser->fill($request->all());
        $advertiser->save();

        return $this->resourceItemResponse('advertiser', $advertiser);
    }

    /**
     * @param Advertiser $advertiser
     * @return JsonResponse
     */
    public function destroy(Advertiser $advertiser): JsonResponse
    {
        $advertiser->delete();
        return $this->emptySuccessResponse();
    }
}
