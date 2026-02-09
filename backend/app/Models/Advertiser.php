<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertiser extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'inn', 'is_active', 'start_date', 'end_date'];

    public function banners()
    {
        return $this->hasMany(AdvBanner::class);
    }

    public function scopeSearch($q, string $needle)
    {
        return $q->where(function ($q) use ($needle) {
            $q->where("name", "like", "%$needle%")
                ->orWhere("inn", "like", "%$needle%");
        });
    }

    public function scopeActive($q)
    {
        return $q->where("is_active", true);
    }

    public function scopeRunning($q)
    {
        return $q->where("start_date", '<=', Carbon::now()->startOfDay())
            ->where("end_date", '>=', Carbon::now()->endOfDay());
    }
}
