<?php

namespace App\Http\Controllers;

use App\Models\AdvPlace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdvPlaceController extends Controller
{
    public function index(): JsonResponse
    {
        $places = AdvPlace::query()->get();
        return $this->resourceListResponse('advPlaces', $places, $places->count(), 1);
    }
}
