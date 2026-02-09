<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscribeVehicle\SubscribeRequest;
use App\Models\SubscribedVehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscribedVehicleController extends Controller
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
     * @param SubscribeRequest $request
     * @return JsonResponse
     */
    public function store(SubscribeRequest $request): JsonResponse
    {
        $user = auth("sanctum")->user();
        $user->subscribedVehicles()->sync($request->get("vehicle_types_id"));
        return $this->emptySuccessResponse();
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\SubscribedVehicle $subscribedVehicle
     * @return \Illuminate\Http\Response
     */
    public function show(SubscribedVehicle $subscribedVehicle)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\SubscribedVehicle $subscribedVehicle
     * @return \Illuminate\Http\Response
     */
    public function edit(SubscribedVehicle $subscribedVehicle)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\SubscribedVehicle $subscribedVehicle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SubscribedVehicle $subscribedVehicle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\SubscribedVehicle $subscribedVehicle
     * @return \Illuminate\Http\Response
     */
    public function destroy(SubscribedVehicle $subscribedVehicle)
    {
        //
    }
}
