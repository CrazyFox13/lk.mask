<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Photo\Create;
use App\Http\Requests\Photo\PhotoRequest;
use App\Http\Requests\Photo\Update;
use App\Models\Company;
use App\Models\Photo;
use App\Models\PhotoGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PhotoController extends Controller
{
    // ID типа компании "Заказчик"
    const CUSTOMER_COMPANY_TYPE_ID = 3;

    /**
     * Проверяет, является ли компания пользователя типом "Заказчик"
     * Если да, то блокирует доступ к операциям с фотографиями портфолио
     *
     * @return void
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function checkCustomerCompanyType()
    {
        $user = auth("sanctum")->user();
        
        if ($user && $user->company_id) {
            $company = Company::find($user->company_id);
            
            if ($company && $company->company_type_id === self::CUSTOMER_COMPANY_TYPE_ID) {
                abort(403, 'Доступ к портфолио закрыт для компаний типа "Заказчик"');
            }
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Company $company, PhotoGroup $photoGroup, Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        list($page, $skip, $take) = Paginator::get($request);

        $totalCount = Photo::query()
            ->where('photo_group_id', $photoGroup->id)
            ->count();

        $photos = Photo::query()
            ->where('photo_group_id', $photoGroup->id)
            ->orderBy('id', 'desc')
            ->skip($skip)
            ->take($take)
            ->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('photos', $photos, $totalCount, $pagesCount);
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
    public function store(Company $company, PhotoGroup $photoGroup, Create $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $photo = new Photo($request->all());
        $photo->photo_group_id = $photoGroup->id;
        $photo->save();

        return $this->resourceItemResponse('photo', $photo);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Photo $photo
     * @return \Illuminate\Http\Response
     */
    public function show(Photo $photo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Photo $photo
     * @return \Illuminate\Http\Response
     */
    public function edit(Photo $photo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Photo $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Update $request, Company $company, PhotoGroup $photoGroup, Photo $photo): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $photo->fill($request->all());
        $photo->save();
        return $this->resourceItemResponse('photo', $photo);
    }

    /**
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @param Photo $photo
     * @return JsonResponse
     */
    public function destroy(Company $company, PhotoGroup $photoGroup, Photo $photo): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('delete', [$photo, $company, $photoGroup])) abort(403);
        $photo->delete();
        return $this->emptySuccessResponse();
    }
}
