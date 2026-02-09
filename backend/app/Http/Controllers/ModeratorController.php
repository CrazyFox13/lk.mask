<?php

namespace App\Http\Controllers;

use App\Helpers\Paginator;
use App\Http\Requests\Moderator\Create;
use App\Http\Requests\Moderator\Update;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ModeratorController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */

    public function index(Request $request): JsonResponse
    {
        $users = User::query()->moderator();

        if ($request->has('search')) {
            $users = $users->search($request->get('search'));
        }
        $totalCount = $users->count();

        list($sort, $sortDir) = Paginator::getSorting($request);
        $users = $users->orderBy($sort, $sortDir);

        list($page, $skip, $take) = Paginator::get($request);
        if ($take > 0) {
            $users = $users->skip($skip)->take($take);
        }

        $users = $users->get();

        $pagesCount = Paginator::pagesCount($take, $totalCount);
        return $this->resourceListResponse('users', $users, $totalCount, $pagesCount);
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
     * @param Create $request
     * @return JsonResponse
     */
    public function store(Create $request): JsonResponse
    {
        $plainPassword = Str::random(12);

        $user = new User($request->all('name', 'surname', 'email', 'phone'));
        $user->password = Hash::make($plainPassword);
        $user->type = User::USER_TYPES[1];
        $user->save();

        return $this->resourceItemResponse('user', $user, [
            'plainPassword' => $plainPassword,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * @param Update $request
     * @param User $moderator
     * @return JsonResponse
     */
    public function update(Update $request, User $moderator): JsonResponse
    {
        $moderator->fill($request->all('name', 'surname', 'email', 'phone'));
        $moderator->save();

        return $this->resourceItemResponse('user', $moderator);
    }

    /**
     * @param User $moderator
     * @return JsonResponse
     */
    public function destroy(User $moderator): JsonResponse
    {
        $moderator->delete();

        return $this->emptySuccessResponse();
    }

    /**
     * @param User $moderator
     * @return JsonResponse
     */
    public function resetPassword(User $moderator): JsonResponse
    {
        $plainPassword = Str::random(12);
        $moderator->password = Hash::make($plainPassword);
        $moderator->save();

        return $this->resourceItemResponse('plainPassword', $plainPassword);
    }
}
