<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\PhotoGroup\BulkDelete;
use App\Http\Requests\PhotoGroup\Create;
use App\Http\Requests\PhotoGroup\Update;
use App\Models\Company;
use App\Models\Photo;
use App\Models\PhotoGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PhotoGroupController extends Controller
{
    // ID типа компании "Заказчик"
    const CUSTOMER_COMPANY_TYPE_ID = 3;

    /**
     * Проверяет, является ли компания пользователя типом "Заказчик"
     * Если да, то блокирует доступ к операциям с портфолио
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
     * @param Company $company
     * @return JsonResponse
     */
    public function index(Company $company, Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        list($page, $skip, $take) = Paginator::get($request);

        $totalCount = PhotoGroup::query()
            ->where("company_id", $company->id)
            ->count();

        $groups = PhotoGroup::query()
            ->where("company_id", $company->id)
            ->withCount('photos')
            ->with("photos")
            ->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('photoGroups', $groups, $totalCount, $pagesCount);
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
     * @param Company $company
     * @param Create $request
     * @return JsonResponse
     */
    public function store(Company $company, Create $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $group = new PhotoGroup([
            'company_id' => $company->id,
            'title' => $request->get('title')
        ]);
        $group->save();

        if ($request->has('photos')) {
            $group->bulkUploadPhotos($request->get('photos'));
        }

        $group->loadCount('photos');
        $group->load('photos');

        return response()->json([
            'status' => 'success',
            'photoGroup' => $group
        ]);
    }

    /**
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @return JsonResponse
     */
    public function show(Company $company, PhotoGroup $photoGroup): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $photoGroup->load('company','photos');
        return $this->resourceItemResponse('photoGroup', $photoGroup);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\PhotoGroup $photoGroup
     * @return \Illuminate\Http\Response
     */
    public function edit(PhotoGroup $photoGroup)
    {
        //
    }

    /**
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @param Update $request
     * @return JsonResponse
     */
    public function update(Company $company, PhotoGroup $photoGroup, Update $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $photoGroup->title = $request->get('title');
        $photoGroup->save();

        if ($request->has('photos')) {
            $photoGroup->bulkUploadPhotos($request->get('photos'));
        }

        $photoGroup->loadCount('photos');
        $photoGroup->load('photos');
        return response()->json([
            'status' => 'success',
            'photoGroup' => $photoGroup
        ]);
    }

    /**
     * @param Company $company
     * @param PhotoGroup $photoGroup
     * @return JsonResponse
     */
    public function destroy(Company $company, PhotoGroup $photoGroup): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if(Gate::denies('delete',[$photoGroup,$company])) abort(403);

        $photoGroup->delete();

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function bulkDelete(Company $company, PhotoGroup $photoGroup, BulkDelete $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        Photo::where("photo_group_id", $photoGroup->id)
            ->whereIn("id", $request->get("photos_id"))
            ->delete();

        return $this->emptySuccessResponse();
    }
}
