<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Pusher\Pusher;
use Pusher\PusherException;

class BroadcastController extends Controller
{
    /**
     * @throws PusherException
     */
    public function authPresence(Request $request)
    {
        $request->validate([
            'channel_name' => "required",
            "socket_id" => "required"
        ]);
        $pusher = new Pusher(
            config("broadcasting.connections.pusher.key"),
            config("broadcasting.connections.pusher.secret"),
            config("broadcasting.connections.pusher.app_id"),
            [
                'cluster' => 'ru1',
                'useTLS' => false,
                'host' => config("broadcasting.connections.pusher.options.host"),
                'port' => (int)config("broadcasting.connections.pusher.options.port"),
                'scheme' => config("broadcasting.connections.pusher.options.scheme"),
            ]
        );
        list($channelType, $resourceName, $resourceId) = explode("-", $request->get("channel_name"));
        if ($resourceName === "chat") {
            $chat = \App\Models\Chat::query()->findOrFail($resourceId);
            if (Gate::denies('create', [Message::class, $chat])) abort(401);
            /** @var User $user */
            $user = auth("sanctum")->user();
            return $pusher->authorizePresenceChannel($request->get('channel_name'), $request->get('socket_id'), $user->id, $user->only("id", "name", "surname"));
        }
        if ($resourceName === "user") {
            if ((int)$resourceId !== auth("sanctum")->id()) abort(401);
            return $pusher->authorizeChannel($request->get('channel_name'), $request->get('socket_id'));
        }
        abort(404);
    }

    /**
     * @throws PusherException
     */
    public function auth(Request $request)
    {
        $request->validate([
            'channel_name' => "required",
            "socket_id" => "required"
        ]);
        $pusher = new Pusher(
            config("broadcasting.connections.pusher.key"),
            config("broadcasting.connections.pusher.secret"),
            config("broadcasting.connections.pusher.app_id"),
            [
                'cluster' => 'ru1',
                'useTLS' => false,
                'host' => config("broadcasting.connections.pusher.options.host"),
                'port' => (int)config("broadcasting.connections.pusher.options.port"),
                'scheme' => config("broadcasting.connections.pusher.options.scheme"),
            ]
        );
        list($channelType, $resourceName, $resourceId) = explode("-", $request->get("channel_name"));
        if ($resourceName === "chat") {
            $chat = \App\Models\Chat::query()->findOrFail($resourceId);
            if (Gate::denies('create', [Message::class, $chat])) abort(401);
            return $pusher->authorizeChannel($request->get('channel_name'), $request->get('socket_id'));
        }
        if ($resourceName === "user") {
            if ((int)$resourceId !== auth("sanctum")->id()) abort(401);
            return $pusher->authorizeChannel($request->get('channel_name'), $request->get('socket_id'));
        }
        abort(404);
    }

}
