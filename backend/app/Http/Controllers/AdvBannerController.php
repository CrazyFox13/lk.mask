<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\AdvBanner\AdvBannerRequest;
use App\Models\AdvBanner;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class AdvBannerController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $banners = AdvBanner::query();

        if ($search = $request->get('search')) {
            $banners = $banners->search($search);
        }
        if ($advId = $request->get('advertiser_id')) {
            $banners = $banners->whereAdvertiserId($advId);
        }
        if ($placeId = $request->get('adv_place_id')) {
            $banners = $banners->whereAdvPlaceId($placeId);
        }

        $totalCount = $banners->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $banners = $banners->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $banners = $banners->skip($skip)->take($take);
        }

        $banners = $banners->with(['advertiser', 'place', 'vehicleTypes'])->get()->each(function (AdvBanner $advBanner) {
            $advBanner->append('showing');
        });

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('advBanners', $banners, $totalCount, $pagesCount);
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
     * @param AdvBannerRequest $request
     * @return JsonResponse
     */
    public function store(AdvBannerRequest $request): JsonResponse
    {
        $model = new AdvBanner($request->all());
        $model->save();

        $place = $model->place;
        if ($place->with_vehicle_filter && $request->has("vehicle_types_id")) {
            $vehicleIds = $request->get("vehicle_types_id");
            $model->vehicleTypes()->attach($vehicleIds);
        }

        $model->load(['advertiser', 'place', 'vehicleTypes']);

        return $this->resourceItemResponse('advBanner', $model);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\AdvBanner $advBanner
     * @return \Illuminate\Http\Response
     */
    public function show(AdvBanner $advBanner)
    {
        //
    }

    /**
     * Выдача случайной рекламы из запущенных на страницу
     * @param Request $request
     * @return JsonResponse
     */
    public function getRandomByTypes(Request $request): JsonResponse
    {
        $places = array_filter(explode(",", $request->get("places")));
        if (!count($places)) abort(403);

        $banners = [];
        foreach ($places as $place) {
            $banner = AdvBanner::query()->forPlace($place)->active()->running()->withEnabledAdvertiser()->with(['advertiser' => function ($q) {
                $q->select(["id", "name", "inn"]);
            }]);

            if ($request->has("vehicle_types_id")) {
                $vehicleTypes = array_filter(explode(",", $request->get("vehicle_types_id")));
                $banner = $vehicleTypes ? $banner->forTypes($vehicleTypes) : $banner->whereDoesntHave("vehicleTypes");
            }

            $banner = $banner->with(['place'])->inRandomOrder()->first();
            if ($banner) $banner->view();
            $banners[] = $banner;
        }

        return $this->resourceItemResponse('banners', $banners);
    }

    /**
     * Фиксирование клика по баннеру
     * @param AdvBanner $advBanner
     * @return JsonResponse
     */
    public function click(AdvBanner $advBanner): JsonResponse
    {
        $advBanner->click();
        return $this->emptySuccessResponse();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AdvBanner $advBanner
     * @return \Illuminate\Http\Response
     */
    public function edit(AdvBanner $advBanner)
    {
        //
    }

    /**
     * @param AdvBannerRequest $request
     * @param AdvBanner $advBanner
     * @return JsonResponse
     */
    public function update(AdvBannerRequest $request, AdvBanner $advBanner): JsonResponse
    {
        $advBanner->fill($request->all());
        $advBanner->save();

        $place = $advBanner->place;
        if ($place->with_vehicle_filter && $request->has("vehicle_types_id")) {
            $vehicleIds = $request->get("vehicle_types_id");
            $advBanner->vehicleTypes()->sync($vehicleIds);
        } else {
            $advBanner->vehicleTypes()->detach();
        }

        $advBanner->load(['advertiser', 'place', 'vehicleTypes']);

        return $this->resourceItemResponse('advBanner', $advBanner);
    }

    /**
     * @param AdvBanner $advBanner
     * @return JsonResponse
     */
    public function destroy(AdvBanner $advBanner): JsonResponse
    {
        $advBanner->delete();
        return $this->emptySuccessResponse();
    }
}
