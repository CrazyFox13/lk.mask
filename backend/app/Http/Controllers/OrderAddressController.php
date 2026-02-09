<?php

namespace App\Http\Controllers;

use App\Models\OrderAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderAddressController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\OrderAddress $orderAddress
     * @return \Illuminate\Http\Response
     */
    public function show(OrderAddress $orderAddress)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\OrderAddress $orderAddress
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderAddress $orderAddress)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\OrderAddress $orderAddress
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OrderAddress $orderAddress)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\OrderAddress $orderAddress
     * @return \Illuminate\Http\Response
     */
    public function destroy(OrderAddress $orderAddress)
    {
        //
    }

    public function addressFromString(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required'
        ]);

        $token = config('services.dadata.key');
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->suggest("address", $request->get('query'));
        return $this->resourceItemResponse('result', $result);
    }

    public function addressFromGeo(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required',
            'lng' => 'required'
        ]);

        $token = config('services.dadata.key');
        $dadata = new \Dadata\DadataClient($token, null);
        $result = $dadata->geolocate("address", $request->get("lat"), $request->get("lng"));
        return $this->resourceItemResponse('result', $result);
    }

}
