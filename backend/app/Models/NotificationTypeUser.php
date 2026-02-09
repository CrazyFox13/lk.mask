<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTypeUser extends Model
{
    use HasFactory;

    protected $table = 'notification_type_user';

    public function type()
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

    public function scopePush($q)
    {
        return $q->where("way", "push");
    }

    public function scopeEmail($q)
    {
        return $q->where("way", "email");
    }

    public function scopeActive($q)
    {
        return $q->where("active", true);
    }

    public static function isEnabled(int $uid, string $key, string $way): bool
    {
        if ($way === "push") {
            // device exists?
            $device = AppDevice::query()->where("user_id", "=", $uid)->exists();
            if (!$device) return false;
        }

        if ($way === "email") {
            // email confirmed?
            $user = User::query()->whereNotNull("email_verified_at")->where("id", "=", $uid)->exists();
            if (!$user) return false;
        }

        return NotificationTypeUser::query()
            ->whereHas("type", function ($q) use ($key) {
                $q->where("key", "=", $key);
            })
            ->where("user_id", $uid)
            ->active()
            ->where("way", $way)->exists();
    }
}
