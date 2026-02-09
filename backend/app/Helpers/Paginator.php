<?php

namespace App\Helpers;

use Illuminate\Http\Request;

class Paginator
{
    const DEFAULT_PAGINATION_SIZE = 30;

    public static function get(Request $request)
    {
        $page = (int)$request->get('page') ?: 1;
        $take = (int)$request->get('take') ?: self::DEFAULT_PAGINATION_SIZE;
        $skip = ($page - 1) * $take;
        return [$page, $skip, $take];
    }

    public static function pagesCount(int $take, int $totalCount): int
    {
        return ceil($totalCount / $take);
    }

    /**
     * @param Request $request
     * @return array
     */
    public static function getSorting(Request $request): array
    {
        if ($request->has("sort_desc")) {
            $sortDir = $request->sort_desc ? "DESC" : "ASC";
        } else {
            $sortDir = "DESC";
        }
        $sort = $request->sort_by ?: "id";
        return [$sort, $sortDir];
    }
}
