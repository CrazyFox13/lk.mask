<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportType\ReportTypeRequest;
use App\Models\ReportType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => 'index']);
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $types = ReportType::all();
        return $this->resourceListResponse('reportTypes', $types, $types->count(), 1);
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
     * @param ReportTypeRequest $request
     * @return JsonResponse
     */
    public function store(ReportTypeRequest $request): JsonResponse
    {
        $type = new ReportType($request->all('title'));
        $type->save();
        return $this->resourceItemResponse('reportType', $type);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ReportType $reportType
     * @return \Illuminate\Http\Response
     */
    public function show(ReportType $reportType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ReportType $reportType
     * @return \Illuminate\Http\Response
     */
    public function edit(ReportType $reportType)
    {
        //
    }

    /**
     * @param ReportTypeRequest $request
     * @param ReportType $reportType
     * @return JsonResponse
     */
    public function update(ReportTypeRequest $request, ReportType $reportType):JsonResponse
    {
        $reportType->fill($request->all('title'));
        $reportType->save();
        return $this->resourceItemResponse('reportType', $reportType);
    }

    /**
     * @param ReportType $reportType
     * @return JsonResponse
     */
    public function destroy(ReportType $reportType):JsonResponse
    {
        $reportType->delete();
        return $this->emptySuccessResponse();
    }
}
