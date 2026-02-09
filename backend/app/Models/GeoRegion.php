<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoRegion extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_with_type', 'type', 'federal_district', 'postal_code', 'timezone', 'geoname_id', 'fias_id'];

    public function cities()
    {
        return $this->hasMany(GeoCity::class);
    }

    public function scopeSearch($query, $needle)
    {
        return $query->where(function ($q) use ($needle) {
            $q->whereHas("cities", function ($q) use ($needle) {
                $q->where("name", "like", "%$needle%");
            })->orWhere(function ($q) use ($needle) {
                $q->where("name", "like", "%$needle%")
                    ->whereHas("cities", function ($q) {
                        $q->where("name", "");
                    });
            });
        });
    }
}
