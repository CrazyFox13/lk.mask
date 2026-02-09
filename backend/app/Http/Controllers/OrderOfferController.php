<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\OrderOffer\OrderOfferRequest;
use App\Jobs\CompanyNotification\OrderOfferAccepted;
use App\Jobs\CompanyNotification\OrderOfferCreated;
use App\Jobs\CompanyNotification\OrderOfferDeclined;
use App\Models\NotificationTypeUser;
use App\Models\Order;
use App\Models\OrderOffer;
use App\Models\OrderOfferDocument;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;

class OrderOfferController extends Controller
{
    /**
     * @param Order $order
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Order $order, Request $request): JsonResponse
    {
        if (Gate::denies("viewAny", [OrderOffer::class, $order])) {
            abort(403);
        }

        $offers = $order->offers();

        if ($request->has("statuses")) {
            $statuses = explode(",", $request->get("statuses"));
            $offers = $offers->whereIn("status", $statuses);
        }

        $totalCount = $offers->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $offers = $offers->orderBy($sort, $sortDir);

        $withRelations = [
            "user" => function ($q) {
                $q->select(User::PUBLIC_FIELDS);
            },
            "company",
            "company.boss" => function ($q) {
                $q->select(User::PUBLIC_FIELDS);
            }
        ];
        if (Schema::hasTable('order_offer_documents')) {
            $withRelations[] = "documents";
            $withRelations[] = "printForm";
            $withRelations[] = "signedDocument";
            $withRelations[] = "invoice";
        }
        list($page, $skip, $take) = Paginator::get($request);

        $offers = $offers->with($withRelations);
        if ($take > 0) {
            $offers = $offers->skip($skip)->take($take);
        }
        $offers = $offers->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        if ($offers->isNotEmpty()) {
            OrderOffer::query()
                ->whereIn("id", $offers->pluck("id"))
                ->whereNull("viewed_at")
                ->update([
                    "viewed_at" => now(),
                ]);
        }

        return $this->resourceListResponse("orderOffers", $offers, $totalCount, $pagesCount);
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
     * @param Order $order
     * @param OrderOfferRequest $request
     * @return JsonResponse
     */
    public function store(Order $order, OrderOfferRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth("sanctum")->user();
        $offer = new OrderOffer([
            "order_id" => $order->id,
            "company_id" => $user->company_id,
            "user_id" => $user->id,
            ...$request->only([
                "amount_account_vat",
                "amount_account",
                "amount_cash",
                "date_start",
                "comment",
            ]),
        ]);
        $offer->save();

        // Автоматически меняем статус заявки на "На согласовании" при создании предложения
        // Только если заявка в статусе "approved" (новая заявка)
        if ($order->moderation_status === Order::MODERATION_STATUSES["APPROVED"]) {
            $order->setOnApproval();
        }

        if (NotificationTypeUser::isEnabled($order->user_id, 'orders', 'push')) {
            dispatch(new OrderOfferCreated($offer));
        }

        if (NotificationTypeUser::isEnabled($order->user_id, "personal", 'email')) {
            /** @var User $orderAuthor */
            $orderAuthor = $order->user()->first();
            if ($email = $orderAuthor->getRawOriginal('email')) {
                Mail::to($email)->later(now()->addSeconds($orderAuthor->getSilenceDelay()), new \App\Mail\OrderOfferCreated($offer));
            }
        }

        return $this->resourceItemResponse("orderOffer", $offer);
    }

    /**
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return JsonResponse
     */
    public function show(Order $order, OrderOffer $orderOffer): JsonResponse
    {
        // Загружаем документы только если таблица существует
        if (Schema::hasTable('order_offer_documents')) {
            $orderOffer->load("documents", "printForm", "signedDocument", "application", "invoice");
        }
        return $this->resourceItemResponse("orderOffer", $orderOffer);
    }

    /**
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return void
     */
    public function edit(Order $order, OrderOffer $orderOffer)
    {
        //
    }

