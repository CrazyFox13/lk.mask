<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = ['report_id', 'order_id'];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot(["role", "last_seen_at", "muted_at", "blocked_at"])->using(ChatUser::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function lastMessage()
    {
        return $this->hasOne(Message::class)->orderBy("created_at", "desc");
    }

    public function newMessages()
    {
        return $this->messages()
            ->where("messages.author_id", "!=", auth()->id())
            ->join("chat_user", function ($q) {
                $q->on("chat_user.chat_id", "=", "messages.chat_id")
                    ->where("chat_user.user_id", "=", auth()->id());
            })
            ->whereRaw("CASE WHEN chat_user.last_seen_at is null THEN 1 ELSE messages.created_at > chat_user.last_seen_at END");
    }

    public function inviteParties()
    {
        $report = $this->report;

        // invite author;
        if (!$this->users()->where("users.id", $report->author_user_id)->exists()) {
            $this->users()->attach($report->author_user_id, ['role' => 'author']);
        }

        // invite target
        if (!$this->users()->where("users.id", $report->target_user_id)->exists()) {
            $this->users()->attach($report->target_user_id, ['role' => 'target']);
        }

        // invite target company boss
        if ($boss = $report->company?->boss) {
            if (!$this->users()->where("users.id", $boss->id)->exists()) {
                $this->users()->attach($boss->id, ['role' => 'target']);
            }
        }
    }
}
