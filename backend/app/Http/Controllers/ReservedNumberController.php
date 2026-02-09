<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\ReservedNumber\CreateRequest;
use App\Http\Requests\ReservedNumber\UpdateRequest;
use App\Models\ReservedNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservedNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse
    {
        $numbers = ReservedNumber::query();

        if ($search = $request->get('search')) {
            $numbers = $numbers->where("number", "like", "%$search%");
        }

        if ($request->has("status")) {
            switch ($request->get("status")) {
                case "busy":
                    $numbers = $numbers->busy();
                    break;
                case "free":
                    $numbers = $numbers->free();
                    break;
            }
        }

        if ($request->has("for_company")) {
            $companyId = (int)$request->get("for_company");
            $numbers = $numbers->where(function ($q) use ($companyId) {
                $q->free()
                    ->orWhere("company_id", $companyId);
            });
        }

        $totalCount = $numbers->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $numbers = $numbers->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take >= 1) {
            $numbers = $numbers->skip($skip)->take($take);
        }

        $numbers = $numbers->with("company")->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('reservedNumbers', $numbers, $totalCount, $pagesCount);
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
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $number = new ReservedNumber($request->all());
        $number->save();

        return $this->resourceItemResponse('reservedNumber', $number);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ReservedNumber $reservedNumber
     * @return \Illuminate\Http\Response
     */
    public function show(ReservedNumber $reservedNumber)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ReservedNumber $reservedNumber
     * @return \Illuminate\Http\Response
     */
    public function edit(ReservedNumber $reservedNumber)
    {
        //
    }

    /**
     * @param UpdateRequest $request
     * @param ReservedNumber $reservedNumber
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, ReservedNumber $reservedNumber): JsonResponse
    {
        $reservedNumber->fill($request->all());
        $reservedNumber->save();

        return $this->resourceItemResponse('reservedNumber', $reservedNumber);
    }

    /**
     * @param ReservedNumber $reservedNumber
     * @return JsonResponse
     */
    public function destroy(ReservedNumber $reservedNumber): JsonResponse
    {
        $reservedNumber->delete();
        return $this->emptySuccessResponse();
    }
}
