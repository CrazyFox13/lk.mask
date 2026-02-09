<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Order\Create;
use App\Http\Requests\Order\CreateFullOrder;
use App\Http\Requests\Order\OrderList;
use App\Http\Requests\Order\SetAddresses;
use App\Http\Requests\Order\SetBudget;
use App\Http\Requests\Order\SetDates;
use App\Http\Requests\Order\SetDetails;
use App\Http\Requests\Order\Update;
use App\Models\Chat;
use App\Models\Company;
use App\Models\FavoriteOrder;
use App\Models\Order;
use App\Models\OrderFilter;
use App\Models\OrderOffer;
use App\Models\User;
use App\Models\VehicleGroup;
use App\Models\VehicleType;
use App\Traits\ValidateOnly;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    use ValidateOnly;

    // ID типа компании "Заказчик"
    const CUSTOMER_COMPANY_TYPE_ID = 3;

    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['index', 'show']]);
    }

    /**
     * Проверяет, является ли компания пользователя типом "Заказчик"
     * Если да, то блокирует доступ к операциям с заявками
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
                abort(403, 'Доступ к заявкам закрыт для компаний типа "Заказчик"');
            }
        }
    }

    protected function enforceCustomerOrderAction(Order $order, string $action): void
    {
        $user = auth("sanctum")->user();
        if (!$user || !$user->company_id) {
            return;
        }

        $company = Company::find($user->company_id);
        if (!$company || $company->company_type_id !== self::CUSTOMER_COMPANY_TYPE_ID) {
            return;
        }

        $status = $order->moderation_status;
        $allowed = [];
        $message = 'Действие недоступно для текущего статуса';

        switch ($action) {
            case 'edit':
                $allowed = [
                    Order::MODERATION_STATUSES["DRAFT"],
                    Order::MODERATION_STATUSES["MODERATION"],
                    Order::MODERATION_STATUSES["APPROVED"],
                    Order::MODERATION_STATUSES["ON_APPROVAL"],
                ];
                $message = 'Редактирование доступно только для статусов "Черновик", "На модерации", "Новая заявка", "На согласовании"';
                break;
            case 'delete':
                $allowed = [Order::MODERATION_STATUSES["DRAFT"]];
                $message = 'Удаление доступно только для статуса "Черновик"';
                break;
            case 'cancel':
                $allowed = [
                    Order::MODERATION_STATUSES["MODERATION"],
                    Order::MODERATION_STATUSES["APPROVED"],
                    Order::MODERATION_STATUSES["ON_APPROVAL"],
                ];
                $message = 'Отмена доступна только для статусов "На модерации", "Новая заявка", "На согласовании"';
                break;
        }

        if ($allowed && !in_array($status, $allowed, true)) {
            abort(403, $message);
        }
    }

    /**
     * @param OrderList $request
     * @return JsonResponse
     */
    public function index(OrderList $request): JsonResponse
    {
        $orders = Order::query()
            ->visible()
            ->filtered($request);

        if ($request->get('favorite')) {
            $orders = $orders->favorite();
        }

        $totalCount = $orders->count();

        switch ($request->get('sort_by')) {
            case "created_at_asc":
                $orders = $orders->orderBy("created_at", "asc");
                break;
            case "created_at_desc":
                $orders = $orders->orderBy("created_at", "desc");
                break;
            default:
                list($sort, $sortDir) = Paginator::getSorting($request);
                $orders = $orders->orderBy($sort, $sortDir);
        }

        if ($request->get('show_deleted')) {
            $orders = $orders->withTrashed();
        }

        $user = auth("sanctum")->user();
        $orders = $orders->with(['startAddress', 'startAddress.city', 'startAddress.region', 'company' => function ($q) {
            $q->published()
                ->withCount('approvedRecommendations', 'activeReports', 'activeOrders');
                //->select(Company::SHOW_FOR_GUESTS)
            ;
        }, 'company.boss' => function ($q) {
            $q->select(User::SHOW_FOR_GUESTS)->withCount('approvedRecommendations', 'activeReports', 'activeOrders');
        }, 'vehicleType', 'vehicleType.group', 'user' => function ($q) {
            $q->withCount('approvedRecommendations', 'activeReports');
        }, "companyOffer" => function ($q) use ($user) {
            $q->with(["user" => function ($q) {
                $q->select(User::SHOW_FOR_GUESTS);
            }])->company($user->company_id ?? null);
        }])->withCount(["offers" => function ($q) {
            $q->notDeclined();
        }, "offers as new_offers_count" => function ($q) use ($user) {
            $q->notDeclined()->new($user->company_id ?? null);
        }]);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) $orders = $orders->skip($skip)->take($take);
        $orders = $orders->get();
        $pagesCount = Paginator::pagesCount($take, $totalCount);

        // check if favourite
        $favourites = auth()->check() ? FavoriteOrder::query()
            ->where("user_id", auth()->id())
            ->whereIn("order_id", $orders->pluck("id"))
            ->pluck('order_id') : collect([]);

        $orders = $orders->map(function (Order $order) use ($favourites) {
            $order->is_favorite = in_array($order->id, $favourites->toArray());
            $order->append('is_viewed');
            return $order;
        });


        return $this->resourceListResponse('orders', $orders, $totalCount, $pagesCount, [
            'orderFiltersExists' => OrderFilter::query()
                ->where("user_id", auth('sanctum')->id())
                ->exists(),

            'availableToSave' => OrderFilter::availableToSave($request),
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
     * @param Create $request
     * @return JsonResponse
     */
    public function store(Create $request): JsonResponse
    {
        if ($this->validateOnly()) {
            return $this->emptySuccessResponse();
        }

        $user = auth()->user();
        $vehicleType = VehicleType::query()->find($request->get('vehicle_type_id'));

        $order = new Order();
        $order->user_id = $user->id;
        $order->company_id = $user->company_id;
        $order->vehicle_type_id = $vehicleType->id;
        $order->vehicles_count = $request->get('vehicles_count');
        $order->title = $vehicleType->title;
        $order->save();

        $order->setAnswers($request->get('form_answers'));

        $order->generateTitle();

        return $this->resourceItemResponse('order', $order);
    }

    public function createFullOrder(CreateFullOrder $request)
    {
        $user = auth()->user();
        $vehicleType = VehicleType::query()->find($request->get('vehicle_type_id'));
        
        if (!$vehicleType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Тип техники не найден'
            ], 422);
        }

        $order = new Order();
        $order->user_id = $user->id;
        $order->company_id = $user->company_id;
        $order->vehicle_type_id = $vehicleType->id;
        $order->vehicles_count = $request->get('vehicles_count');
        $order->title = $vehicleType->title;
        $order->start_date = $request->get('start_date');
        $order->finish_date = $request->get('finish_date');
        $order->payment_unit_id = $request->get('payment_unit_id');
        $order->amount_account_vat = $request->get('amount_account_vat') ?? 0;
        $order->amount_account = $request->get('amount_account') ?? 0;
        $order->amount_cash = $request->get('amount_cash') ?? 0;
        $order->amount_by_agreement = $request->boolean('amount_by_agreement', false);
        $order->no_haggling = $request->boolean('no_haggling', false);
        $order->description = $request->get('description');
        $order->communication_way = $request->get('communication_way') ?: "any";
        $order->save();


        $order->setAnswers($request->get('form_answers'));

        $order->generateTitle();

        $addresses = $request->get("addresses");
        $order->setAddresses($addresses);

        if ($request->has('documents')) {
            $order->setDocuments($request->get('documents'));
        }

        $order->load('addresses', 'addresses.city', 'documents');

        // Если moderate = 1 или true, отправляем на модерацию, иначе оставляем как черновик
        if ($request->get('moderate') == 1 || $request->boolean('moderate', false)) {
            $order->sendOnModeration();
        } else {
            // Явно устанавливаем статус черновика, чтобы гарантировать, что заявка не перейдет в другие статусы
            $order->makeDraft();
        }
        return $this->resourceItemResponse('order', $order);
    }

    /**
     * @param Order $order
     * @return JsonResponse
     */
    public function show(Order $order): JsonResponse
    {
        try {
            $user = auth("sanctum")->user();
            $companyId = $user ? ($user->company_id ?? null) : null;
            
            // Загружаем базовые связи без withCount
            $order->load([
                'documents',
                'addresses',
                'vehicleType',
                'vehicleType.group',
                'paymentUnit',
                'formAnswers',
                'formAnswers.question'
            ]);
            
            // Загружаем addresses.city и addresses.region
            try {
                $order->load(['addresses.city', 'addresses.region']);
            } catch (\Exception $e) {
                Log::warning('Ошибка при загрузке addresses.city/region: ' . $e->getMessage());
            }
            
            // Загружаем user
            if ($order->user_id) {
                $order->load(['user']);
            }
            
            // Загружаем company
            if ($order->company_id) {
                try {
                    $order->load(['company', 'company.boss']);
                } catch (\Exception $e) {
                    Log::warning('Ошибка при загрузке company: ' . $e->getMessage());
                }
            }
            
            // Загружаем companyOffer
            if ($companyId) {
                try {
                    $order->load([
                        "companyOffer" => function ($q) use ($companyId) {
                            $q->company($companyId);
                        }
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Ошибка при загрузке companyOffer: ' . $e->getMessage());
                }
            }
            
            // Загружаем счетчики
            try {
                $order->loadCount([
                    "offers" => function ($q) {
                        $q->notDeclined();
                    },
                    "offers as new_offers_count" => function ($q) use ($companyId) {
                        $q->notDeclined()->new($companyId);
                    }
                ]);
            } catch (\Exception $e) {
                Log::warning('Ошибка при загрузке счетчиков offers: ' . $e->getMessage());
                $order->offers_count = 0;
                $order->new_offers_count = 0;
            }

            // Добавляем атрибуты
            try {
                $order->append('is_favorite', 'is_viewed');
            } catch (\Exception $e) {
                Log::warning('Ошибка при добавлении атрибутов: ' . $e->getMessage());
                $order->is_favorite = false;
                $order->is_viewed = false;
            }

            // Проверяем, является ли текущая компания исполнителем (имеет принятое предложение)
            $order->is_contractor = false;
            if ($companyId && $companyId !== $order->company_id) {
                try {
                    $acceptedOffer = OrderOffer::where('order_id', $order->id)
                        ->where('company_id', $companyId)
                        ->where('status', OrderOffer::STATUSES["ACCEPTED"])
                        ->exists();
                    $order->is_contractor = $acceptedOffer;
                } catch (\Exception $e) {
                    Log::warning('Ошибка при проверке is_contractor: ' . $e->getMessage());
                }
            }

            // Вызываем view()
            if ($user) {
                try {
                    $order->view();
                } catch (\Exception $e) {
                    Log::warning('Ошибка при вызове view(): ' . $e->getMessage());
                }
            }

            return $this->resourceItemResponse('order', $order);
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке заявки: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при загрузке заявки: ' . $e->getMessage(),
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * @param Update $request
     * @param Order $order
     * @return JsonResponse
     */
    public function update(Update $request, Order $order): JsonResponse
    {
        try {
            $this->enforceCustomerOrderAction($order, 'edit');

            $vehicleType = VehicleType::query()->find($order->vehicle_type_id);
            
            if (!$vehicleType) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Тип техники не найден'
                ], 422);
            }
            
            $order->vehicles_count = $request->get('vehicles_count');
            $order->title = $vehicleType->title;
            $order->payment_unit_id = $request->get('payment_unit_id');
            $order->amount_account_vat = $request->get('amount_account_vat') ?? 0;
            $order->amount_account = $request->get('amount_account') ?? 0;
            $order->amount_cash = $request->get('amount_cash') ?? 0;
            $order->amount_by_agreement = $request->boolean('amount_by_agreement', false);
            $order->no_haggling = $request->boolean('no_haggling', false);
            $order->description = $request->get('description');
            $order->communication_way = $request->get('communication_way') ?: "any";

            if ($request->has("start_date")) {
                $order->start_date = $this->normalizeDateValue($request->get('start_date'));
            }

            if ($request->has("finish_date")) {
                $order->finish_date = $this->normalizeDateValue($request->get('finish_date'));
            }

            $order->save();

            $order->setAnswers($request->get('form_answers'));

            $addresses = $request->get("addresses");
            $order->setAddresses($addresses);

            if ($request->has('documents')) {
                $order->setDocuments($request->get('documents'));
            }

            // Загружаем vehicleType если нужно, затем генерируем заголовок
            if (!$order->relationLoaded('vehicleType')) {
                $order->load('vehicleType');
            }
            if ($order->vehicleType) {
                $order->generateTitle();
            }

            // Отправляем на модерацию только если передан параметр moderate
            // Если заявка была черновиком и moderate не передан, оставляем как черновик
            if ($request->get('moderate') == 1 || $request->boolean('moderate', false)) {
                $order->sendOnModeration();
            } elseif ($order->moderation_status === Order::MODERATION_STATUSES["DRAFT"]) {
                // Если заявка была черновиком и moderate не передан, сохраняем как черновик
                $order->makeDraft();
            }

            $order->load('addresses', 'addresses.city', 'documents', 'vehicleType', 'vehicleType.group', 'paymentUnit');

            return $this->resourceItemResponse('order', $order);
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении заявки: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Ошибка при обновлении заявки'
            ], 500);
        }
    }

    private function normalizeDateValue($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception $e) {
            Log::warning('Некорректный формат даты: ' . $e->getMessage(), [
                'value' => $value
            ]);
            return null;
        }
    }

    public function setDates(Order $order, SetDates $request): JsonResponse
    {
        $this->enforceCustomerOrderAction($order, 'edit');
        
        if ($this->validateOnly()) {
            return $this->emptySuccessResponse();
        }

        $order->start_date = $request->get('start_date');
        $order->finish_date = $request->get('finish_date');
        $order->save();

        return $this->resourceItemResponse('order', $order);
    }

    public function setAddresses(Order $order, SetAddresses $request): JsonResponse
    {
        $this->enforceCustomerOrderAction($order, 'edit');
        
        if ($this->validateOnly()) {
            return $this->emptySuccessResponse();
        }

        $addresses = $request->get("addresses");
        $order->setAddresses($addresses);

        $order->load('addresses', 'addresses.city');

        return $this->resourceItemResponse('order', $order);
    }

    public function setBudget(Order $order, SetBudget $request): JsonResponse
    {
        $this->enforceCustomerOrderAction($order, 'edit');
        
        if ($this->validateOnly()) {
            return $this->emptySuccessResponse();
        }

        $order->payment_unit_id = $request->get('payment_unit_id');
        $order->amount_account_vat = $request->get('amount_account_vat') ?: 0;
        $order->amount_account = $request->get('amount_account') ?: 0;
        $order->amount_cash = $request->get('amount_cash') ?: 0;
        $order->amount_by_agreement = $request->get('amount_by_agreement') ?: 0;
        $order->no_haggling = $request->get('no_haggling') ?: 0;
        $order->save();

        return $this->resourceItemResponse('order', $order);
    }

    public function setDetails(Order $order, SetDetails $request): JsonResponse
    {
        $this->enforceCustomerOrderAction($order, 'edit');
        
        if ($this->validateOnly()) {
            return $this->emptySuccessResponse();
        }

        $order->description = $request->get('description');
        $order->communication_way = $request->get('communication_way') ?: "any";
        $order->save();

        if ($request->has('documents')) {
            $order->setDocuments($request->get('documents'));
        }

        $order->load('documents');

        if ($request->has('moderate')) {
            $order->sendOnModeration();
        }

        return $this->resourceItemResponse('order', $order);
    }

    public function draft(Order $order)
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $order)) abort(403);

        $order->makeDraft();
        return $this->emptySuccessResponse();
    }

    public function moderate(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $order)) abort(403);

        $order->sendOnModeration();
        return $this->resourceItemResponse('status', $order->moderation_status);
    }

    public function approve(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('moderate', $order)) abort(403);

        $order->passModeration();
        return $this->emptySuccessResponse();
    }

    public function cancel(Order $order, Request $request): JsonResponse
    {
        try {
            if (Gate::denies('update', $order)) abort(403);

            $this->enforceCustomerOrderAction($order, 'cancel');

            $request->validate([
                'cancel_reason' => 'required|in:works_canceled,found_equipment,other'
            ]);

            $cancelReason = $request->get('cancel_reason');
            $order->failModeration($request->get('text'), $cancelReason);
            
            return $this->emptySuccessResponse();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Ошибка при отмене заявки: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при отмене заявки: ' . $e->getMessage()
            ], 500);
        }
    }

    public function remove(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $order)) abort(403);
        $order->remove();
        return $this->emptySuccessResponse();
    }

    public function complete(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $order)) abort(403);
        $order->complete();
        return $this->emptySuccessResponse();
    }

    public function onApproval(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $order)) abort(403);
        $order->setOnApproval();
        return $this->emptySuccessResponse();
    }

    public function inProgress(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        if (Gate::denies('update', $order)) abort(403);
        $order->setInProgress();
        return $this->emptySuccessResponse();
    }

    /**
     * Установить статус заявки (для поставщиков - владельцев заявки или исполнителей)
     */
    public function setStatus(Order $order, Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $user = auth("sanctum")->user();
        $companyId = $user ? $user->company_id : null;
        
        // Проверяем права: владелец заявки или исполнитель (компания с принятым предложением)
        $isOwner = !Gate::denies('update', $order);
        $isContractor = false;
        
        if ($companyId && $companyId !== $order->company_id) {
            $isContractor = OrderOffer::where('order_id', $order->id)
                ->where('company_id', $companyId)
                ->where('status', OrderOffer::STATUSES["ACCEPTED"])
                ->exists();
        }
        
        if (!$isOwner && !$isContractor) {
            abort(403, 'Нет прав для изменения статуса заявки');
        }

        $request->validate([
            'status' => 'required|in:approved,on_approval,in_progress,canceled,completed'
        ]);

        $status = $request->get('status');

        switch ($status) {
            case 'approved':
                $order->rePublish();
                break;
            case 'on_approval':
                $order->setOnApproval();
                break;
            case 'in_progress':
                $order->setInProgress();
                break;
            case 'canceled':
                $order->failModeration($request->get('text'), $request->get('cancel_reason'));
                break;
            case 'completed':
                $order->complete();
                break;
        }

        return $this->resourceItemResponse('status', $order->moderation_status);
    }

    public function favorite(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $inFavorite = $order->inFavorite() ? $order->removeFromFavorite() : $order->addToFavorite();
        return $this->resourceItemResponse('in_favorite', $inFavorite);
    }

    public function chat(Order $order): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $user = auth("sanctum")->user();
        $chat = $user->chats()->where("order_id", "=", $order->id)->first();
        if (!$chat) {
            $chat = new Chat(["order_id" => $order->id]);
            $chat->save();

            // invite author;
            $chat->users()->attach(auth()->id(), ['role' => 'author']);

            if ($order->user_id !== auth()->id()) {
                // invite target
                $chat->users()->attach($order->user_id, ['role' => 'target']);
            }
        }

        return $this->resourceItemResponse("chat", $user->chats()->find($chat->id));
    }

    /**
     * @param Order $order
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Order $order, Request $request): JsonResponse
    {
        $this->enforceCustomerOrderAction($order, 'delete');
        
        if (Gate::denies('delete', $order)) abort(403);

        if ($request->get("force")) {
            $order->forceDelete();
        } else {
            $order->delete();
        }

        return $this->emptySuccessResponse();
    }

    public function multipleDelete(Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $request->validate([
            'ids' => 'array|min:1',
            'ids.*' => 'integer|exists:orders,id'
        ]);

        Order::query()->whereIn("id", $request->get("ids"))->delete();

        return $this->emptySuccessResponse();
    }

    public function multipleModeration(Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $request->validate([
            'ids' => 'array|min:1',
            'ids.*' => 'integer|exists:orders,id',
            'status' => "required|in:" . implode(",", Order::MODERATION_STATUSES),
        ]);

        Order::query()->whereIn("id", $request->get("ids"))->update([
            'moderation_status' => $request->get("status"),
            'moderation_message' => $request->get("text"),
        ]);

        return $this->emptySuccessResponse();
    }

    public function employeeReport(Request $request): JsonResponse
    {
        $this->checkCustomerCompanyType();
        
        $request->validate([
            "company_id" => "required|exists:companies,id",
            "date_start" => "required|date|before:date_end",
            "date_end" => "required|date|after:date_start",
        ]);

        /** @var Company $company */
        $company = Company::query()->find($request->get("company_id"));

        list("date_start" => $dateStart, "date_end" => $dateEnd) = $request->all(["date_start", "date_end"]);

        $employees = $company->users()
            ->withCount(["orders" => function (Builder $query) use ($dateStart, $dateEnd) {
                $query->inDateRange([$dateStart, $dateEnd]);
            }, "activeOrders" => function (Builder $query) use ($dateStart, $dateEnd) {
                $query->inDateRange([$dateStart, $dateEnd]);
            }, "finishedOrders" => function (Builder $query) use ($dateStart, $dateEnd) {
                $query->inDateRange([$dateStart, $dateEnd]);
            }, "completedOrders" => function (Builder $query) use ($dateStart, $dateEnd) {
                $query->inDateRange([$dateStart, $dateEnd]);
            }])
            ->with(["lastOrder"])
            #->select("id", "name", "surname", "company_id")
            ->get();

        return $this->resourceListResponse("employees", $employees, count($employees), 1);
    }
}
