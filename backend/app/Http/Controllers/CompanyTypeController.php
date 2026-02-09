<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyType\CompanyTypeRequest;
use App\Models\CompanyType;
use Illuminate\Http\JsonResponse;

class CompanyTypeController extends Controller
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
        // Исключаем тип "Заказчик-Поставщик" если он еще существует
        // Используем whereNotIn для более надежной фильтрации
        $companyTypes = CompanyType::where(function($query) {
                $query->where('title', '!=', 'Заказчик-Поставщик')
                      ->where('title', 'not like', '%Заказчик%Поставщик%')
                      ->where('title', '!=', 'Заказчик - Поставщик')
                      ->where('title', '!=', 'Заказчик/Поставщик');
            })
            ->get();
        return $this->resourceListResponse('companyTypes', $companyTypes, $companyTypes->count(), 1);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * @param CompanyTypeRequest $request
     * @return JsonResponse
     */
    public function store(CompanyTypeRequest $request): JsonResponse
    {
        $type = new CompanyType($request->all('title', 'is_worker'));
        $type->save();

        return $this->resourceItemResponse('companyType', $type);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\CompanyType $companyType
     * @return \Illuminate\Http\Response
     */
    public function show(CompanyType $companyType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\CompanyType $companyType
     * @return \Illuminate\Http\Response
     */
    public function edit(CompanyType $companyType)
    {
        //
    }

    /**
     * @param CompanyTypeRequest $request
     * @param CompanyType $companyType
     * @return JsonResponse
     */
    public function update(CompanyTypeRequest $request, CompanyType $companyType): JsonResponse
    {
        $companyType->fill($request->all('title', 'is_worker'));
        $companyType->save();

        return $this->resourceItemResponse('companyType', $companyType);
    }

    /**
     * @param CompanyType $companyType
     * @return JsonResponse
     */
    public function destroy(CompanyType $companyType):JsonResponse
    {
        $companyType->delete();
        return $this->emptySuccessResponse();
    }
}
