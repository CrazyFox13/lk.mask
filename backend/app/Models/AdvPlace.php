<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvPlace extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'name', 'width', 'height', 'with_vehicle_filter'];

    public function banners()
    {
        return $this->hasMany(AdvBanner::class);
    }
}
