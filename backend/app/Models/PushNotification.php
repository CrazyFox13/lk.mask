<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ручные PUSH рассылки
 */
class PushNotification extends Model
{
    use HasFactory;

    protected $fillable = ['subject', 'text', 'action', 'type', 'material'];

    protected $appends = ['editable'];

    const STATUSES = ['draft', 'scheduled', 'sending', 'sent', 'paused'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('sent_at')->withPivot("schedule_id");
    }

    public function queuedUsers()
    {
        return $this->users()->orderBy('push_notification_user.id')->wherePivotNull('sent_at');
    }

    public function sentUsers()
    {
        return $this->users()->orderBy('push_notification_user.id')->wherePivotNotNull('sent_at');
    }

    public function schedules()
    {
        return $this->hasMany(PushNotificationSchedule::class, "push_notification_id")->orderBy("push_notification_schedules.time");
    }

    public function getEditableAttribute()
    {
        return $this->status === self::STATUSES[0];
    }

    public function scopeScheduled($query)
    {
        return $query->where("status", self::STATUSES[1]);
    }

    public function scopeSending($query)
    {
        return $query->where("status", self::STATUSES[2]);
    }

    public function scopePush($q)
    {
        return $q->where("type", "push");
    }

    public function scopeEmail($q)
    {
        return $q->where("type", "email");
    }

    public function isPush(): bool
    {
        return $this->type === "push";
    }

    public function isEmail(): bool
    {
        return $this->type === "email";
    }

    public function copyToCompanyNotifications(User $user)
    {
        $notification = new CompanyNotification([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'subject' => $this->subject,
            'text' => $this->text,
            'data' => ["key" => $this->action, "push_notification_id" => $this->id],
            'push' => false,
        ]);
        $notification->save();
    }
}
