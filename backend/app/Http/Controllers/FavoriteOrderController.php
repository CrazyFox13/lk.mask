<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\FavoriteOrder;
use App\Models\Order;
use App\Models\OrderFilter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteOrderController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()->visible()->filtered($request)->favorite();
        $totalCount = $orders->count();

        switch ($request->get('sort_by')) {
            case "created_at_asc":
                $orders->orderBy("created_at", "asc");
                break;
            default:
                $orders->orderBy("created_at", "desc");
        }

        $orders = $orders->with(['startAddress', 'startAddress.city', 'startAddress.region', 'company' => function ($q) {
            $q->published()->withCount('approvedRecommendations', 'activeReports');
        }, 'company.boss' => function ($q) {
            $q->select(User::PUBLIC_FIELDS)->withCount('approvedRecommendations', 'activeReports');
        }, 'user' => function ($q) {
            $q->select(User::PUBLIC_FIELDS)->withCount('approvedRecommendations', 'activeReports');
        }]);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) $orders = $orders->skip($skip)->take($take);
        $orders = $orders->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);

        $orders = $orders->map(function (Order $order) {
            $order->is_favorite = true;
            return $order;
        });

        return $this->resourceListResponse('orders', $orders, $totalCount, $pagesCount,[
            'orderFiltersExists' => OrderFilter::query()->where("user_id", auth('sanctum')->id())->exists()
        ]);
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
     * @param \App\Models\FavoriteOrder $favoriteOrder
     * @return \Illuminate\Http\Response
     */
    public function show(FavoriteOrder $favoriteOrder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FavoriteOrder $favoriteOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(FavoriteOrder $favoriteOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FavoriteOrder $favoriteOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FavoriteOrder $favoriteOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FavoriteOrder $favoriteOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(FavoriteOrder $favoriteOrder)
    {
        //
    }
}
