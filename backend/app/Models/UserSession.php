<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    use HasFactory;

    protected $fillable = ['uuid'];

    public static function findOrCreate(): UserSession
    {
        $value = request()->header('UserSession');
        $session = UserSession::query()->where("uuid", $value)->first();
        if (!$session) {
            $session = new UserSession(["uuid" => $value]);
            $session->save();
        }
        return $session;
    }
}
