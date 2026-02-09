<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'region_id'];

    protected $hidden=['title'];

    protected $appends=['name'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function orderAddresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function getNameAttribute(){
        return $this->title;
    }

    public static function findOrCreateByTitle(string $title, $regionId = null): City
    {
        $model = City::where("title", "like", "%$title%");

        if ($regionId) {
            $model = $model->where("region_id", $regionId);
        }

        $model = $model->first();
        if (!$model) {
            $model = new City(['title' => $title, 'region_id' => $regionId]);
            $model->save();
        }
        return $model;
    }
}
