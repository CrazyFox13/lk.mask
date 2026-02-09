<?php

namespace App\Models;

use App\Jobs\CompanyNotification\RecommendationCreated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;

class Recommendation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['author_user_id', 'company_id', 'target_user_id', 'text'];

    const STATUSES = [
        "DRAFT" => 'draft',
        "VIEWED" => 'viewed',
        "APPROVED" => 'approved',
        "CANCELED" => 'canceled',
        "DELETED" => 'deleted'
    ];

    protected $casts=[
        'target_viewed_at'=>'datetime'
    ];

    /**
     * Relations
     */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    /**
     * Scopes
     */

    public function scopeDraft(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUSES["DRAFT"]);
    }

    public function scopeViewed(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUSES["VIEWED"]);
    }

    public function scopeApproved(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUSES["APPROVED"]);
    }

    public function scopeCanceled(Builder $builder): Builder
    {
        return $builder->where('status', self::STATUSES["CANCELED"]);
    }

    public function scopeDeleted(Builder $builder): Builder
    {
        return $builder->where('status', '=', self::STATUSES["DELETED"]);
    }

    public function scopeNotDeleted(Builder $builder): Builder
    {
        return $builder->where('status', '!=', self::STATUSES["DELETED"]);
    }

    public function scopeVisible(Builder $builder): Builder
    {
        return $builder->where(function ($q) {
            $q->approved()->orWhere("author_user_id", auth()->id());
        });
    }

    public function scopeToMe(Builder $builder): Builder
    {
        return $builder->where("target_user_id", auth()->id());
    }

    public function scopeForUser(Builder $builder, User $user)
    {
        if ($company = $user->company()->first()) {
            $companyUsers = $company->users()->pluck("users.id")->toArray();
        } else {
            $companyUsers = [$user->id];
        }

        return $builder->where(function ($q) use ($companyUsers) {
            $q->whereIn("target_user_id", $companyUsers);
        });
    }

    public function scopeNew(Builder $builder): Builder
    {
        return $builder->whereNull("target_viewed_at");
    }

    public function scopeSearch(Builder $query, string $searchString): Builder
    {
        return $query->where(function (Builder $q) use ($searchString) {
            $q->where("text", "like", "%$searchString%")
                ->orWhereHas("author", function ($q) use ($searchString) {
                    $q->search($searchString);
                })->orWhereHas('targetUser', function ($q) use ($searchString) {
                    $q->search($searchString);
                })->orWhereHas('company', function ($q) use ($searchString) {
                    $q->search($searchString);
                });
        });
    }

    /**
     * Methods
     */

    public function isToMe(): bool
    {
        $user = auth('sanctum')->user();
        if ($company = $user->company()->first()) {
            $companyUsers = $company->users()->pluck("users.id")->toArray();
        } else {
            $companyUsers = [$user->id];
        }
        return in_array($this->target_user_id, $companyUsers);
    }

    public function makeDraft()
    {
        $this->status = self::STATUSES["DRAFT"];
        $this->save();
    }

    public function viewed()
    {
        $this->status = self::STATUSES["VIEWED"];
        $this->save();

        dispatch(new RecommendationCreated($this));

        if (NotificationTypeUser::isEnabled($this->target_user_id, 'personal', 'email')) {
            $email = $this->targetUser?->getRawOriginal('email');
            if ($email) Mail::to($email)->later(now()->addSeconds($this->targetUser->getSilenceDelay()), new \App\Mail\RecommendationCreated($this));
        }
    }

    public function approve()
    {
        $this->status = self::STATUSES["APPROVED"];
        $this->save();

        dispatch(new RecommendationCreated($this));

        if (NotificationTypeUser::isEnabled($this->target_user_id, 'personal', 'email')) {
            $email = $this->targetUser?->getRawOriginal('email');
            if ($email) Mail::to($email)->later(now()->addSeconds($this->targetUser->getSilenceDelay()), new \App\Mail\RecommendationCreated($this));
        }
    }

    public function cancel()
    {
        $this->status = self::STATUSES["CANCELED"];
        $this->save();
    }

    public function archive()
    {
        $this->status = self::STATUSES["DELETED"];
        $this->save();
    }

    public function targetView()
    {
        if (!$this->target_viewed_at) {
            $this->target_viewed_at = Carbon::now();
            $this->save();
        }
    }

    public static function targetViewMany(array $ids)
    {
        if (count($ids) === 0) return;

        $user = auth('sanctum')->user();
        if ($company = $user->company()->first()) {
            $companyUsers = $company->users()->pluck("users.id");
        } else {
            $companyUsers = [$user->id];
        }

        Recommendation::query()
            ->whereIn("id", $ids)
            ->whereIn("target_user_id", $companyUsers)
            ->whereNull('target_viewed_at')
            ->update([
                'target_viewed_at' => Carbon::now(),
            ]);
    }
}
