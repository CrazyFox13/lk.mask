<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        list($page, $skip, $take) = Paginator::get($request);
        $user = auth("sanctum")->user();
        $chats = $user->chats()
            ->has('messages')
            ->whereNotNull("order_id");

        $totalCount = $chats->distinct("chats.id")->count();

        $chats = $chats
            ->with(["lastMessage", "lastMessage.author" => function ($q) {
                $q->select("users.id", "users.name", "users.surname", "users.company_id", "users.avatar");
            }, "users" => function ($q) {
                $q->select("users.id", "users.name", "users.surname", "users.company_id", "users.avatar");
            }, "users.company" => function ($q) {
                $q->select("companies.id", "companies.title");
            }, "users.company.boss" => function ($q) {
                $q->select("users.id", "users.name", "users.surname", "users.company_id", "users.avatar");
            }, "order" => function ($q) {
                $q->select("orders.id", "orders.title", "orders.moderation_status");
            }])
            ->withCount(['newMessages'])
            ->orderBy('updated_at', 'desc')
            ->skip($skip)->take($take)->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);

        return $this->resourceListResponse("chats", $chats, $totalCount, $pagesCount);
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
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        //
    }

    /**
     * @param Chat $chat
     * @return JsonResponse
     */
    public function show(Chat $chat): JsonResponse
    {
        $user = auth("sanctum")->user();
        $chat = $user->chats()->with(["lastMessage", "lastMessage.author" => function ($q) {
            $q->select("users.id", "users.name", "users.surname", "users.company_id", "users.avatar","users.last_online_datetime");
        }, "users" => function ($q) {
            $q->select("users.id", "users.name", "users.surname", "users.company_id", "users.avatar","users.last_online_datetime");
        }, "users.company" => function ($q) {
            $q->select("companies.id", "companies.title");
        }, "users.company.boss" => function ($q) {
            $q->select("users.id", "users.name", "users.surname", "users.company_id", "users.avatar","users.last_online_datetime");
        }, "order" => function ($q) {
            $q->select("orders.id", "orders.title", "orders.moderation_status");
        }])->withCount(['newMessages'])->find($chat->id);
        if (!$chat) abort(404);
        return $this->resourceItemResponse("chat", $chat);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\Response
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Chat $chat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    public function mute(Chat $chat): JsonResponse
    {
        $user = auth()->user();
        $chatUser = $user->chats()->find($chat->id);
        $user->chats()->updateExistingPivot($chat->id, ["muted_at" => $chatUser->pivot->muted_at ? null : now()]);
        return $this->emptySuccessResponse();
    }

    public function block(Chat $chat): JsonResponse
    {
        $user = auth()->user();
        $chatUser = $user->chats()->find($chat->id);
        $user->chats()->updateExistingPivot($chat->id, ["blocked_at" => $chatUser->pivot->blocked_at ? null : now()]);
        return $this->emptySuccessResponse();
    }

    /**
     * @param Chat $chat
     * @return JsonResponse
     */
    public function destroy(Chat $chat): JsonResponse
    {
        if (Gate::denies("delete", $chat)) abort(403);
        $chat->delete();
        return $this->emptySuccessResponse();
    }

    public function enter(Chat $chat): JsonResponse
    {
        if (!$chat->users()->where("users.id", auth()->id())->exists()) {
            $chat->users()->attach(auth()->id(), ['role' => 'support']);
        }
        return $this->emptySuccessResponse();
    }
}
