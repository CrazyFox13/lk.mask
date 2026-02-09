<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    use HasFactory;

    public function region()
    {
        return $this->belongsTo(GeoRegion::class,'geo_region_id');
    }

    public function city()
    {
        return $this->belongsTo(GeoCity::class,'geo_city_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('order', function ($q) {
            $q->active();
        });
    }
}