    /**
     * @param OrderOfferRequest $request
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return JsonResponse
     */
    public function update(OrderOfferRequest $request, Order $order, OrderOffer $orderOffer): JsonResponse
    {
        // Проверяем, было ли предложение отклонено
        $wasDeclined = $orderOffer->status === OrderOffer::STATUSES["DECLINED"];
        
        $orderOffer->fill($request->only([
            "amount_account_vat",
            "amount_account",
            "amount_cash",
            "date_start",
            "comment",
        ]));
        
        // Если предложение было отклонено, возвращаем его в статус "ожидание"
        if ($wasDeclined) {
            $orderOffer->status = OrderOffer::STATUSES["WAITING"];
            // Сбрасываем viewed_at, чтобы предложение подсвечивалось как новое
            $orderOffer->viewed_at = null;
        }
        
        // Очищаем причину отказа при изменении предложения
        // Проверяем, существует ли поле decline_reason в таблице перед сохранением
        if (Schema::hasColumn('order_offers', 'decline_reason')) {
            $orderOffer->decline_reason = null;
        }
        
        $orderOffer->save();

        // Если предложение было отклонено и теперь возвращено, меняем статус заявки на "На согласовании"
        if ($wasDeclined) {
            $order->setOnApproval();
            
            // Отправляем уведомление о создании предложения (так как оно снова стало новым)
            try {
                if (NotificationTypeUser::isEnabled($order->user_id, 'orders', 'push')) {
                    dispatch(new OrderOfferCreated($orderOffer));
                }
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке push-уведомления о возврате предложения: ' . $e->getMessage());
            }

            try {
                if (NotificationTypeUser::isEnabled($order->user_id, "personal", 'email')) {
                    /** @var User $orderAuthor */
                    $orderAuthor = $order->user()->first();
                    if ($email = $orderAuthor->getRawOriginal('email')) {
                        Mail::to($email)->later(now()->addSeconds($orderAuthor->getSilenceDelay()), new \App\Mail\OrderOfferCreated($orderOffer));
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке email-уведомления о возврате предложения: ' . $e->getMessage());
            }
        }

        return $this->resourceItemResponse("orderOffer", $orderOffer);
    }

    public function accept(Order $order, OrderOffer $orderOffer): JsonResponse
    {
        try {
            $user = auth("sanctum")->user();
            if ($order->company_id !== $user->company_id) abort(403);

            $orderOffer->status = OrderOffer::STATUSES["ACCEPTED"];
            // Очищаем причину отказа при принятии предложения
            // Проверяем, существует ли поле decline_reason в таблице перед сохранением
            if (Schema::hasColumn('order_offers', 'decline_reason')) {
                $orderOffer->decline_reason = null;
            }
            
            $orderOffer->save();

            OrderOffer::query()
                ->where("order_id", "=", $order->id)
                ->where("id", "!=", $orderOffer->id)
                ->where("status", "=", OrderOffer::STATUSES["ACCEPTED"])
                ->update([
                    "status" => OrderOffer::STATUSES["WAITING"]
                ]);

            /*
             * Согласно https://wolfstudio.atlassian.net/browse/ASTT-887
             * при выборе исполнителя статус заявки не меняем
                $order->complete();
            */

            // Формирование договора (заявки) в HTML при принятии предложения
            try {
                $this->createApplicationDocument($order, $orderOffer);
            } catch (\Exception $e) {
                \Log::error('Ошибка при создании договора при принятии предложения: ' . $e->getMessage(), [
                    'order_id' => $order->id ?? null,
                    'offer_id' => $orderOffer->id ?? null,
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Отправка уведомлений - оборачиваем в try-catch, чтобы ошибки отправки не блокировали процесс
            try {
                if (NotificationTypeUser::isEnabled($orderOffer->user_id, 'orders', 'push')) {
                    dispatch(new OrderOfferAccepted($orderOffer));
                }
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке push-уведомления о принятии предложения: ' . $e->getMessage());
            }

            try {
                if (NotificationTypeUser::isEnabled($orderOffer->user_id, "orders", 'email')) {
                    /** @var User $offerAuthor */
                    $offerAuthor = $orderOffer->user()->first();
                    if ($email = $offerAuthor->getRawOriginal('email')) {
                        Mail::to($email)->later(now()->addSeconds($offerAuthor->getSilenceDelay()), new \App\Mail\OrderOfferAccepted($orderOffer));
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке email-уведомления о принятии предложения: ' . $e->getMessage(), [
                    'offer_id' => $orderOffer->id ?? null,
                    'user_id' => $orderOffer->user_id ?? null
                ]);
                // Не прерываем выполнение - предложение уже принято
            }

            return $this->emptySuccessResponse();
        } catch (\Exception $e) {
            \Log::error('Ошибка при принятии предложения: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'offer_id' => $orderOffer->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при принятии предложения: ' . $e->getMessage()
            ], 500);
        }
    }

    public function undoAccept(Order $order, OrderOffer $orderOffer): JsonResponse
    {
        $user = auth("sanctum")->user();
        if ($order->company_id !== $user->company_id) abort(403);

        $orderOffer->status = OrderOffer::STATUSES["WAITING"];
        $orderOffer->save();

        $order->silentApprove();

        return $this->emptySuccessResponse();
    }

    public function revert(Order $order, OrderOffer $orderOffer): JsonResponse
    {
        try {
            $user = auth("sanctum")->user();
            if ($order->company_id !== $user->company_id) abort(403);

            $orderOffer->status = OrderOffer::STATUSES["WAITING"];
            
            // Очищаем причину отказа при возврате предложения
            // Проверяем, существует ли поле decline_reason в таблице перед сохранением
            if (Schema::hasColumn('order_offers', 'decline_reason')) {
                $orderOffer->decline_reason = null;
            } else {
                \Log::warning('Поле decline_reason не существует в таблице order_offers. Выполните миграцию: php artisan migrate');
            }
            
            $orderOffer->save();

            // При возврате отклоненного предложения заявка автоматически переходит в статус "На согласовании"
            $order->setOnApproval();

            return $this->emptySuccessResponse();
        } catch (\Exception $e) {
            \Log::error('Ошибка при возврате предложения: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'offer_id' => $orderOffer->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при возврате предложения: ' . $e->getMessage()
            ], 500);
        }
    }

    public function decline(Order $order, OrderOffer $orderOffer, Request $request): JsonResponse
    {
        try {
            $user = auth("sanctum")->user();
            if ($order->company_id !== $user->company_id) abort(403);

            $request->validate([
                'decline_reason' => 'required|in:works_canceled,found_equipment,high_price,bad_terms,other'
            ]);

            $orderOffer->status = OrderOffer::STATUSES["DECLINED"];
            
            // Проверяем, существует ли поле decline_reason в таблице перед сохранением
            if (\Schema::hasColumn('order_offers', 'decline_reason')) {
                $orderOffer->decline_reason = $request->get('decline_reason');
            } else {
                \Log::warning('Поле decline_reason не существует в таблице order_offers. Выполните миграцию: php artisan migrate');
            }
            
            $orderOffer->save();

            // При отклонении предложения заявка автоматически переходит в статус "Новая заявка"
            $order->silentApprove();

            // Отправка уведомлений - оборачиваем в try-catch, чтобы ошибки отправки не блокировали процесс
            try {
                if (NotificationTypeUser::isEnabled($orderOffer->user_id, 'orders', 'push')) {
                    dispatch(new OrderOfferDeclined($orderOffer));
                }
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке push-уведомления об отклонении предложения: ' . $e->getMessage());
            }

            try {
                if (NotificationTypeUser::isEnabled($orderOffer->user_id, "orders", 'email')) {
                    /** @var User $offerAuthor */
                    $offerAuthor = $orderOffer->user()->first();
                    if ($email = $offerAuthor->getRawOriginal('email')) {
                        Mail::to($email)->later(now()->addSeconds($offerAuthor->getSilenceDelay()), new \App\Mail\OrderOfferDeclined($orderOffer));
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке email-уведомления об отклонении предложения: ' . $e->getMessage(), [
                    'offer_id' => $orderOffer->id ?? null,
                    'user_id' => $orderOffer->user_id ?? null
                ]);
                // Не прерываем выполнение - предложение уже отклонено
            }

            return $this->emptySuccessResponse();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Ошибка при отклонении предложения: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'offer_id' => $orderOffer->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при отклонении предложения: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return JsonResponse
     */
    public function destroy(Order $order, OrderOffer $orderOffer): JsonResponse
    {
        $orderOffer->delete();
        return $this->emptySuccessResponse();
    }

    /**
     * Генерация печатной формы заявки при принятии предложения
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return \Illuminate\Http\Response
     */
    public function print(Order $order, OrderOffer $orderOffer)
    {
        $user = auth("sanctum")->user();
        
        // Проверяем права: только заказчик может скачать печатную форму
        if ($order->company_id !== $user->company_id) {
            abort(403);
        }

        // Проверяем, что предложение принято
        if ($orderOffer->status !== OrderOffer::STATUSES["ACCEPTED"]) {
            abort(403, 'Предложение должно быть принято');
        }

        // Проверяем, существует ли уже печатная форма
        $printForm = $orderOffer->printForm()->first();
        
        if (!$printForm) {
            // Генерируем HTML печатной формы
            $html = $this->generatePrintFormHtml($order, $orderOffer);
            
            // Сохраняем HTML в S3 в отдельную директорию для документов предложений
            $fileName = "order-offer-documents/{$orderOffer->id}/print-form-" . time() . ".html";
            Storage::disk('s3')->put($fileName, $html, 'private');
            $url = Storage::disk('s3')->url($fileName);
            
            // Создаем запись в БД
            $printForm = OrderOfferDocument::create([
                'order_offer_id' => $orderOffer->id,
                'type' => OrderOfferDocument::TYPE_PRINT_FORM,
                'url' => $url,
                'file_name' => 'print-form.html',
                'file_size' => strlen($html),
                'mime_type' => 'text/html',
            ]);
        }

        // Возвращаем HTML для скачивания/просмотра
        $path = parse_url($printForm->url, PHP_URL_PATH);
        // Убираем начальный слэш, если есть
        $path = ltrim($path, '/');
        $content = Storage::disk('s3')->get($path);
        return response($content, 200)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="print-form.html"');
    }

    /**
     * Генерация договора (заявки) для принятого предложения — возвращает HTML (без PDF)
     */
    public function generateApplication(Order $order, OrderOffer $orderOffer)
    {
        $user = auth("sanctum")->user();
        if (!$user) {
            abort(401, 'Необходима авторизация');
        }
        if ($order->company_id !== $user->company_id) {
            abort(403, 'Нет доступа к этой заявке');
        }
        if ($orderOffer->status !== OrderOffer::STATUSES["ACCEPTED"]) {
            abort(403, 'Предложение должно быть принято');
        }

        $application = $orderOffer->application()->first();
        if ($application) {
            $path = parse_url($application->url, PHP_URL_PATH);
            $path = $path ? ltrim($path, '/') : '';
            if ($path && Storage::disk('s3')->exists($path)) {
                $content = Storage::disk('s3')->get($path);
                return response($content, 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('Content-Disposition', 'inline; filename="application-' . $order->id . '.html"');
            }
        }

        $html = $this->generateApplicationHtml($order, $orderOffer);
        return response($html, 200)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('Content-Disposition', 'inline; filename="application-' . $order->id . '.html"');
    }

    /**
     * Создание и сохранение документа договора (заявки) в формате HTML в S3 и БД (без PDF)
     */
    private function createApplicationDocument(Order $order, OrderOffer $orderOffer): OrderOfferDocument
    {
        $existing = $orderOffer->application()->first();
        if ($existing) {
            return $existing;
        }

        $html = $this->generateApplicationHtml($order, $orderOffer);
        if (empty($html)) {
            throw new \Exception('Не удалось сгенерировать HTML заявки');
        }

        $fileName = "order-offer-documents/{$orderOffer->id}/application-" . time() . ".html";
        Storage::disk('s3')->put($fileName, $html, 'private');
        $url = Storage::disk('s3')->url($fileName);

        return OrderOfferDocument::create([
            'order_offer_id' => $orderOffer->id,
            'type' => OrderOfferDocument::TYPE_APPLICATION,
            'url' => $url,
            'file_name' => 'application-' . $order->id . '.html',
            'file_size' => strlen($html),
            'mime_type' => 'text/html',
        ]);
    }

    /**
     * Генерация HTML договора (заявки) по образцу — структура как в шаблоне, данные подставляются из заявки
     */
    private function generateApplicationHtml(Order $order, OrderOffer $orderOffer): string
    {
        $order->load(['company', 'company.boss', 'vehicleType', 'addresses.city', 'addresses.region', 'user', 'formAnswers.question', 'paymentUnit']);
        $orderOffer->load(['company', 'company.boss', 'user']);

        $applicationNumber = (int)$order->id;
        $applicationDate = Carbon::parse($order->created_at);
        $applicationDateStr = $applicationDate->locale('ru')->translatedFormat('j F Y'); // "11 декабря 2025"

        $contractNumber = config('app.contract_number', '20251031/3');
        $contractDate = config('app.contract_date', '31.10.2025');

        // Данные исполнителя — ООО «МАСК Групп» как на образце
        $executorTitle = 'ООО "МАСК Групп"';
        $executorInn = '7731621944';
        $executorKpp = '772701001';
        $executorOgrn = '1097746010262';
        $executorAddress = '117638, Москва г. Одесская ул, дом 2, корпус С, помещение 20';
        $executorDirector = 'Лобода Виктор Иванович';

        $customerCompany = $order->company;
        $customerTitle = htmlspecialchars($customerCompany->full_title ?? $customerCompany->title ?? '');

        $formAnswers = $order->formAnswers()->with('question')->get()->keyBy(function ($a) {
            return $a->question->type ?? null;
        });
        $securityVal = $formAnswers->get('security');
        $securityText = ($securityVal && ($securityVal->value === 'true' || $securityVal->value === '1')) ? 'Есть' : 'нет';
        $livingVal = $formAnswers->get('living');
        $livingText = ($livingVal && ($livingVal->value === 'true' || $livingVal->value === '1')) ? 'да' : 'нет';

        $equipmentName = htmlspecialchars($order->vehicleType->title ?? '');
        $startDate = $order->start_date ? Carbon::parse($order->start_date)->format('d.m.Y') : '';
        $finishDate = $order->finish_date ? Carbon::parse($order->finish_date)->format('d.m.Y') : '';
        $startTime = $order->start_date ? Carbon::parse($order->start_date)->format('H:i:s') : '';

        $additionalInfo = htmlspecialchars($orderOffer->comment ?? '');
        $overtimeText = 'Можно перерабатывать';

        $deliveryAddress = '';
        $additionalAddress = '';
        if ($order->addresses && $order->addresses->count() > 0) {
            $first = $order->addresses->first();
            $regionName = $first->region->name ?? '';
            $cityName = $first->city->name ?? $first->city->title ?? '';
            $deliveryAddress = htmlspecialchars(trim($regionName . ', ' . $cityName . ', ' . ($first->address ?? '')));
            if ($order->addresses->count() > 1) {
                $second = $order->addresses->skip(1)->first();
                $additionalAddress = htmlspecialchars(($second->address ?? '') . ' ' . ($second->city->name ?? $second->city->title ?? ''));
            }
        }

        $customerContact = htmlspecialchars(trim(($order->user->name ?? '') . ' ' . ($order->user->surname ?? '')) . ', ' . ($order->user->phone ?? ''));
        $customerSignatory = htmlspecialchars(trim(($order->user->name ?? '') . ' ' . ($order->user->surname ?? '')) . ', ' . ($order->user->phone ?? ''));
        $executorManager = htmlspecialchars(trim(($orderOffer->user->name ?? '') . ' ' . ($orderOffer->user->surname ?? '')) . ', тел. ' . ($orderOffer->user->phone ?? ''));

        $serviceUnit = $order->paymentUnit ? $order->paymentUnit->name : 'ч';
        $serviceName = 'Услуга спецтехники (' . mb_strtolower($serviceUnit) . ') ' . $equipmentName . ' ' . $serviceUnit;
        $totalAmount = 0;
        $vatAmount = 0;
        $serviceQuantity = 0;
        $servicePrice = 0;
        if ($order->start_date && $order->finish_date) {
            $start = Carbon::parse($order->start_date);
            $finish = Carbon::parse($order->finish_date);
            if (stripos($serviceUnit, 'ч') !== false || stripos($serviceUnit, 'час') !== false) {
                $serviceQuantity = max(1, (int)$start->diffInHours($finish));
            } else {
                $serviceQuantity = max(1, (int)$start->diffInDays($finish) + 1);
            }
        } else {
            $serviceQuantity = 1;
        }
        if ($orderOffer->amount_account_vat) {
            $totalAmount = (float)$orderOffer->amount_account_vat;
            $vatAmount = round($totalAmount / 6, 2);
            $servicePrice = $serviceQuantity > 0 ? round($totalAmount / $serviceQuantity, 2) : $totalAmount;
        } elseif ($orderOffer->amount_account) {
            $totalAmount = (float)$orderOffer->amount_account;
            $vatAmount = round($totalAmount / 6, 2);
            $servicePrice = $serviceQuantity > 0 ? round($totalAmount / $serviceQuantity, 2) : $totalAmount;
        } elseif ($orderOffer->amount_cash) {
            $totalAmount = (float)$orderOffer->amount_cash;
            $vatAmount = round($totalAmount / 6, 2);
            $servicePrice = $serviceQuantity > 0 ? round($totalAmount / $serviceQuantity, 2) : $totalAmount;
        }

        $customerDirector = htmlspecialchars($customerCompany->director ?? '');

        $html = '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ЗАЯВКА № ' . $applicationNumber . '</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; font-size: 12px; }
        .top-row { display: flex; justify-content: space-between; margin-bottom: 15px; }
        .contract-ref { text-align: right; font-size: 11px; }
        .header { text-align: center; margin: 20px 0; }
        .header h1 { font-size: 18px; font-weight: bold; margin: 5px 0; }
        .header-meta { display: flex; justify-content: space-between; margin-top: 10px; }
        .intro { margin: 15px 0; }
        .field-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .field-table td { padding: 6px 10px; border: 1px solid #333; vertical-align: top; }
        .field-table .label { width: 45%; font-weight: bold; background: #f5f5f5; }
        .services-table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 11px; }
        .services-table td, .services-table th { padding: 6px; border: 1px solid #333; text-align: center; }
        .services-table .left { text-align: left; }
        .totals { font-weight: bold; }
        .footer-clause { margin: 20px 0 15px; font-size: 11px; }
        .signature-row { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-block { width: 48%; }
        .signature-block .company { font-weight: bold; margin-bottom: 5px; }
        .signature-block .details { font-size: 11px; margin: 5px 0; }
        .signature-name { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="top-row">
        <div>
            <strong>' . $executorTitle . '</strong><br>
            ИНН: ' . $executorInn . '<br>
            КПП: ' . $executorKpp . '<br>
            ОГРН: ' . $executorOgrn . '<br>
            Адрес: ' . $executorAddress . '
        </div>
        <div class="contract-ref">Приложение №1 к Договору на оказание услуг строительной техники №' . htmlspecialchars($contractNumber) . ' от ' . htmlspecialchars($contractDate) . '</div>
    </div>
    <div class="header">
        <h1>ЗАЯВКА № ' . $applicationNumber . '</h1>
        <div class="header-meta">
            <span>г.Москва</span>
            <span>от ' . $applicationDateStr . '</span>
        </div>
    </div>
    <div class="intro">
        ' . $executorTitle . ' обязуется предоставить услуги строительной техники, а ' . $customerTitle . ' обязуется принять и оплатить стоимость услуги строительной техники на следующих условиях:
    </div>
    <table class="field-table">
        <tr><td class="label">Наименование техники</td><td>' . $equipmentName . '</td></tr>
        <tr><td class="label">Дата начала работ</td><td>' . ($orderOffer->date_start ? Carbon::parse($orderOffer->date_start)->format('d.m.Y') : $startDate) . '</td></tr>
        <tr><td class="label">Дата окончания</td><td>' . $finishDate . '</td></tr>
        <tr><td class="label">Время с:</td><td>' . $startTime . '</td></tr>
        <tr><td class="label">Дополнительная информация (вид работ, условия)</td><td>' . $additionalInfo . '</td></tr>
        <tr><td class="label">Охрана на объекте:</td><td>' . $securityText . '</td></tr>
        <tr><td class="label">Проживание для машиниста/водителя:</td><td>' . $livingText . '</td></tr>
        <tr><td class="label">Адрес подачи техники:</td><td>' . $deliveryAddress . '</td></tr>
        <tr><td class="label">Дополнительный адрес:</td><td>' . $additionalAddress . '</td></tr>
        <tr><td class="label">Переработки:</td><td>' . $overtimeText . '</td></tr>
        <tr><td class="label">ФИО, телефон и E-mail ответственного сотрудника Заказчика:</td><td>' . $customerContact . '</td></tr>
        <tr><td class="label">ФИО и должность ответственного лица, имеющего право подписи документов на объекте от имени заказчика:</td><td>' . $customerSignatory . '</td></tr>
        <tr><td class="label">Менеджер Исполнителя, ответственный за Заявку:</td><td>' . $executorManager . '</td></tr>
    </table>
    <table class="services-table">
        <tr>
            <th>№</th>
            <th>Наименование услуги</th>
            <th>Ед</th>
            <th>Кол-во</th>
            <th>Цена</th>
            <th>Сумма</th>
        </tr>
        <tr>
            <td>1</td>
            <td class="left">' . $serviceName . '</td>
            <td>' . htmlspecialchars($serviceUnit) . '</td>
            <td>' . $serviceQuantity . '</td>
            <td>' . number_format($servicePrice, 2, ',', ' ') . '</td>
            <td>' . number_format($totalAmount, 2, ',', ' ') . '</td>
        </tr>
        <tr class="totals">
            <td colspan="4">Итого:</td>
            <td colspan="2">' . number_format($totalAmount, 2, ',', ' ') . '</td>
        </tr>
        <tr class="totals">
            <td colspan="4">В том числе НДС:</td>
            <td colspan="2">' . number_format($vatAmount, 2, ',', ' ') . '</td>
        </tr>
    </table>
    <div class="footer-clause">
        1. Настоящая заявка является неотъемлемой частью к Договору на оказание услуг строительной техники №' . htmlspecialchars($contractNumber) . ' от ' . htmlspecialchars($contractDate) . '
    </div>
    <div class="signature-row">
        <div class="signature-block">
            <div class="company">ИСПОЛНИТЕЛЬ</div>
            <div>Генеральный директор</div>
            <div>' . $executorTitle . '</div>
            <div class="details">ОГРН: ' . $executorOgrn . '</div>
            <div class="details">ИНН: ' . $executorInn . '</div>
            <div class="signature-name">/' . $executorDirector . '/</div>
        </div>
        <div class="signature-block">
            <div class="company">ЗАКАЗЧИК</div>
            <div>Подписи сторон:</div>
            <div>Генеральный директор</div>
            <div>' . $customerTitle . '</div>
            <div class="details">ОГРН: ' . htmlspecialchars($customerCompany->ogrn ?? '') . '</div>
            <div class="details">ИНН: ' . htmlspecialchars($customerCompany->inn ?? '') . '</div>
            <div class="signature-name">/' . $customerDirector . '/</div>
        </div>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Загрузка подписанного документа
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadSignedDocument(Order $order, OrderOffer $orderOffer, Request $request): JsonResponse
    {
        try {
            $user = auth("sanctum")->user();
            
            // Проверяем права: только заказчик может загрузить подписанный документ
            if ($order->company_id !== $user->company_id) {
                abort(403);
            }

            // Проверяем, что предложение принято
            if ($orderOffer->status !== OrderOffer::STATUSES["ACCEPTED"]) {
                abort(403, 'Предложение должно быть принято');
            }

            $request->validate([
                'url' => 'required|string',
                'file_name' => 'nullable|string',
                'file_size' => 'nullable|integer',
                'mime_type' => 'nullable|string',
            ]);

            // Удаляем старый подписанный документ, если есть
            $oldSignedDoc = $orderOffer->signedDocument()->first();
            if ($oldSignedDoc) {
                $oldSignedDoc->delete();
            }

            // Создаем новую запись
            $signedDocument = OrderOfferDocument::create([
                'order_offer_id' => $orderOffer->id,
                'type' => OrderOfferDocument::TYPE_SIGNED_DOCUMENT,
                'url' => $request->get('url'),
                'file_name' => $request->get('file_name'),
                'file_size' => $request->get('file_size'),
                'mime_type' => $request->get('mime_type'),
            ]);

            return $this->resourceItemResponse("document", $signedDocument);
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке подписанного документа: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'offer_id' => $orderOffer->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при загрузке документа: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Скачивание документа
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @param OrderOfferDocument $document
     * @return \Illuminate\Http\Response
     */
    public function downloadDocument(Order $order, OrderOffer $orderOffer, OrderOfferDocument $document)
    {
        // Проверяем права доступа
        $this->checkDocumentAccess($order, $orderOffer, $document);

        $path = parse_url($document->url, PHP_URL_PATH);
        // Убираем начальный слэш, если есть
        $path = ltrim($path, '/');
        $content = Storage::disk('s3')->get($path);
        
        return response($content, 200)
            ->header('Content-Type', $document->mime_type ?? 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . ($document->file_name ?? 'document') . '"');
    }

    /**
     * Просмотр документа (preview)
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @param OrderOfferDocument $document
     * @return \Illuminate\Http\Response
     */
    public function previewDocument(Order $order, OrderOffer $orderOffer, OrderOfferDocument $document)
    {
        // Проверяем права доступа
        $this->checkDocumentAccess($order, $orderOffer, $document);

        $path = parse_url($document->url, PHP_URL_PATH);
        // Убираем начальный слэш, если есть
        $path = ltrim($path, '/');
        $content = Storage::disk('s3')->get($path);
        
        return response($content, 200)
            ->header('Content-Type', $document->mime_type ?? 'application/octet-stream')
            ->header('Content-Disposition', 'inline; filename="' . ($document->file_name ?? 'document') . '"');
    }

    /**
     * Перевод заявки в работу
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return JsonResponse
     */
    public function setInProgress(Order $order, OrderOffer $orderOffer): JsonResponse
    {
        try {
            $user = auth("sanctum")->user();
            
            // Проверяем права: только поставщик может перевести заявку в работу
            if ($orderOffer->company_id !== $user->company_id) {
                abort(403);
            }

            // Проверяем, что предложение принято
            if ($orderOffer->status !== OrderOffer::STATUSES["ACCEPTED"]) {
                abort(403, 'Предложение должно быть принято');
            }

            // Проверяем, что подписанный документ загружен
            $signedDocument = $orderOffer->signedDocument()->first();
            if (!$signedDocument) {
                return response()->json([
                    'message' => 'Необходимо загрузить подписанный документ'
                ], 422);
            }

            // Переводим заявку в работу
            $order->setInProgress();

            return $this->emptySuccessResponse();
        } catch (\Exception $e) {
            Log::error('Ошибка при переводе заявки в работу: ' . $e->getMessage(), [
                'order_id' => $order->id ?? null,
                'offer_id' => $orderOffer->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Ошибка при переводе заявки в работу: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка прав доступа к документу
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @param OrderOfferDocument $document
     * @return void
     */
    private function checkDocumentAccess(Order $order, OrderOffer $orderOffer, OrderOfferDocument $document)
    {
        $user = auth("sanctum")->user();
        
        if (!$user) {
            abort(401);
        }
        
        // Проверяем, что документ принадлежит этому предложению
        if ($document->order_offer_id !== $orderOffer->id) {
            abort(404);
        }

        // Заказчик может просматривать документы своей заявки
        if ($order->company_id === $user->company_id) {
            return;
        }

        // Поставщик может просматривать документы своего предложения
        if ($orderOffer->company_id === $user->company_id) {
            return;
        }

        // Все зарегистрированные поставщики (не заказчики) могут просматривать документы
        if ($user->company && $user->company->company_type_id !== 3) {
            return;
        }

        abort(403);
    }

    /**
     * Генерация HTML печатной формы заявки
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return string
     */
    private function generatePrintFormHtml(Order $order, OrderOffer $orderOffer): string
    {
        $order->load(['company', 'vehicleType', 'addresses.city', 'user', 'formAnswers.question']);
        $orderOffer->load(['company', 'user']);

        $html = '<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Печатная форма заявки</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 16px; margin-bottom: 10px; }
        .field { margin-bottom: 8px; }
        .field-label { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table td { padding: 8px; border: 1px solid #ddd; }
        .signature-section { margin-top: 50px; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin-top: 50px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ЗАЯВКА</h1>
    </div>
    
    <div class="section">
        <div class="section-title">Информация о заявке</div>
        <div class="field"><span class="field-label">Номер заявки:</span> ' . $order->id . '</div>
        <div class="field"><span class="field-label">Название:</span> ' . htmlspecialchars($order->title ?? '') . '</div>
        <div class="field"><span class="field-label">Тип техники:</span> ' . htmlspecialchars($order->vehicleType->title ?? '') . '</div>
        <div class="field"><span class="field-label">Количество:</span> ' . ($order->vehicles_count ?? 1) . '</div>
    </div>
    
    <div class="section">
        <div class="section-title">Информация о заказчике</div>
        <div class="field"><span class="field-label">Компания:</span> ' . htmlspecialchars($order->company->title ?? '') . '</div>
        <div class="field"><span class="field-label">Контактное лицо:</span> ' . htmlspecialchars(($order->user->name ?? '') . ' ' . ($order->user->surname ?? '')) . '</div>
    </div>
    
    <div class="section">
        <div class="section-title">Информация о предложении</div>
        <div class="field"><span class="field-label">Компания-исполнитель:</span> ' . htmlspecialchars($orderOffer->company->title ?? '') . '</div>
        <div class="field"><span class="field-label">Контактное лицо:</span> ' . htmlspecialchars(($orderOffer->user->name ?? '') . ' ' . ($orderOffer->user->surname ?? '')) . '</div>';

        if ($orderOffer->amount_account_vat) {
            $html .= '<div class="field"><span class="field-label">Сумма по счету с НДС:</span> ' . number_format($orderOffer->amount_account_vat, 2, '.', ' ') . ' руб.</div>';
        }
        if ($orderOffer->amount_account) {
            $html .= '<div class="field"><span class="field-label">Сумма по счету:</span> ' . number_format($orderOffer->amount_account, 2, '.', ' ') . ' руб.</div>';
        }
        if ($orderOffer->amount_cash) {
            $html .= '<div class="field"><span class="field-label">Сумма наличными:</span> ' . number_format($orderOffer->amount_cash, 2, '.', ' ') . ' руб.</div>';
        }
        if ($orderOffer->date_start) {
            $html .= '<div class="field"><span class="field-label">Дата начала работ:</span> ' . date('d.m.Y', strtotime($orderOffer->date_start)) . '</div>';
        }

        $html .= '</div>';

        if ($order->addresses && $order->addresses->count() > 0) {
            $html .= '<div class="section">
                <div class="section-title">Адреса</div>
                <table>
                    <tr><td>Адрес</td><td>Город</td></tr>';
            foreach ($order->addresses as $address) {
                $html .= '<tr><td>' . htmlspecialchars($address->address ?? '') . '</td><td>' . htmlspecialchars($address->city->title ?? '') . '</td></tr>';
            }
            $html .= '</table></div>';
        }

        $html .= '<div class="signature-section">
        <div>Дата: ' . date('d.m.Y') . '</div>
        <div class="signature-line"></div>
        <div>Подпись заказчика</div>
    </div>
    
</body>
</html>';

        return $html;
    }

    /**
     * Преобразование числа в пропись (упрощенная версия)
     * @param float $number
     * @return string
     */
    private function numberToWords(float $number): string
    {
        $whole = (int)$number;
        $fraction = round(($number - $whole) * 100);
        
        $words = [
            0 => '', 1 => 'один', 2 => 'два', 3 => 'три', 4 => 'четыре', 5 => 'пять',
            6 => 'шесть', 7 => 'семь', 8 => 'восемь', 9 => 'девять', 10 => 'десять',
            11 => 'одиннадцать', 12 => 'двенадцать', 13 => 'тринадцать', 14 => 'четырнадцать',
            15 => 'пятнадцать', 16 => 'шестнадцать', 17 => 'семнадцать', 18 => 'восемнадцать',
            19 => 'девятнадцать', 20 => 'двадцать', 30 => 'тридцать', 40 => 'сорок',
            50 => 'пятьдесят', 60 => 'шестьдесят', 70 => 'семьдесят', 80 => 'восемьдесят',
            90 => 'девяносто', 100 => 'сто', 200 => 'двести', 300 => 'триста',
            400 => 'четыреста', 500 => 'пятьсот', 600 => 'шестьсот', 700 => 'семьсот',
            800 => 'восемьсот', 900 => 'девятьсот'
        ];
        
        if ($whole === 0) {
            $result = 'ноль';
        } else {
            $result = $this->convertNumberToWords($whole, $words);
        }
        
        $result .= ' руб.';
        
        if ($fraction > 0) {
            $result .= ' ' . $this->convertNumberToWords($fraction, $words) . ' коп.';
        }
        
        return mb_strtoupper(mb_substr($result, 0, 1)) . mb_substr($result, 1);
    }

    /**
     * Конвертация числа в слова
     * @param int $number
     * @param array $words
     * @return string
     */
    private function convertNumberToWords(int $number, array $words): string
    {
        if ($number === 0) {
            return '';
        }
        
        if ($number < 20) {
            return $words[$number];
        }
        
        if ($number < 100) {
            $tens = (int)($number / 10) * 10;
            $ones = $number % 10;
            return $words[$tens] . ($ones > 0 ? ' ' . $words[$ones] : '');
        }
        
        if ($number < 1000) {
            $hundreds = (int)($number / 100) * 100;
            $remainder = $number % 100;
            return $words[$hundreds] . ($remainder > 0 ? ' ' . $this->convertNumberToWords($remainder, $words) : '');
        }
        
        if ($number < 1000000) {
            $thousands = (int)($number / 1000);
            $remainder = $number % 1000;
            $thousandsWord = $this->getThousandsWord($thousands);
            return $thousandsWord . ($remainder > 0 ? ' ' . $this->convertNumberToWords($remainder, $words) : '');
        }
        
        return '';
    }

    /**
     * Получение слова для тысяч
     * @param int $number
     * @return string
     */
    private function getThousandsWord(int $number): string
    {
        $words = [
            1 => 'одна тысяча', 2 => 'две тысячи', 3 => 'три тысячи', 4 => 'четыре тысячи',
            5 => 'пять тысяч', 6 => 'шесть тысяч', 7 => 'семь тысяч', 8 => 'восемь тысяч',
            9 => 'девять тысяч'
        ];
        
        if ($number < 10) {
            return $words[$number] ?? '';
        }
        
        if ($number < 20) {
            return $this->convertNumberToWords($number, []) . ' тысяч';
        }
        
        $tens = (int)($number / 10) * 10;
        $ones = $number % 10;
        
        if ($ones === 1) {
            return $this->convertNumberToWords($tens, []) . ' одна тысяча';
        } elseif ($ones >= 2 && $ones <= 4) {
            return $this->convertNumberToWords($tens, []) . ' ' . $words[$ones];
        } else {
            return $this->convertNumberToWords($number, []) . ' тысяч';
        }
    }

    /**
     * Скачивание счета
     * @param Order $order
     * @param OrderOffer $orderOffer
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice(Order $order, OrderOffer $orderOffer)
    {
        $user = auth("sanctum")->user();
        
        // Проверяем права: заказчик и поставщик могут скачать счет
        $hasAccess = false;
        if ($order->company_id === $user->company_id) {
            $hasAccess = true; // Заказчик
        } elseif ($orderOffer->company_id === $user->company_id) {
            $hasAccess = true; // Поставщик
        }
        
        if (!$hasAccess) {
            abort(403);
        }

        // Проверяем, что предложение принято
        if ($orderOffer->status !== OrderOffer::STATUSES["ACCEPTED"]) {
            abort(403, 'Предложение должно быть принято');
        }

        // Проверяем, существует ли счет
        $invoice = $orderOffer->invoice()->first();
        if (!$invoice) {
            // Если счета нет, создаем его
            $invoice = $this->createInvoiceDocument($order, $orderOffer);
        }

        // Возвращаем PDF для скачивания
        $path = parse_url($invoice->url, PHP_URL_PATH);
        $path = ltrim($path, '/');
        $content = Storage::disk('s3')->get($path);
        
        return response($content, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="invoice-' . $order->id . '.pdf"');
    }
}
