<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VehicleType extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'vehicle_group_id','image','color','show_in_menu'];

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    public function publishedCompanies()
    {
        return $this->companies()->published();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(VehicleGroup::class, 'vehicle_group_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function activeOrders()
    {
        return $this->orders()->active();
    }

    public function questions()
    {
        return $this->hasMany(FormQuestion::class);
    }

    public function paymentUnits()
    {
        return $this->belongsToMany(PaymentUnit::class);
    }

    public function getPaymentUnitsIdAttribute()
    {
        return $this->paymentUnits()->pluck("payment_units.id");
    }
}
