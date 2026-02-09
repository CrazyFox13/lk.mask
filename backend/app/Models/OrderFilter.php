<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class OrderFilter extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'active_email', 'active_push', 'query'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActiveEmail($q)
    {
        return $q->where("active_email", true);
    }

    public function scopeActivePush($q)
    {
        return $q->where("active_push", true);
    }

    public function scopePasses($q, Order $order)
    {
        # vehicle_types_id
        $q->where(function ($q) use ($order) {
            $q->whereJsonContains('query->vehicle_types_id', $order->vehicle_type_id)
                ->orWhereNull('query->vehicle_types_id');
        });

        # cities_id
        $q->where(function ($q) use ($order) {
            $q->whereJsonContains('query->cities_id', $order->addresses()->pluck('city_id'))
                ->orWhereNull('query->cities_id');
        });

        # regions_id
        $q->where(function ($q) use ($order) {
            $q->whereJsonContains('query->regions_id', $order->addresses()->pluck('region_id'))
                ->orWhereNull('query->regions_id');
        });

        # shifts
        ## one,two,less_five,more_five

        $q->where(function ($q) use ($order) {
            $dateDiff = Carbon::parse($order->finish_date)->diffInDays(Carbon::parse($order->start_date));
            switch (true) {
                case $dateDiff === 1:
                    $q->where('query->shifts', 'one')->orWhere('query->shifts', 'less_five');
                    break;
                case $dateDiff === 2:
                    $q->where('query->shifts', 'two')->orWhere('query->shifts', 'less_five');
                    break;
                case $dateDiff > 5:
                    $q->where('query->shifts', 'more_five');
                    break;
                case $dateDiff < 5:
                    $q->where('query->shifts', 'less_five');
                    break;
            }
            $q->orWhereNull('query->shifts');
        });

        # 2023-04-06 05:00:00

        # date_range
        $q->where(function ($q) use ($order) {
            $q->where(function ($q) use ($order) {
                $q->where('query->date_range[0]', '<', $order->start_date)
                    ->where('query->date_range[1]', '>', $order->start_date);
            });
            $q->orWhereNull('query->date_range');
        });

        # date
        $q->where(function ($q) use ($order) {
            $q->where('query->date', '=', Carbon::parse($order->start_date)->format("Y-m-d"));
            $q->orWhereNull('query->date');
        });

        # amount_by_agreement
        $q->where(function ($q) use ($order) {
            $q->where('query->amount_by_agreement', '=', $order->amount_by_agreement);
            $q->orWhereNull('query->amount_by_agreement');
        });

        # amount_with_vat
        $q->where(function ($q) use ($order) {
            $q->where('query->amount_with_vat', '=', $order->amount_with_vat ? 1 : 0);
            $q->orWhereNull('query->amount_with_vat');
        });

        # amount_cash
        $q->where(function ($q) use ($order) {
            $q->where('query->amount_cash', '=', $order->amount_cash ? 1 : 0);
            $q->orWhereNull('query->amount_cash');
        });

        # with_company
        $q->where(function ($q) use ($order) {
            $q->where('query->with_company', '=', $order->company_id ? 1 : 0);
            $q->orWhereNull('query->with_company');
        });

        return $q;
    }

    public function setQueryAttribute($v)
    {
        if ($query = json_decode($v, true)) {
            unset($query['page']);
            unset($query['take']);
            unset($query['sort_by']);
            unset($query['status']);
            ksort($query);
            $this->attributes['query'] = json_encode($query);
        }
    }

    public static function availableToSave(Request $request)
    {
        $sortedRequest = $request->except(['page', 'take', 'sort_by', 'status']);
        if (count($sortedRequest) === 0) return false;
        // 'page', 'take'
        foreach ($sortedRequest as $k => $v) {
            if (in_array($k, ['amount_by_agreement', 'amount_with_vat', 'amount_cash', 'with_company',])) {
                $sortedRequest[$k] = intval($v);
            } elseif (in_array($k, ['cities_id'])) {
                $sortedRequest[$k] = array_map("intval", explode(",", $v));
            }
        }
        ksort($sortedRequest);
        $exists = OrderFilter::query()
            ->where("user_id", auth('sanctum')->id())
            ->where("query", json_encode($sortedRequest))->exists();

        return !$exists;
    }
}
