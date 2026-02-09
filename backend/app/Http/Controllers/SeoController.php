<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function publicURLS(Request $request): JsonResponse
    {
        $pagePaths = Page::query()
            ->where('type', '=', 'material')
            ->active()
            ->pluck("path");

        $orderPaths = Order::query()
            ->visible()
            ->filtered($request)
            ->pluck("id")->map(fn($id) => "/orders/{$id}");

        return response()->json([
            ...$pagePaths,
            ...$orderPaths
        ]);
    }
}
