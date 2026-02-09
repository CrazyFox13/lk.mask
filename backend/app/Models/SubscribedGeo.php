<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscribedGeo extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'geo_city_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function city()
    {
        return $this->belongsTo(GeoCity::class);
    }
}
