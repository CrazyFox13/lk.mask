<?php

namespace App\Models;

use App\Casts\Price;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderOffer extends Model
{
    use HasFactory;

    const STATUSES = [
        "WAITING" => "waiting",
        "ACCEPTED" => "accepted",
        "DECLINED" => "declined",
    ];

    protected $casts = [
        'date_start' => 'date',
        'viewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'amount_account_vat' => Price::class,
        'amount_account' => Price::class,
        'amount_cash' => Price::class,
    ];

    protected $fillable = [
        "order_id", "user_id", "company_id", "amount_account_vat", "amount_account", "amount_cash", "date_start", "comment", "decline_reason"
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(OrderOfferDocument::class);
    }

    public function printForm()
    {
        return $this->hasOne(OrderOfferDocument::class)->where('type', OrderOfferDocument::TYPE_PRINT_FORM);
    }

    public function signedDocument()
    {
        return $this->hasOne(OrderOfferDocument::class)->where('type', OrderOfferDocument::TYPE_SIGNED_DOCUMENT);
    }

    public function application()
    {
        return $this->hasOne(OrderOfferDocument::class)->where('type', OrderOfferDocument::TYPE_APPLICATION);
    }

    public function invoice()
    {
        return $this->hasOne(OrderOfferDocument::class)->where('type', OrderOfferDocument::TYPE_INVOICE);
    }

    public function scopeNew($query, int|null $companyID)
    {
        if (!$companyID) {
            return $query->whereRaw("1 <> 1");
        }
        return $query->whereHas("order", function ($q) use ($companyID) {
            $q->where('orders.company_id', $companyID);
        })->whereNull('viewed_at');
    }

    public function scopeCompany($q, int|null $companyID)
    {
        if (!$companyID) {
            $userId = auth("sanctum")->id();
            if ($userId) {
                return $q->where("user_id", "=", $userId);
            }
            // Если пользователь не авторизован, возвращаем пустой результат
            return $q->whereRaw("1 <> 1");
        }
        return $q->where("company_id", "=", $companyID);
    }

    public function scopeNotDeclined($q)
    {
        return $q->whereIn("status", [self::STATUSES["WAITING"], self::STATUSES["ACCEPTED"]]);
    }
}
