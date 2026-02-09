<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class VehicleCategory extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image', 'color', 'show_in_menu'];

    public function groups(): HasMany
    {
        return $this->hasMany(VehicleGroup::class);
    }

    public function types(): HasManyThrough
    {
        return $this->hasManyThrough(VehicleType::class, VehicleGroup::class);
    }
}
