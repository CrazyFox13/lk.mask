<?php

namespace App\Http\Controllers;

use App\Models\VehicleCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VehicleCategoryController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = VehicleCategory::query()->with(["groups", "groups.types"])->get();
        return $this->resourceListResponse("vehicleCategories", $categories, $categories->count(), 1);
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
        if (Gate::forUser(auth("sanctum")->user())->denies("create", VehicleCategory::class)) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|unique:vehicle_categories,title',
            'image' => 'required_if:show_in_menu,true|url',
            'color' => 'required_if:show_in_menu,true',
            'show_in_menu' => 'boolean',
        ]);

        $category = new VehicleCategory($request->all());
        $category->save();

        return $this->resourceItemResponse("vehicleCategory", $category);
    }

    /**
     * @param VehicleCategory $vehicleCategory
     * @return JsonResponse
     */
    public function show(VehicleCategory $vehicleCategory):JsonResponse
    {
        $vehicleCategory->load("groups");
        return $this->resourceItemResponse("vehicleCategory",$vehicleCategory);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VehicleCategory $vehicleCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(VehicleCategory $vehicleCategory)
    {
        //
    }

    /**
     * @param Request $request
     * @param VehicleCategory $vehicleCategory
     * @return JsonResponse
     */
    public function update(Request $request, VehicleCategory $vehicleCategory): JsonResponse
    {
        if (Gate::forUser(auth("sanctum")->user())->denies("update", $vehicleCategory)) {
            abort(403);
        }

        $request->validate([
            'title' => ['required', Rule::unique('vehicle_categories', 'title')->ignore($vehicleCategory->id)],
            'image' => 'required_if:show_in_menu,true|url',
            'color' => 'required_if:show_in_menu,true',
            'show_in_menu' => 'boolean',
        ]);

        $vehicleCategory->fill($request->all());
        $vehicleCategory->save();

        return $this->resourceItemResponse("vehicleCategory", $vehicleCategory);
    }

    /**
     * @param VehicleCategory $vehicleCategory
     * @return JsonResponse
     */
    public function destroy(VehicleCategory $vehicleCategory): JsonResponse
    {
        if (Gate::forUser(auth("sanctum")->user())->denies("delete", $vehicleCategory)) {
            abort(403);
        }

        $vehicleCategory->delete();
        return $this->emptySuccessResponse();
    }
}
