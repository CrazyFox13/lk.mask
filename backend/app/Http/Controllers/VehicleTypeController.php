<?php

namespace App\Http\Controllers;

use App\Models\VehicleGroup;
use App\Models\VehicleType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VehicleTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'index']);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     * @param Request $request
     * @return JsonResponse
     */
    public function index(VehicleGroup $vehicleGroup, Request $request): JsonResponse
    {
        $types = VehicleType::query()->with('group')->withCount(['orders' => function ($q) {
            $q->active();
        }])->where("vehicle_group_id", $vehicleGroup->id);

        if ($request->has('with_orders')) {
            $types = $types->whereHas('orders', function ($q) {
                $q->active();
            });
        }

        $types = $types->get();
        return response()->json([
            'status' => 'success',
            'vehicleTypes' => $types,
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
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request, VehicleGroup $vehicleGroup): JsonResponse
    {
        if (Gate::forUser(auth('sanctum')->user())->denies("create", VehicleType::class)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|unique:vehicle_types,title',
            'payment_units_id' => 'array',
            'payment_units_id.*' => 'integer|exists:payment_units,id',
            'image' => 'required_if:show_in_menu,true|url',
            'color' => 'required_if:show_in_menu,true',
            'show_in_menu' => 'boolean',
            #  'logo' => 'required|url'
        ]);
        $type = new VehicleType($request->all());
        $type->vehicle_group_id = $vehicleGroup->id;
        $type->save();

        $type->paymentUnits()->sync($request->get("payment_units_id"));

        $type->loadCount('orders');
        return $this->resourceItemResponse('vehicleType', $type);
    }

    /**
     * @param VehicleType $vehicleType
     * @return JsonResponse
     */
    public function show(VehicleGroup $vehicleGroup, VehicleType $vehicleType): JsonResponse
    {
        $vehicleType->load('group');
        $vehicleType->append('payment_units_id');
        return $this->resourceItemResponse('vehicleType', $vehicleType);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VehicleType $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleType $vehicleType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VehicleType $vehicleType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VehicleGroup $vehicleGroup, VehicleType $vehicleType): JsonResponse
    {
        if (Gate::forUser(auth('sanctum')->user())->denies("update", $vehicleType)) {
            abort(403);
        }

        $request->validate([
            'title' => ['required', Rule::unique('vehicle_types', 'title')->ignore($vehicleType->id)],
            'payment_units_id' => 'array',
            'payment_units_id.*' => 'integer|exists:payment_units,id',
            'image' => 'required_if:show_in_menu,true|url',
            'color' => 'required_if:show_in_menu,true',
            'show_in_menu' => 'boolean',
            #  'logo' => 'sometimes|url'
        ]);
        $vehicleType->fill($request->all());
        $vehicleType->save();

        $vehicleType->paymentUnits()->sync($request->get("payment_units_id"));
        return $this->resourceItemResponse('vehicleType', $vehicleType);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     * @param VehicleType $vehicleType
     * @return JsonResponse
     */
    public function destroy(VehicleGroup $vehicleGroup, VehicleType $vehicleType): JsonResponse
    {
        if (Gate::forUser(auth('sanctum')->user())->denies("delete", $vehicleType)) {
            abort(403);
        }

        $vehicleType->delete();
        return $this->emptySuccessResponse();
    }
}
