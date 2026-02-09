<?php

namespace App\Models;

use App\Jobs\SendPush;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Конкретные уведомления (ручные+автоматические)
 */
class CompanyNotification extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'user_id', 'subject', 'text', 'data', 'push'];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    public static function boot()
    {
        parent::boot();

        self::created(function (CompanyNotification $companyNotification) {
            if ($companyNotification->push) {
                $companyNotification->sendPush();
            };
        });
    }

    /**
     * Relations
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessors, Mutators
     */

    public function getDataAttribute($value)
    {
        if (!$value) return null;
        return json_decode($value);
    }

    public function setDataAttribute($value)
    {
        if ($value) $this->attributes['data'] = json_encode($value);
    }

    /**
     * Scopes
     */

    public function scopeVisible(Builder $builder, User $user = null): Builder
    {
        if (!$user) {
            $user = auth()->user();
        }

        return $builder->where("user_id", $user->id);
    }

    public function scopeNew(Builder $builder): Builder
    {
        return $builder->whereNull("viewed_at");
    }

    /**
     * Methods
     */

    public function sendPush()
    {
        /** @var User|null $user */
        $user = $this->user;
        if (!$user) return;
        $device = $user->device;
        $delay = $user->getSilenceDelay();
        if ($device) {
            $allBadges = $user->countBadges();
            $text = str_replace("<br/>", "\n", $this->text);
            dispatch(new SendPush(device: $device, title: $this->subject, text: strip_tags($text), action: $this->id, data: (array)$this->data, badgesCount: $allBadges['total_count']))->delay(now()->addSeconds($delay));
        }
    }

    public static function view(array $ids)
    {
        DB::table('company_notifications')
            ->whereNull("viewed_at")
            ->whereIn("id", $ids)
            ->update([
                'viewed_at' => Carbon::now(),
            ]);
    }
}
