<?php

namespace App\Http\Controllers;

use App\Events\ChatListUpdated;
use App\Events\ChatMessageRead;
use App\Events\NewChatMessage;
use App\Helpers\Paginator;
use App\Http\Requests\Message\Create;
use App\Http\Requests\Message\Update;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    /**
     * @param Chat $chat
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Chat $chat, Request $request): JsonResponse
    {
        list($page, $skip, $take) = Paginator::get($request);

        $pivot = DB::table("chat_user")->where("user_id", auth()->id())
            ->where("chat_id", $chat->id)
            ->first();

        if (!$pivot) abort(404);

        $totalCount = Message::query()
            ->where('chat_id', $chat->id)
            ->visible($pivot->blocked_at)
            ->count();

        $messages = Message::query()
            ->with(['author' => function ($q) use ($chat) {
                $q->select(array_merge(User::PUBLIC_FIELDS, ['chat_user.role']))
                    ->leftJoin('chat_user', function ($q) use ($chat) {
                        $q->on('chat_user.user_id', '=', 'users.id')
                            ->where('chat_user.chat_id', '=', "$chat->id");
                    });
            }, 'repliedMessage', 'repliedMessage.author' => function ($q) {
                $q->select(User::PUBLIC_FIELDS);
            }])
            ->where('chat_id', $chat->id)
            ->visible($pivot->blocked_at)
            ->orderBy('id', 'desc')
            ->skip($skip)
            ->take($take)
            ->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        DB::table("chat_user")
            ->where("chat_id", $chat->id)
            ->where("user_id", auth()->id())
            ->update(['last_seen_at' => Carbon::now()]);

        $readMessages = Message::query()
            ->where("chat_id", $chat->id)
            ->where("author_id", "!=", auth()->id())
            ->whereNull("read_at")
            ->pluck("id");

        Message::query()
            ->whereIn("id", $readMessages)
            ->update([
                "read_at" => Carbon::now()
            ]);

        if ($readMessages->count() > 0) {
            broadcast(new ChatMessageRead($chat->id, $readMessages->toArray()))->toOthers();
        }

        return $this->resourceListResponse('messages', $messages, $totalCount, $pagesCount);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * @param Chat $chat
     * @param Create $request
     * @return JsonResponse
     */
    public function store(Chat $chat, Create $request): JsonResponse
    {
        $message = new Message([
            'chat_id' => $chat->id,
            'author_id' => auth()->id(),
        ]);
        $message->fill($request->all([
            'text', 'file_type', 'file_url', 'reply_message_id'
        ]));
        $message->sent_at = Carbon::now();
        $message->save();

        $chat->updated_at = now();
        $chat->save();

        broadcast(new NewChatMessage($chat->id, $message->id, auth("sanctum")->id()))->toOthers();

        $message->load(['author' => function ($q) use ($chat) {
            $q->select(array_merge(User::PUBLIC_FIELDS, ['chat_user.role']))
                ->join('chat_user', function ($q) use ($chat) {
                    $q->on('chat_user.user_id', '=', 'users.id')
                        ->where('chat_user.chat_id', '=', "$chat->id");
                });
        }, 'repliedMessage', 'repliedMessage.author' => function ($q) {
            $q->select(User::PUBLIC_FIELDS);
        }]);

        return $this->resourceItemResponse('message', $message);
    }

    /**
     * @param Chat $chat
     * @param Message $message
     * @return JsonResponse
     */
    public function show(Chat $chat, Message $message): JsonResponse
    {
        if (Gate::denies('view', $message)) {
            abort(401);
        }

        $message->load(['author' => function ($q) use ($chat) {
            $q->select(array_merge(User::PUBLIC_FIELDS, ['chat_user.role']))
                ->join('chat_user', function ($q) use ($chat) {
                    $q->on('chat_user.user_id', '=', 'users.id')
                        ->where('chat_user.chat_id', '=', "$chat->id");
                });
        }]);

        return $this->resourceItemResponse('message', $message);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Message $message
     * @return \Illuminate\Http\Response
     */
    public function update(Update $request, Chat $chat, Message $message): JsonResponse
    {
        $message->fill($request->all([
            'text', 'file_type', 'file_url'
        ]));

        if (!$request->get('file_url')) {
            $message->file_type = null;
        }

        $message->save();

        return $this->resourceItemResponse('message', $message);
    }

    /**
     * @param Chat $chat
     * @param Message $message
     * @return JsonResponse
     */
    public function destroy(Chat $chat, Message $message): JsonResponse
    {
        if (Gate::denies('delete', $message)) abort(403);

        $message->delete();

        return $this->emptySuccessResponse();
    }

    /* DEPRECATED todo: DELETE AFTER APP UPDATE */
    public function newMessagesCount(Request $request): JsonResponse
    {
        $count = match ($request->get("type")) {
            "out" => Message::getOutReportsNewMessagesCount(),
            "in" => Message::getInReportsNewMessagesCount(),
            default => Message::getTotalNewMessagesCount(),
        };
        return $this->resourceItemResponse('messages_count', $count);
    }
}
