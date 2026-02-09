<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function resourceListResponse(string $key, $collection, int $totalCount, int $pagesCount,$additionalData = []): JsonResponse
    {
        $data = [
            'status' => 'success',
            'totalCount' => $totalCount,
            'pagesCount' => $pagesCount,
        ];
        $data[$key] = $collection;
        return response()->json(array_merge($data, $additionalData));
    }

    public function resourceItemResponse(string $key, $collection, $additionalData = []): JsonResponse
    {
        $data = [
            'status' => 'success'
        ];
        $data[$key] = $collection;
        return response()->json(array_merge($data, $additionalData));
    }

    public function emptySuccessResponse(): JsonResponse
    {
        return response()->json([
            'status' => 'success'
        ]);
    }
}
