<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class AdvBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'advertiser_id', 'adv_place_id', 'is_active',
        'title', 'tooltip', 'start_date', 'end_date', 'img_url',
        'endpoint_url', 'comment', 'token'
    ];

    protected $appends = ['ctr'];

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    public function place()
    {
        return $this->belongsTo(AdvPlace::class, 'adv_place_id');
    }

    public function vehicleTypes()
    {
        return $this->belongsToMany(VehicleType::class);
    }

    public function sessions()
    {
        return $this->belongsToMany(UserSession::class)->withPivot(['clicked_at', 'created_at']);
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

    public function scopeForPlace($q, string $place)
    {
        return $q->whereHas("place", function ($q) use ($place) {
            $q->where("adv_places.key", $place);
        });
    }

    public function scopeWithEnabledAdvertiser($q)
    {
        return $q->whereHas("advertiser", function ($q) {
            $q->active()->running();
        });
    }

    public function scopeForTypes($q, array $vehicleTypes)
    {
        return $q->whereHas("vehicleTypes", function ($q) use ($vehicleTypes) {
            $q->whereIn("vehicle_types.id", $vehicleTypes);
        });
    }

    public function scopeSearch($q, string $search)
    {
        return $q->where("title", "like", "%$search%");
    }

    public function getCtrAttribute()
    {
        if (!$this->views) return 0;
        return round($this->clicks / $this->views, 2);
    }

    public function view()
    {
        $session = UserSession::findOrCreate();
        if (!$this->sessions()->find($session->id)) {
            $this->sessions()->attach($session->id, ['created_at' => Carbon::now()]);
            $this->views++;
            $this->save();
        }
    }

    public function click()
    {
        $session = UserSession::findOrCreate();
        if ($session = $this->sessions()->find($session->id)) {
            if (!$session->pivot->clicked_at) {
                $this->sessions()->updateExistingPivot($session->id, ['clicked_at' => Carbon::now()]);
                $this->clicks++;
                $this->save();
            }
        } else {
            $this->sessions()->attach($session->id, ['clicked_at' => Carbon::now(), 'created_at' => Carbon::now()]);
            $this->clicks++;
            $this->save();
        }
    }

    public function getShowingAttribute()
    {
        return $this->is_active &&
            $this->start_date <= Carbon::now()->startOfDay() &&
            $this->end_date >= Carbon::now()->endOfDay() &&
            $this->advertiser?->is_active;
    }
}
