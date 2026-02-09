<?php

namespace App\Http\Controllers;

use App\Helpers\Mutator;
use App\Helpers\Paginator;
use App\Http\Requests\Employee\Create;
use App\Http\Requests\Employee\Destroy;
use App\Http\Requests\Employee\Update;
use App\Models\Company;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    /**
     * @param Company $company
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Company $company, Request $request): JsonResponse
    {
        list($page, $skip, $take) = Paginator::get($request);

        $users = User::query()
            ->where('company_id', $company->id)
            ->staff();

        if ($take > 0)
            $users = $users
                ->skip($skip)->take($take);

        $totalCount = $users->count();

        $users = $users
            ->orderBy('id', 'desc')
            ->withCount('orders', 'activeOrders')
            ->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse('users', $users, $totalCount, $pagesCount);
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
        $user = new User();
        $user->name = $request->get('name');
        $user->surname = $request->get('surname');
        $user->email = $request->get('email');
        $user->phone = $request->get('phone');
        $user->company_id = $company->id;
        $user->company_role = User::COMPANY_ROLES[0];
        $user->password = Hash::make(Str::random(16));
        $user->save();

        $phoneNumber = Mutator::digitsToRuPhoneNumber($user->phone);
        UserLog::write($company->boss->id, "Добавлен сотрудник $user->name $user->surname номер телефона $phoneNumber");

        $boss = $company->boss()->first();
        $user->subscribedVehicles()->attach($boss->subscribedVehicles()->pluck("vehicle_types.id"));
        $user->subscribedCities()->attach($boss->subscribedCities()->pluck("geo_cities.id"));

        $user->sendEmailInvitation();

        return $this->resourceItemResponse('user', $user);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company, User $employee): JsonResponse
    {
        $employee->loadCount('orders', 'activeOrders');
        return $this->resourceItemResponse('user', $employee);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * @param Update $request
     * @param Company $company
     * @param User $user
     * @return JsonResponse
     */
    public function update(Update $request, Company $company, User $employee): JsonResponse
    {
        $employee->fill($request->all());
        $employee->phone = $request->get("phone");

        $changes = [];
        if ($employee->isDirty('name')) {
            $changes[] = "имя на $employee->name";
        }
        if ($employee->isDirty('surname')) {
            $changes[] = "фамилия на $employee->surname";
        }

        if ($employee->isDirty("email")) {
            $originalEmail = $employee->getOriginal('email');
            $changes[] = "email c  <s>$originalEmail</s> на $employee->email";
            $employee->resetEmailVerification();
        }

        if ($employee->isDirty("phone")) {
            $originalPhone = $employee->getOriginal('phone');
            $originalPhone = Mutator::digitsToRuPhoneNumber($originalPhone);
            $newPhone = Mutator::digitsToRuPhoneNumber($employee->phone);
            $changes[] = "телефон с <s>$originalPhone</s> на $newPhone";
            $employee->resetPhoneVerification();
        }

        $originalName = $employee->getOriginal('name');
        $originalSurname = $employee->getOriginal('surname');
        $changesMessage = implode(", ", $changes);
        $logMessage = "У сотрудника $originalName $originalSurname изменено: $changesMessage";
        UserLog::write($company->boss->id, $logMessage);

        $employee->save();
        return $this->resourceItemResponse('user', $employee);
    }

    public function sendDeleteConfirmation(Company $company, User $employee): JsonResponse
    {
        if (Gate::denies('delete', $employee)) abort(403);

        $user = User::query()->find(auth("sanctum")->id());
        if ($user->isTestMode()) {
            $employee->delete_confirmation_code = 123456;
        } else {
            $employee->delete_confirmation_code = random_int(100000, 999999);
        }
        $employee->save();
        $user->sendSMS("Код подтверждения: $employee->delete_confirmation_code");
        return $this->emptySuccessResponse();
    }

    /**
     * @param Company $company
     * @param User $employee
     * @param Destroy $request
     * @return JsonResponse
     */
    public function destroy(Company $company, User $employee, Destroy $request): JsonResponse
    {
        $user = auth('sanctum')->user();
        if ($request->has('password')) {
            if (!Hash::check($request->get('password'), $user->password)) {
                return response()->json([
                    "message" => "Some fields are filled in incorrectly",
                    "errors" => [
                        "password" => "Неверный пароль. Попробуйте еще раз."
                    ]
                ], 422);
            }
        } elseif ($request->has('code')) {
            if ((string)$request->get('code') !== (string)$employee->delete_confirmation_code) {
                return response()->json([
                    "message" => "Some fields are filled in incorrectly",
                    "errors" => [
                        "code" => "Неверный код. Попробуйте еще раз."
                    ]
                ], 422);
            }
        } else {
            abort(403);
        }

        $phoneNumber = Mutator::digitsToRuPhoneNumber($employee->phone);
        UserLog::write($company->boss->id, "Удалён сотрудник $employee->name $employee->surname номер телефона $phoneNumber");

        $employee->delete();

        return $this->emptySuccessResponse();
    }
}
