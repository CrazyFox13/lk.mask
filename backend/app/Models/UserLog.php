<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'text'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function write(int $userId, string $text): bool
    {
        $model = new UserLog([
            'user_id' => $userId,
            'text' => $text
        ]);
        return $model->save();
    }
}
