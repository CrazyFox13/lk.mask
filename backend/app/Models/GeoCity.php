<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class GeoCity extends Model
{
    use HasFactory;

    protected $fillable = ['geo_region_id', 'name', 'postal_code', 'timezone', 'lat', 'lng', 'fias_id'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope('named', function (Builder $builder) {
            $builder->where(function ($q) {
                $q->where('name', '!=', '')->orWhereIn("geo_region_id", [77, 78, 85]);
            });
        });
    }

    public function region()
    {
        return $this->belongsTo(GeoRegion::class, 'geo_region_id');
    }

    public function scopeSearch($query, $needle)
    {
        return $query->where(function ($q) use ($needle) {
            $q->where("name", "like", "%$needle%")
                ->orWhere(function ($q) use ($needle) {
                    $q->where("name", "")->whereHas('region', function ($q) use ($needle) {
                        $q->where("name", "like", "%$needle%");
                    });
                });
        });
    }

    public function getNameAttribute($value)
    {
        if ($value) return $value;
        return $this->region->name;
    }
}
