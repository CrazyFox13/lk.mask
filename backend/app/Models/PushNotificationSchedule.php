<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationSchedule extends Model
{
    use HasFactory;

    protected $fillable = ["push_notification_id", "time", "status"];

    protected $casts=[
        "time"=>"datetime"
    ];

    const STATUSES = [
        "WAITING" => 'waiting',
        "RUNNING" => 'running',
        "COMPLETED" => 'completed',
    ];

    public function notification(): BelongsTo
    {
        return $this->belongsTo(PushNotification::class,"push_notification_id");
    }

    public function scopeNext($query)
    {
        return $query
            ->where("time", "<=", Carbon::now())
            ->where("status", self::STATUSES["WAITING"])
            ->orderBy("time");
    }

    public function scopeRunning($query)
    {
        return $query->where("status", "=", self::STATUSES["RUNNING"]);
    }

    public function complete()
    {
        $this->status = self::STATUSES["COMPLETED"];
        $this->save();
    }
}
