<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentUnit\PaymentUnitRequest;
use App\Models\PaymentUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentUnitController extends Controller
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
        $units = PaymentUnit::query();
        if ($vehicleTypeId = $request->get('vehicle_type_id')) {
            $units = $units->whereHas('vehicleTypes', function ($q) use ($vehicleTypeId) {
                $q->where("vehicle_types.id", $vehicleTypeId);
            });
        }
        $units = $units->withCount('vehicleTypes')->get();
        return $this->resourceListResponse('paymentUnits', $units, $units->count(), 1);
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
     * @param PaymentUnitRequest $request
     * @return JsonResponse
     */
    public function store(PaymentUnitRequest $request): JsonResponse
    {
        $unit = new PaymentUnit($request->all());
        $unit->save();

        return $this->resourceItemResponse('paymentUnit', $unit);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\PaymentUnit $paymentUnit
     * @return \Illuminate\Http\Response
     */
    public function show(PaymentUnit $paymentUnit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PaymentUnit $paymentUnit
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentUnit $paymentUnit)
    {
        //
    }

    /**
     * @param PaymentUnitRequest $request
     * @param PaymentUnit $paymentUnit
     * @return JsonResponse
     */
    public function update(PaymentUnitRequest $request, PaymentUnit $paymentUnit): JsonResponse
    {
        $paymentUnit->fill($request->all());
        $paymentUnit->save();

        return $this->resourceItemResponse('paymentUnit', $paymentUnit);
    }

    /**
     * @param PaymentUnit $paymentUnit
     * @return JsonResponse
     */
    public function destroy(PaymentUnit $paymentUnit): JsonResponse
    {
        $paymentUnit->delete();
        return $this->emptySuccessResponse();
    }
}
