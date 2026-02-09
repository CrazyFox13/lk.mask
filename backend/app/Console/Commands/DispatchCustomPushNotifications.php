<?php

namespace App\Console\Commands;

use App\Jobs\SendPush;
use App\Mail\EmailNotification;
use App\Models\CompanyNotification;
use App\Models\PushNotification;
use App\Models\PushNotificationSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class DispatchCustomPushNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dispatch_custom_push_notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search next custom notification and send it';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /** @var PushNotification $notification */
        $notification = PushNotification::query()
            ->sending()->orderBy("id", "asc")->first();
        if (!$notification) {
            echo "No sending notification \n";

            echo "Looking for scheduled..\n";
            /** @var PushNotificationSchedule $nextSchedule */
            $nextSchedule = PushNotificationSchedule::query()
                ->next()
                ->first();

            if (!$nextSchedule) {
                echo "No scheduled notifications..\n";
                return 0;
            }
            /** @var PushNotification $push */
            $push = $nextSchedule->notification()->first();
            $push->status = PushNotification::STATUSES[2];
            $push->save();

            $nextSchedule->status = PushNotificationSchedule::STATUSES["RUNNING"];
            $nextSchedule->save();
            echo "Sending scheduled one...\n";
            return 0;
        }

        $schedule = $notification->schedules()->running()->first();
        $users = $this->getUsers($notification, $schedule?->id);

        if ($users->count() === 0) {
            $notification->status = PushNotification::STATUSES[3];
            $notification->progress = 100;
            $notification->save();

            /** @var PushNotificationSchedule|null $runningSchedule */
            $runningSchedule = $notification->schedules()->running()->first();
            $runningSchedule?->complete();
            echo "Sent to all receivers \n";
            return 0;
        }

        foreach ($users as $user) {
            $this->send($notification, $user, $schedule?->id);
        }

        $notification->progress = round($notification->sentUsers()->count() / $notification->users()->count() * 100);
        $notification->save();
        echo "Sent to next batch of receivers \n";
        return 0;
    }

    protected function send(PushNotification $notification, User $user, $scheduleId = null)
    {
        if ($notification->isPush()) {
            $this->sendPush($notification, $user, $scheduleId);
        } elseif ($notification->isEmail()) {
            $this->sendEmail($notification, $user, $scheduleId);
        }
    }

    protected function getUsers(PushNotification $notification, $scheduleId = null)
    {
        if ($notification->isPush()) {
            return $this->getNextPushRecipients($notification, $scheduleId);
        } elseif ($notification->isEmail()) {
            return $this->getNextEmailRecipients($notification, $scheduleId);
        }
        return [];
    }

    protected function sendEmail(PushNotification $notification, User $user, $scheduleId = null)
    {
        Mail::to($user->getRawOriginal('email'))->queue(new EmailNotification($notification, $user));
        DB::table('push_notification_user')
            ->where('push_notification_id', $notification->id)
            ->where("user_id", $user->id)
            ->where("schedule_id", "=", $scheduleId)
            ->update(['sent_at' => Carbon::now()]);
    }

    protected function sendPush(PushNotification $notification, User $user, $scheduleId = null)
    {
        $allBadges = $user->countBadges();

        dispatch(new SendPush(device: $user->device, title: $notification->subject, text: $notification->text, action: $notification->action ?: 'custom_notification', data: [
            'notification_id' => $notification->id,
            'user_id' => $user->id,
        ], badgesCount: $allBadges['total_count']));
        DB::table('push_notification_user')
            ->where('push_notification_id', $notification->id)
            ->where("user_id", $user->id)
            ->where("schedule_id", "=", $scheduleId)
            ->update(['sent_at' => Carbon::now()]);

        $notification->copyToCompanyNotifications($user);
    }

    protected function getNextPushRecipients(PushNotification $notification, $scheduleId = null)
    {
        $receivers = $notification->queuedUsers();
        if ($scheduleId) {
            $receivers = $receivers->wherePivot("schedule_id", $scheduleId);
        }
        return $receivers
            ->has('device')
            ->limit(50)->get();
    }

    protected function getNextEmailRecipients(PushNotification $notification, $scheduleId = null)
    {
        $receivers = $notification->queuedUsers();
        if ($scheduleId) {
            $receivers = $receivers->wherePivot("schedule_id", $scheduleId);
        }
        return $receivers
            ->withConfirmedEmail()
            ->limit(10)->get();
    }
}
