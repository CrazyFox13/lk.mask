<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Company\CompanyCreate;
use App\Http\Requests\Company\CompanyList;
use App\Http\Requests\Company\CompanyUpdate;
use App\Http\Requests\Company\SetAwards;
use App\Http\Requests\Company\SetVehicles;
use App\Models\Company;
use App\Models\ReservedNumber;
use App\Models\User;
use App\Models\VehicleGroup;
use Dadata\DadataClient;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show', 'passport']]);
    }

    /**
     * @param CompanyList $request
     * @return JsonResponse
     */
    public function index(CompanyList $request): JsonResponse
    {
        $companies = Company::query();
        $user = auth('sanctum')->user();
        if (!$user || $user->isUser()) {
            $companies = $companies->published();
        } else {
            switch ($request->get('status')) {
                case Company::MODERATION_STATUSES["DRAFT"]:
                    $companies = $companies->draft();
                    break;
                case Company::MODERATION_STATUSES["MODERATION"]:
                    $companies = $companies->onModeration();
                    break;
                case Company::MODERATION_STATUSES["APPROVED"]:
                    $companies = $companies->published();
                    break;
                case Company::MODERATION_STATUSES["CANCELED"]:
                    $companies = $companies->failed();
                    break;
            }
        }

        if ($request->has('cities_id')) {
            $companies = $companies->inCities(explode(',', $request->get('cities_id')));
        }

        if ($request->has('rating')) {
            $companies = $companies->where("rating", ">=", $request->get('rating'));
        }
        if ($request->has('vehicle_types_id')) {
            $vehList = explode(",", $request->get('vehicle_types_id'));
            if (in_array("-1", $vehList)) {
                $companies = $companies->withoutVehicles();
            } else {
                $companies = $companies->withVehicles($vehList);
            }
        }

        if ($request->has('company_types_id')) {
            $companies = $companies->whereIn("company_type_id", explode(',', $request->get('company_types_id')));
        }

        if ($request->get("workers")) {
            $companies = $companies->whereHas("type", function ($query) {
                $query->where("is_worker", true);
            });
        }

        if ($search = $request->get('search')) {
            $companies = $companies->search($search);
        }

        if ($request->get('show_deleted')) {
            $companies = $companies->withTrashed();
        }

        $totalCount = $companies->count();

        switch ($request->get('sort_by')) {
            case "created_at_desc":
                $companies = $companies->orderBy("created_at", "desc");
                break;
            case "created_at_asc":
                $companies = $companies->orderBy("created_at", "asc");
                break;
            case "rating_desc":
                $companies = $companies->orderBy("rating", "desc");
                break;
            case "rating_asc":
                $companies = $companies->orderBy("rating", "asc");
                break;
            default:
                // admin panel sorting
                list($sort, $sortDir) = Paginator::getSorting($request);
                $companies = $companies->orderBy($sort, $sortDir);
        }
        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $companies = $companies->skip($skip)->take($take);
        }
        $companies = $companies
            ->withCount(['approvedRecommendations', 'activeReports', 'activeOrders', 'vehicleTypes'])
            ->with(['boss' => function ($q) {
                $q->select(User::PUBLIC_FIELDS);
            }, 'boss.city', 'type'])
            ->get();

        $companies = $companies->each(function (Company $company) {
            $company->append('is_favorite');
        });

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('companies', $companies, $totalCount, $pagesCount);
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
        if (Gate::denies('create', Company::class)) {
            abort(403);
        }

        $data = $request->all();
        
        // Если тип компании не указан, устанавливаем "Заказчик" по умолчанию
        if (!isset($data['company_type_id']) || !$data['company_type_id']) {
            $customerType = \App\Models\CompanyType::where('title', 'Заказчик')->first();
            if ($customerType) {
                $data['company_type_id'] = $customerType->id;
            }
        }

        $company = new Company($data);
        $company->save();

        if ($request->has('vehicle_types_id')) {
            $company->vehicleTypes()->sync($request->get('vehicle_types_id'));
        }

        if ($request->has('documents')) {
            $company->setDocuments($request->get('documents'));
        }

        $user = auth('sanctum')->user();
        if (!$user->isUser()) {
            $uid = $request->get("user_id");
            $boss = User::query()->find($uid);
            if ($boss) {
                $boss->company_id = $company->id;
                $boss->company_role = User::COMPANY_ROLES[1];
                $boss->save();

                $user = $boss;
            }
        }

        if (!$user->auto_subscribe_vehicles) {
            if ($user->subscribedVehicles()->count() === 0) {
                $user->subscribedVehicles()->attach($company->vehicleTypes()->pluck("vehicle_types.id"));
                $user->auto_subscribe_vehicles = true;
                $user->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'company' => $company,
            'boss' => isset($boss) ? $boss->id : '-'
        ]);
    }

    /**
     * @param Company $company
     * @return JsonResponse
     */
    public function show(Company $company): JsonResponse
    {
        $company->load([/*'vehicleTypes','vehicleTypes.group', */ 'documents', 'boss' => function ($q) {
            $q->withTrashed()->select(User::PUBLIC_FIELDS);
        }, 'boss.city', 'reservedNumber']);
        $company->loadCount(['approvedRecommendations', 'activeReports', 'activeOrders']);

        $company->append('is_favorite');

        $companyTypesClosure = function ($q) use ($company) {
            $q->whereHas("companies", function ($q) use ($company) {
                $q->where("companies.id", "=", $company->id);
            });
        };

        $vehicleGroups = VehicleGroup::query()
            ->whereHas('types', $companyTypesClosure)
            ->with('types', $companyTypesClosure)
            ->get();

        $company->vehicle_groups = $vehicleGroups;

        return response()->json([
            'status' => 'success',
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Company $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        //
    }

    /**
     * @param CompanyUpdate $request
     * @param Company $company
     * @return JsonResponse
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        if (Gate::denies('update', $company)) {
            abort(403);
        }

        $company->fill($request->all());

        if (Gate::allows('moderate', $company)) {
            if ($request->has('membership_fee_paid_at')) {
                $company->membership_fee_paid_at = $request->get('membership_fee_paid_at');
            }
            if ($request->has('legal_registration_date')) {
                $company->legal_registration_date = $request->get('legal_registration_date');
            }

            if ($request->has("self_park")) {
                $company->self_park = $request->get('self_park');
            }

            if ($request->has("instant_moderation")) {
                $company->instant_moderation = $request->get('instant_moderation');
            }

            if ($request->has("verified")) {
                $company->verified = $request->get('verified');
            }

            if ($request->has("reg_number")) {
                $request->validate([
                    'reg_number' => ["required",
                        "string", "size:6",
                        Rule::unique("companies", 'reg_number')
                            ->ignore($company->id),
                        'unique:reserved_numbers,number'
                    ]
                ]);
                $company->reg_number = $request->get('reg_number');
            }
        }

        $company->save();

        if ($request->has('vehicle_types_id')) {
            $company->vehicleTypes()->sync($request->get('vehicle_types_id'));
        }

        if ($request->has('documents')) {
            $company->setDocuments($request->get('documents'));
        }

        $user = auth("sanctum")->user();

        if (!$user->auto_subscribe_vehicles) {
            if ($user->subscribedVehicles()->count() === 0) {
                $user->subscribedVehicles()->attach($company->vehicleTypes()->pluck("vehicle_types.id"));
                $user->auto_subscribe_vehicles = true;
            }
        }

        $company->load('documents');

        return response()->json([
            'status' => 'success',
            'company' => $company
        ]);
    }

    /**
     * @param Company $company
     * @return JsonResponse
     */
    public function destroy(Company $company, Request $request): JsonResponse
    {
        if (Gate::denies('delete', $company)) abort(403);

        if ($request->get("force")) {
            $company->forceDelete();
        } else {
            $company->delete();
        }

        return $this->emptySuccessResponse();
    }

    /**
     * @param CompanyUpdate $request
     * @param Company $company
     * @return JsonResponse
     */
    public function validateCompany(CompanyUpdate $request, Company $company): JsonResponse
    {
        return $this->emptySuccessResponse();
    }

    public function draft(Company $company): JsonResponse
    {
        if (Gate::denies('update', $company)) abort(403);

        $company->makeDraft();
        return $this->emptySuccessResponse();
    }

    public function moderate(Company $company): JsonResponse
    {
        if (Gate::denies('update', $company)) abort(403);
        $company->append('vehicle_types_id');
        $company->load('documents');
        $rules = new CompanyUpdate();
        $validator = Validator::make($company->toArray(), $rules->rules());

        $user = auth('sanctum')->user();
        if ($user->isUser() && $validator->fails()) {
            return response()->json([
                'message' => 'Some fields are filled in incorrectly',
                'errors' => array_map(function ($errors) {
                    return $errors[0];
                }, $validator->errors()->toArray())
            ], 422);
        }

        $company->sendOnModeration();
        return $this->emptySuccessResponse();
    }

    public function approve(Company $company): JsonResponse
    {
        if (Gate::denies('moderate', $company)) abort(403);

        $company->passModeration();
        return $this->emptySuccessResponse();
    }

    public function cancel(Company $company, Request $request): JsonResponse
    {
        if (Gate::denies('moderate', $company)) abort(403);

        $company->failModeration($request->get('text'));
        return $this->emptySuccessResponse();
    }

    public function setReservedNumber(Company $company, Request $request): JsonResponse
    {
        if (Gate::denies('moderate', $company)) abort(403);

        ReservedNumber::query()
            ->where("company_id", $company->id)
            ->update(['company_id' => null]);

        if ($request->get("reservedNumberId")) {
            $request->validate([
                'reservedNumberId' => 'sometimes|exists:reserved_numbers,id'
            ]);

            $number = ReservedNumber::find($request->reservedNumberId);
            $number->company_id = $company->id;
            $number->save();
        }

        return $this->emptySuccessResponse();
    }

    public function finDataByINN(Request $request): JsonResponse
    {
        $request->validate([
            'inn' => 'required'
        ]);

        $dadata = new DadataClient(config('services.dadata.key'), config('services.dadata.secret'));
        $result = $dadata->findById("party", $request->inn, 1);
        return $this->resourceItemResponse('data', $result);
    }

    public function validateINN(Request $request): JsonResponse
    {
        $request->validate([
            'inn' => 'required'
        ]);
        $company = Company::query()->where("inn", $request->get("inn"))->first();
        return $this->resourceItemResponse('company_id', $company?->id, ['company_exists' => !!$company]);
    }

    public function passport(Company $company): JsonResponse
    {
        $company->updateRating();
        $company->load('awards', 'boss', 'boss.city');
        return $this->resourceItemResponse('company', $company, [
            'ratingDetails' => $company->getRatingDetails(),
        ]);
    }

    public function setAwards(SetAwards $request, Company $company): JsonResponse
    {
        $company->awards()->sync($request->get("awards"));
        return $this->emptySuccessResponse();
    }

    public function setVehicles(Company $company, SetVehicles $request): JsonResponse
    {
        $company->vehicleTypes()->sync($request->get('vehicle_types_id'));
        return $this->emptySuccessResponse();
    }
}
