<?php

namespace App\Http\Controllers;

use App\Models\VehicleGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VehicleGroupController extends Controller
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
        $groups = VehicleGroup::query();

        if ($request->has('with_orders')) {
            $groups = $groups->has("types.orders")->with(['types' => function ($q) {
                $q->whereHas("orders", function ($q) {
                    $q->active();
                })->withCount(['orders' => function ($q) {
                    $q->active();
                }]);
            }]);
        } else {
            $groups = $groups->with(['types' => function ($q) {
                $q->withCount(['orders' => function ($q) {
                    $q->active();
                }]);
            }]);
        }

        $groups = $groups->withCount(['orders' => function ($q) {
            $q->active();
        }, 'types'])->with('category')->get();
        return response()->json([
            'status' => 'success',
            'vehicleGroups' => $groups,
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
    public function store(Request $request): JsonResponse
    {
        if (Gate::forUser(auth('sanctum')->user())->denies("create", VehicleGroup::class)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|unique:vehicle_groups,title',
            'logo' => 'sometimes|url',
            'image' => 'required_if:show_in_menu,true|url',
            'color' => 'required_if:show_in_menu,true',
            'show_in_menu' => 'boolean',
        ]);
        $group = new VehicleGroup($request->all());
        $group->save();

        $group->loadCount('types', 'orders');
        return $this->resourceItemResponse('vehicleGroup', $group);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     * @return JsonResponse
     */
    public function show(VehicleGroup $vehicleGroup): JsonResponse
    {
        return $this->resourceItemResponse('vehicleGroup', $vehicleGroup);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VehicleGroup $vehicleGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleGroup $vehicleGroup)
    {
        //
    }

    /**
     * @param Request $request
     * @param VehicleGroup $vehicleGroup
     * @return JsonResponse
     */
    public function update(Request $request, VehicleGroup $vehicleGroup): JsonResponse
    {
        if (Gate::forUser(auth('sanctum')->user())->denies("update", $vehicleGroup)) {
            abort(403);
        }

        $request->validate([
            'title' => ['required', Rule::unique('vehicle_groups', 'title')->ignore($vehicleGroup->id)],
            'logo' => 'required|url',
            'image' => 'required_if:show_in_menu,true|url',
            'color' => 'required_if:show_in_menu,true',
            'show_in_menu' => 'boolean',
        ]);
        $vehicleGroup->fill($request->all());
        $vehicleGroup->vehicle_category_id = $request->get("vehicle_category_id") ?: null;
        $vehicleGroup->save();
        return $this->resourceItemResponse('vehicleGroup', $vehicleGroup);
    }

    /**
     * @param VehicleGroup $vehicleGroup
     * @return JsonResponse
     */
    public function destroy(VehicleGroup $vehicleGroup): JsonResponse
    {
        if (Gate::forUser(auth('sanctum')->user())->denies("delete", $vehicleGroup)) {
            abort(403);
        }

        $vehicleGroup->delete();
        return $this->emptySuccessResponse();
    }
}
