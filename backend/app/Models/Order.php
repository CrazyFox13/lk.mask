<?php

namespace App\Models;

use App\Casts\DatetimeCast;
use App\Casts\Price;
use App\Jobs\AutoModerationOrder;
use App\Jobs\CompanyNotification\MultipleReportsOnOrder;
use App\Jobs\CompanyNotification\NewOrder;
use App\Jobs\CompanyNotification\OrderModerationFailed;
use App\Jobs\CompanyNotification\OrderModerationPassed;
use App\Jobs\CompanyNotification\OrderOnApproval;
use App\Jobs\CompanyNotification\OrderInProgress;
use App\Jobs\SendNewOrderEmailNotification;
use App\Jobs\SendTelegramPost;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    const MODERATION_STATUSES = [
        "DRAFT" => 'draft', // черновик
        "MODERATION" => 'moderation', // на модерации
        "ON_APPROVAL" => 'on_approval', // на согласовании
        "APPROVED" => 'approved', // одобрено
        "IN_PROGRESS" => 'in_progress', // в работе
        "CANCELED" => 'canceled', // отклонено
        "REMOVED" => 'removed', //снято с модерации
        "COMPLETED" => 'completed', //исполнитель найден
    ];

    const SHIFT_DURATION_DAYS = 1;

    protected $casts = [
        'start_date' => DatetimeCast::class,
        'publish_date' => DatetimeCast::class,
        'finish_date' => DatetimeCast::class,
        'created_at' => DatetimeCast::class,
        'updated_at' => DatetimeCast::class,
        'amount_account_vat' => Price::class,
        'amount_account' => Price::class,
        'amount_cash' => Price::class,
    ];

    const RECENTLY_COMPLETED_DELAY = 60 * 24; // Сколько висят в общ списке выполненные заявки (минут)
    const RECENTLY_REMOVED_DELAY = 60 * 24; // Сколько висят в общ списке снятые с публикации заявки (минут)
    const COMPLETE_DELAY = 1; // Сколько дней висят заявки по проществии start_date
    const AUTO_MODERATION_DELAY = 3;

    const COMMUNICATION_WAYS = ["any", "chat", "call"];

    public static function boot()
    {
        parent::boot();

        self::updating(function (Order $order) {
            if ($order->isDirty("moderation_status") && $order->moderation_status === self::MODERATION_STATUSES["APPROVED"]) {
                $order->publish_date = Carbon::now();
            }
        });
    }

    /**
     * Relations
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function publishedCompany()
    {
        return $this->company()->published();
    }

    public function favorites()
    {
        return $this->hasMany(FavoriteOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function addresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function startAddress()
    {
        return $this->hasOne(OrderAddress::class)->orderBy("order_addresses.id");
    }

    public function documents()
    {
        return $this->hasMany(OrderDocument::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function claim()
    {
        return $this->hasMany(Claim::class);
    }

    public function formAnswers()
    {
        return $this->hasMany(FormAnswer::class);
    }

    public function paymentUnit()
    {
        return $this->belongsTo(PaymentUnit::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(OrderOffer::class, "order_id");
    }

    public function companyOffer(): HasOne
    {
        return $this->hasOne(OrderOffer::class);
    }

    /**
     * Scopes
     */

    public function scopeFiltered(Builder $orders, Request $request): Builder
    {
        if ($request->has('status')) {
            switch ($request->get('status')) {
                case Order::MODERATION_STATUSES["DRAFT"]:
                    $orders = $orders->draft();
                    break;
                case Order::MODERATION_STATUSES["MODERATION"]:
                    $orders = $orders->moderation();
                    break;
                case Order::MODERATION_STATUSES["APPROVED"]:
                    $orders = $orders->active();
                    break;
                case Order::MODERATION_STATUSES["CANCELED"]:
                    $orders = $orders->canceled();
                    break;
                case Order::MODERATION_STATUSES["REMOVED"]:
                    $orders = $orders->removed();
                    break;
                case Order::MODERATION_STATUSES["COMPLETED"]:
                    $orders = $orders->completed();
                    break;
            }
        }

        if ($search = $request->get("search")) {
            $orders = $orders->where("title", "like", "%$search%");
        }

        if ($request->has("company_id")) {
            $orders = $orders->where("company_id", $request->get('company_id'));
        }

        if ($request->has("user_id")) {
            $orders = $orders->where("user_id", $request->get('user_id'));
        }

        if ($request->has("by_company_employees")) {
            $companyId = $request->get("by_company_employees");
            $orders = $orders->whereHas("user", function ($q) use ($companyId) {
                return $q->where("company_id", $companyId)->staff();
            });
        }

        if ($request->get("is_active")) {
            $orders = $orders->active();
        }

        if ($request->get("is_finished")) {
            $orders = $orders->finished();
        }

        if ($request->has('geo')) {
            $orders = $orders->inRadius(explode(',', $request->get('geo')));
        }

        if ($request->has('vehicle_types_id') && !$request->get("for_company_vehicles")) {
            $orders = $orders->withVehicles(explode(',', $request->get('vehicle_types_id')));
        }

        if ($request->get("for_company_vehicles")) {
            $company_id = $request->get("for_company_vehicles");
            $vehicles = VehicleType::query()->whereHas("companies", function ($q) use ($company_id) {
                $q->where("companies.id", "=", $company_id);
            })->pluck("vehicle_types.id")->toArray();

            if ($request->get('vehicle_types_id')) {
                $selectedIds = explode(',', $request->get('vehicle_types_id'));
                $vehicles = array_unique([...$vehicles, ...$selectedIds]);
            }

            $orders = $orders->withVehicles($vehicles);
        }

        if ($request->has('cities_id')) {
            $orders = $orders->inCities(explode(',', $request->get('cities_id')));
        }

        if ($request->has('regions_id')) {
            $orders = $orders->inRegions(explode(',', $request->get('regions_id')));
        }

        if ($request->has('shifts')) {
            $orders = $orders->whereShift($request->get('shifts'));
        }

        if ($request->has('date_range')) {
            $orders = $orders->inDateRange(explode(',', $request->get('date_range')));
        }

        if ($request->has('date')) {
            $orders = $orders->inDateRange([$request->get('date'), $request->get('date')]);
        }

        $orders = $orders->filterByAmount();


        if ($request->get('with_company')) {
            $orders = $orders->withCompany();
        }

        if ($offerUserID = $request->get("offered_user_id")) {
            $orders = $orders->whereHas("offers", function ($q) use ($offerUserID) {
                $q->where("order_offers.user_id", "=", $offerUserID);
            });
        } elseif ($offerCompanyID = $request->get("offered_company_id")) {
            $orders = $orders->whereHas("offers", function ($q) use ($offerCompanyID) {
                $q->where("order_offers.company_id", "=", $offerCompanyID);
            });

            if (auth("sanctum")->check()) {
                // Свои не надо показывать с сотрудниками
                $orders->whereDoesntHave("offers", function ($q) use ($offerUserID) {
                    $q->where("order_offers.user_id", "=", auth("sanctum")->id());
                });
            }
        }


        return $orders;
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where("moderation_status", self::MODERATION_STATUSES["DRAFT"]);
    }

    public function scopeModeration(Builder $query): Builder
    {
        return $query->where("moderation_status", self::MODERATION_STATUSES["MODERATION"]);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where("moderation_status", self::MODERATION_STATUSES["APPROVED"]);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->approved()
                ->orWhere(function (Builder $q) {
                    $q->removed()->visibleRemoved();
                })
                ->orWhere(function (Builder $q) {
                    $q->completed()->visibleCompleted();
                });
        });
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->whereIn("moderation_status", [self::MODERATION_STATUSES["COMPLETED"], self::MODERATION_STATUSES["REMOVED"]]);
    }

    public function scopeCanceled(Builder $query): Builder
    {
        return $query->where("moderation_status", self::MODERATION_STATUSES["CANCELED"]);
    }

    public function scopeRemoved(Builder $query): Builder
    {
        return $query->where("moderation_status", self::MODERATION_STATUSES["REMOVED"]);
    }

    public function scopeRecentlyRemoved(Builder $query)
    {
        return $query->whereNotNull("removed_at")
            ->where("removed_at", ">", Carbon::now()->subMinutes(self::RECENTLY_REMOVED_DELAY));
    }

    public function scopeVisibleRemoved(Builder $query)
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $q) {
                // или 180 минут назад была снята
                $q->recentlyRemoved();
            })->orWhere(function (Builder $q) {
                // или она у меня в избранном
                $q->favorite();
            });
        });
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where("moderation_status", self::MODERATION_STATUSES["COMPLETED"]);
    }

    public function scopeRecentlyCompleted(Builder $query)
    {
        return $query->whereNotNull("completed_at")
            ->where("completed_at", ">", Carbon::now()->subMinutes(self::RECENTLY_COMPLETED_DELAY));
    }

    public function scopeVisibleCompleted(Builder $query)
    {
        return $query->where(function (Builder $q) {
            $q->where(function (Builder $q) {
                // или 180 минут назад была снята
                $q->recentlyCompleted();
            })->orWhere(function (Builder $q) {
                // или она у меня в избранном
                $q->favorite();
            });
        });
    }

    public function scopeVisible(Builder $query): Builder
    {
        $user = auth('sanctum')->user();
        if ($user && ($user->isAdmin() || $user->isModerator())) {
            return $query;
        }
        $cid = $user?->company_id ?: 0;
        return $query->where(function ($q) use ($cid) {
            if (request()->input("is_finished")) {
                $q->finished();

            } else {
                $q->active();
            }
            $q->orWhere("company_id", $cid)
                ->orWhere("user_id", auth('sanctum')->id());
        });
    }

    public function scopeInRadius(Builder $query, array $geo): Builder
    {
        list($lat, $lng, $radius) = $geo;
        if (!$lat || !$lng) return $query->where("id", 0); // dont return all!
        $radius = $radius / 1000; # to km;
        $haversine = "(6371 * acos(cos(radians($lat))
                    * cos(radians(`lat`))
                    * cos(radians(`lng`)
                    - radians($lng))
                    + sin(radians($lat))
                    * sin(radians(`lat`))))";

        return $query->whereHas("addresses", function (Builder $query) use ($haversine, $radius) {
            $query->whereRaw("{$haversine} < ?", [$radius]);
        });
    }

    public function scopeWithVehicles(Builder $query, array $vehicleTypesId): Builder
    {
        if (count($vehicleTypesId) === 0) {
            return $query;
        }
        return $query->whereIn("vehicle_type_id", $vehicleTypesId);
    }

    public function scopeInCities(Builder $query, array $citiesId): Builder
    {
        if (count($citiesId) === 0) {
            return $query;
        }

        return $query->whereHas("addresses", function (Builder $q) use ($citiesId) {
            $q->whereIn("geo_city_id", $citiesId);
        });
    }

    public function scopeInRegions(Builder $query, array $regionsIds): Builder
    {
        if (count($regionsIds) === 0) {
            return $query;
        }

        return $query->whereHas("addresses", function (Builder $q) use ($regionsIds) {
            $q->whereIn("geo_region_id", $regionsIds);
        });
    }

    public function scopeWhereShift(Builder $query, string $key): Builder
    {
        $symbol = "=";
        $daysCount = 0;

        switch ($key) {
            case "one":
                $daysCount = self::SHIFT_DURATION_DAYS;
                break;
            case "two":
                $daysCount = self::SHIFT_DURATION_DAYS * 2;
                break;
            case "less_five":
                $daysCount = self::SHIFT_DURATION_DAYS * 5;
                $symbol = "<";
                break;
            case "more_five":
                $daysCount = self::SHIFT_DURATION_DAYS * 5;
                $symbol = ">";
                break;
            default:
        }

        if (!$daysCount) {
            return $query;
        }

        return $query->whereRaw("DATEDIFF(finish_date, start_date) $symbol $daysCount");
    }

    /**
     * @param Builder $query
     * @param array $dateRange [2022-01-01, 2022-02-01]
     * @return Builder
     */
    public function scopeInDateRange(Builder $query, array $dateRange): Builder
    {
        return $query->where("start_date", ">=", Carbon::parse($dateRange[0])->startOfDay())
            ->where("start_date", "<=", Carbon::parse($dateRange[1])->endOfDay());
    }

    public function scopeWithAgreementAmount(Builder $query): Builder
    {
        return $query->where('amount_by_agreement', true);
    }

    public function scopeWithVATAmount(Builder $query): Builder
    {
        return $query->whereNotNull('amount_account_vat')->where("amount_account_vat", ">", 0);
    }

    public function scopeWithCashAmount(Builder $query): Builder
    {
        return $query->whereNotNull('amount_cash')->where("amount_cash", ">", 0);
    }

    public function scopeFilterByAmount(Builder $builder): Builder
    {
        $agreement = (int)request()->input('amount_by_agreement');
        $vat = (int)request()->input('amount_with_vat');
        $cash = (int)request()->input('amount_cash');

        $amountFilterCount = $agreement + $vat + $cash;

        switch ($amountFilterCount) {
            case 2:
                if (!$agreement) {
                    return $builder->where(function (Builder $q) {
                        $q->withVATAmount()->orWhere("amount_cash", ">", 0);
                    });
                } else if (!$vat) {
                    return $builder->withAgreementAmount()->orWhere("amount_cash", ">", 0);
                } else {
                    return $builder->withAgreementAmount()->orWhere("amount_account_vat", ">", 0);
                }
            case 1:
                if ($agreement) {
                    return $builder->withAgreementAmount();
                } elseif ($vat) {
                    return $builder->withVATAmount();
                } else {
                    return $builder->withCashAmount();
                }
        }
        return $builder;
    }

    public function scopeWithCompany(Builder $query): Builder
    {
        return $query->whereHas("publishedCompany");
    }

    public function scopeFavorite(Builder $query): Builder
    {
        return $query->whereHas('favorites', function (Builder $query) {
            $query->where('favorite_orders.user_id', auth('sanctum')->id());
        });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where("start_date", "<", Carbon::now()->subDays(self::COMPLETE_DELAY));
    }

    /**
     * Accessors, Mutators
     */

    public function getIsFavoriteAttribute()
    {
        if (!auth('sanctum')->check()) return false;
        return FavoriteOrder::query()->where("order_id", $this->id)
            ->where("user_id", auth('sanctum')->id())->exists();
    }

    public function getIsViewedAttribute()
    {
        if (!auth('sanctum')->check()) return false;
        return DB::table('order_views')->where("order_id", $this->id)
            ->where("user_id", auth('sanctum')->id())->exists();
    }

    /**
     *  Methods
     */

    public function setAnswers($answers)
    {
        if (empty($answers) || !is_array($answers)) {
            return;
        }
        
        $order = $this;
        $data = array_map(function ($answer) use ($order) {
            return [
                'form_question_id' => $answer['form_question_id'],
                'order_id' => $order->id,
                'value' => $answer['value'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ];
        }, $answers);
        $existingAnswers = $this->formAnswers()->get();

        DB::transaction(function () use ($data, $existingAnswers, $order) {
            foreach ($data as $row) {
                $answer = $existingAnswers->where('form_question_id', $row['form_question_id'])->first();
                if ($answer) {
                    FormAnswer::query()
                        ->where("form_question_id", $row['form_question_id'])
                        ->where("order_id", $order->id)
                        ->update([
                            'value' => $row['value'],
                            'updated_at' => $row['updated_at']
                        ]);
                } else {
                    FormAnswer::query()->insert($row);
                }
            }
        });
    }

    public function setAddresses($addresses)
    {
        if (empty($addresses) || !is_array($addresses)) {
            return;
        }
        
        $order = $this;
        $data = array_map(function ($address) use ($order) {
            return [
                'order_id' => $order->id,
                'lat' => $address['lat'],
                'lng' => $address['lng'],
                'address' => $address['address'],
                'fias_id' => $address['fias_id'] ?? null,
                'region_id' => $address['region_id'] ?? null,
                'region' => $address['region'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ];
        }, $addresses);

        $uniqueValues = array_map(function ($address) {
            return '"' . $address['lat'] . $address['lng'] . '"';
        }, $addresses);

        $uniqueValuesSQL = implode(",", $uniqueValues);
        
        if (!empty($uniqueValuesSQL)) {
            $this->addresses()->whereRaw("CONCAT(`lat`,`lng`) not in ($uniqueValuesSQL)")
                ->where("order_id", $this->id)
                ->delete();
        }

        $existedAddresses = $this->addresses()->get();

        DB::transaction(function () use ($data, $existedAddresses, $order) {

            foreach ($data as $row) {
                $city = GeoCity::query()->where("fias_id", $row['fias_id'])->first();
                if ($city) {
                    $row['geo_city_id'] = $city->id;
                    $row['geo_region_id'] = $city->geo_region_id;
                }

                $existedAddress = $existedAddresses->where('lat', $row['lat'])
                    ->where('lng', $row['lng'])
                    ->first();
                if ($existedAddress) {
                    OrderAddress::query()
                        ->where("lat", $row['lat'])
                        ->where("lng", $row['lng'])
                        ->update([
                            'address' => $row['address'],
                            'geo_city_id' => $row['geo_city_id'] ?? null,
                            'geo_region_id' => $row['geo_region_id'] ?? null,
                            'updated_at' => $row['updated_at']
                        ]);
                } else {
                    OrderAddress::query()->insert([
                        'order_id' => $order->id,
                        'lat' => $row['lat'],
                        'lng' => $row['lng'],
                        'address' => $row['address'],
                        'geo_city_id' => $row['geo_city_id'] ?? null,
                        'geo_region_id' => $row['geo_region_id'] ?? null,
                    ]);
                }
            }
        });
    }

    public function setDocuments($documents)
    {
        $order = $this;
        $data = array_map(function ($document) use ($order) {
            return [
                'order_id' => $order->id,
                'url' => $document['url'],
                'type' => $document['type'],
                'file_size' => $document['file_size'] ?? null,
                'mime_type' => $document['mime_type'] ?? null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()

            ];
        }, $documents);

        $docsToDelete = $this->documents()
            ->whereNotIn('url', array_column($data, 'url'))
            ->where("order_id", $this->id)
            ->get();

        DB::transaction(function () use ($data, $order, $docsToDelete) {

            $docsToDelete->each(function (OrderDocument $document) {
                $document->delete();
            });

            $existedDocuments = $order->documents()->get();
            foreach ($data as $row) {
                $existedDocument = $existedDocuments->where('url', $row['url'])->first();
                if ($existedDocument) {
                    OrderDocument::query()
                        ->where('order_id', $order->id)
                        ->where("url", $row['url'])
                        ->update([
                            'type' => $row['type'],
                            'file_size' => $row['file_size'] ?? null,
                            'mime_type' => $row['mime_type'] ?? null,
                            'updated_at' => $row['updated_at']
                        ]);
                } else {
                    OrderDocument::query()->insert($row);
                }
            }
        });
    }

    public function generateTitle()
    {
        try {
            // Загружаем vehicleType если он еще не загружен
            if (!$this->relationLoaded('vehicleType')) {
                $this->load('vehicleType');
            }
            $type = $this->vehicleType;
            $title = $type ? $type->title : ($this->title ?? 'Заявка');
            $count = $this->vehicles_count ?? 1;

            $answers = $this->formAnswers()
                ->where(function ($q) {
                    $q->where("value", "!=", "false")
                        ->orWhereHas("question", function ($q) {
                            $q->whereNotIn('type', ['checkbox', 'orv']);
                        });
                })
                ->whereHas('question', function ($q) {
                    $q->whereNotIn('type', ['security', 'living']);
                })
                ->with('question')
                ->get()
                ->sortBy("question.order")
                ->values()
                ->map(function (FormAnswer $formAnswer) {
                    return $formAnswer->getLabel();
                })->implode(" ");

            $this->title = trim("$title $answers");
            if ($count > 1) {
                $this->title .= " $count шт.";
            }
            $this->save();
        } catch (\Exception $e) {
            Log::error('Ошибка при генерации заголовка заявки: ' . $e->getMessage(), [
                'order_id' => $this->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            // Если произошла ошибка, оставляем текущий заголовок без изменений
        }
    }

    public function makeDraft()
    {
        $this->moderation_status = self::MODERATION_STATUSES["DRAFT"];
        $this->moderation_message = null;
        $this->save();
    }

    public function sendOnModeration($afterClaim = false)
    {
        $company = $this->company()->first();
        if ($company && $company->instant_moderation) {
            $this->passModeration();
        } else {
            $this->moderation_status = self::MODERATION_STATUSES["MODERATION"];
            $this->moderation_message = null;

            if (!$afterClaim) {
                $this->sent_on_moderation_at = Carbon::now();
            }

            $this->save();
        }
    }

    public function passModeration()
    {
        $this->moderation_status = self::MODERATION_STATUSES["APPROVED"];
        $this->moderation_message = null;
        $this->save();

        $this->resetClaimCounter();
        try {
            dispatch(new OrderModerationPassed($this));
            dispatch(new NewOrder($this));
            dispatch(new SendNewOrderEmailNotification($this));
            if (config('app.env') === "production") {
                dispatch(new SendTelegramPost($this));
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке задач в очередь: ' . $e->getMessage());
        }
    }

    public function silentApprove()
    {
        $this->moderation_status = self::MODERATION_STATUSES["APPROVED"];
        $this->moderation_message = null;
        $this->completed_at = null;
        $this->save();
    }

    public function failModeration(string $msg = null, string $cancelReason = null)
    {
        $this->moderation_status = self::MODERATION_STATUSES["CANCELED"];
        $this->moderation_message = $msg;
        
        // Проверяем, существует ли поле cancel_reason в таблице перед сохранением
        if ($cancelReason && \Schema::hasColumn($this->getTable(), 'cancel_reason')) {
            $this->cancel_reason = $cancelReason;
        } elseif ($cancelReason) {
            \Log::warning('Поле cancel_reason не существует в таблице orders. Выполните миграцию.');
        }
        
        try {
            $this->save();
        } catch (\Exception $e) {
            \Log::error('Ошибка при сохранении заявки при отмене: ' . $e->getMessage(), [
                'order_id' => $this->id ?? null,
                'cancel_reason' => $cancelReason,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        try {
            dispatch(new OrderModerationFailed($this));
        } catch (\Exception $e) {
            \Log::error('Ошибка при отправке задачи в очередь: ' . $e->getMessage());
        }
    }

    public function remove()
    {
        $this->moderation_status = self::MODERATION_STATUSES["REMOVED"];
        $this->removed_at = Carbon::now();
        $this->save();
    }

    public function complete()
    {
        $this->moderation_status = self::MODERATION_STATUSES["COMPLETED"];
        $this->completed_at = Carbon::now();
        $this->save();
    }

    public function setOnApproval()
    {
        $this->moderation_status = self::MODERATION_STATUSES["ON_APPROVAL"];
        $this->moderation_message = null;
        $this->save();

        try {
            dispatch(new OrderOnApproval($this));
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке уведомления о согласовании: ' . $e->getMessage());
        }
    }

    public function setInProgress()
    {
        $this->moderation_status = self::MODERATION_STATUSES["IN_PROGRESS"];
        $this->moderation_message = null;
        $this->save();

        try {
            dispatch(new OrderInProgress($this));
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке уведомления о работе: ' . $e->getMessage());
        }
    }

    public function inFavorite(): bool
    {
        return FavoriteOrder::query()->where("user_id", auth('sanctum')->id())
            ->where("order_id", $this->id)
            ->exists();
    }

    public function removeFromFavorite(): bool
    {
        FavoriteOrder::query()->where("user_id", auth('sanctum')->id())
            ->where("order_id", $this->id)
            ->delete();
        return false;

    }

    public function addToFavorite(): bool
    {
        $this->removeFromFavorite();
        FavoriteOrder::query()->insert([
            'user_id' => auth('sanctum')->id(),
            'order_id' => $this->id,
        ]);
        return true;
    }

    public function view()
    {
        try {
            $user = auth('sanctum')->user();
            if (!$user) {
                // Если пользователь не авторизован, просто увеличиваем счетчик просмотров
                $this->views_count++;
                $this->save();
                return;
            }

            // Проверяем, не просматривал ли уже пользователь эту заявку
            $viewed = DB::table("order_views")
                ->where("order_id", $this->id)
                ->where("user_id", $user->id)
                ->first();
            
            if ($viewed) {
                // Если уже просматривал, не увеличиваем счетчик
                return;
            }

            // Увеличиваем счетчик просмотров
            $this->views_count++;
            $this->save();

            // Записываем факт просмотра
            try {
                DB::table("order_views")->insert([
                    'order_id' => $this->id,
                    'user_id' => $user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            } catch (\Exception $e) {
                // Игнорируем ошибку дублирования записи
                Log::warning('Ошибка при записи просмотра заявки: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем выполнение
            Log::warning('Ошибка в методе view(): ' . $e->getMessage());
        }
    }

    public function increaseClaimCounter()
    {
        $this->claim_counter++;
        $this->save();

        if ($this->claim_counter >= 2) {
            $this->sendOnModeration(true);
            try {
                dispatch(new MultipleReportsOnOrder($this));
            } catch (\Exception $e) {
                \Log::error('Ошибка при отправке задачи в очередь: ' . $e->getMessage());
            }
        }
    }

    public function resetClaimCounter()
    {
        $this->claim_counter = 0;
        $this->save();
    }
}
