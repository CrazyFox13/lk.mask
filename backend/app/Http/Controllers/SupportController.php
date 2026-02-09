<?php

namespace App\Http\Controllers;

use App\Http\Requests\Support\NeedHelpRequest;
use App\Mail\SupportEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function sendOnEmail(NeedHelpRequest $request): JsonResponse
    {
        Mail::to(config('mail.support_address'))->queue(new SupportEmail($request->all()));
        return $this->emptySuccessResponse();
    }
}
