<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatUser extends Pivot
{
    protected $casts = [
        "muted_at"=>"datetime",
        "blocked_at"=>"datetime",
        "last_seen_at"=>"datetime",
    ];
}
