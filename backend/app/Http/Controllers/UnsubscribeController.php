<?php

namespace App\Http\Controllers;

use App\Models\NotificationType;
use App\Models\NotificationTypeUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UnsubscribeController extends Controller
{
    public function unsubscribeFromEmail(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'email' => 'required|exists:users,email',
            'unsubscribe_hash' => 'required'
        ]);

        if (!Hash::check("astt-unsubscribe-$request->email", $request->get("unsubscribe_hash"))) abort(403);

        $user = User::query()->where("email", $request->email)->first();

        $notificationType = NotificationType::query()->where("key", $request->get("type"))->first();
        if (!$notificationType) abort(404);

        NotificationTypeUser::query()
            ->where("notification_type_id", $notificationType->id)
            ->where("user_id", $user->id)
            ->where("way", "=", "email")
            ->update([
                'active' => false
            ]);

        return $this->emptySuccessResponse();
    }
}
