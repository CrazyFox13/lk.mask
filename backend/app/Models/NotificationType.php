<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    use HasFactory;

    const TYPES = [
        [
            'key' => 'vehicle_orders',
            'title' => 'Новые заявки на технику',
            'description' => 'Общая подписка на уведомления о новых заявках'
        ],
        [
            'key' => 'filters',
            'title' => 'Новые заявки в сохраненных фильтрах',
            'description' => 'Заявки по параметрам сохраненных поисков'
        ],
        [
            'key' => 'personal',
            'title' => 'Персональные сообщения',
            'description' => 'Анонсы, отзывы, модерация, арбитраж, рейтинг'
        ],
        [
            'key' => 'orders',
            'title' => 'Информация по вашим заявкам',
            'description' => 'Модерация, завершение сроков публикации, жалобы'
        ],
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('way', 'active');
    }

    public function notificationTypeUser()
    {
        return $this->hasMany(NotificationTypeUser::class);
    }

    public function scopePersonal($query)
    {
        return $query->where("key", "personal");
    }

    public static function attachNotifications(User $user)
    {
        NotificationType::query()
            ->get()->each(function (NotificationType $type) use ($user) {
                $type->users()->attach($user->id, ['way' => 'push', 'active' => true]);
                $type->users()->attach($user->id, ['way' => 'email', 'active' => true]);
            });
    }

    public static function enableFiltersForAuthUser(bool $activeEmail, bool $activePush, $uid = null)
    {
        if ($uid) {
            $uid = auth('sanctum')->id();
        }
        // enable notification if not
        $notification = NotificationType::query()->where("key", "=", "filters")->first();
        if ($notification) {
            if ($activePush) {
                NotificationTypeUser::query()
                    ->where("user_id", $uid)
                    ->where("notification_type_id", $notification->id)
                    ->whereWay("push")
                    ->update(['active' => true]);
            }
            if ($activeEmail) {
                NotificationTypeUser::query()
                    ->where("user_id", $uid)
                    ->where("notification_type_id", $notification->id)
                    ->whereWay("email")
                    ->update(['active' => true]);
            }
        }
    }
}
