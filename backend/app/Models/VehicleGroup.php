<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleGroup extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'logo','image','color','show_in_menu'];

    public function types():HasMany
    {
        return $this->hasMany(VehicleType::class);
    }

    public function category():BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class,'vehicle_category_id');
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, VehicleType::class);
    }

    public function activeOrders()
    {
        return $this->orders()->active();
    }

    public function getPublishedCompaniesCountAttribute()
    {
        $group = $this;
        return Company::query()->published()->whereHas('vehicleTypes', function ($q) use ($group) {
            $q->where("vehicle_group_id", $group->id);
        })->count();
    }
}
