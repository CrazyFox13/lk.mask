<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type'];

    protected $hidden=['title'];

    protected $appends=['name'];

    public function cities()
    {
        return $this->hasMany(City::class);
    }

    public function orderAddresses()
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function getNameAttribute(){
        return $this->title;
    }

    public static function findOrCreateByTitle(string $title): Region
    {
        $region = Region::query()->where("title", "like", "%$title%")->first();
        if (!$region) {
            $region = new Region(['title' => $title]);
            $region->save();
        }
        return $region;
    }
}
