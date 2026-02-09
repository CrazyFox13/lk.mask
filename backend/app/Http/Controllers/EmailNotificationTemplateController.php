<?php

namespace App\Http\Controllers;

use App\Models\EmailNotificationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailNotificationTemplateController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $templates = EmailNotificationTemplate::query()->orderBy("id", "desc")->get();
        return $this->resourceListResponse("emailNotificationTemplates", $templates, count($templates), 1);
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
        $request->validate([
            "subject" => "required|max:255",
            "text" => "required"
        ]);

        $template = new EmailNotificationTemplate($request->only("subject", "text"));
        $template->save();

        return $this->resourceItemResponse("emailNotificationTemplate", $template);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\EmailNotificationTemplate $emailNotificationTemplate
     * @return \Illuminate\Http\Response
     */
    public function show(EmailNotificationTemplate $emailNotificationTemplate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\EmailNotificationTemplate $emailNotificationTemplate
     * @return \Illuminate\Http\Response
     */
    public function edit(EmailNotificationTemplate $emailNotificationTemplate)
    {
        //
    }

    /**
     * @param Request $request
     * @param EmailNotificationTemplate $emailNotificationTemplate
     * @return JsonResponse
     */
    public function update(Request $request, EmailNotificationTemplate $emailNotificationTemplate): JsonResponse
    {
        $request->validate([
            "subject" => "required|max:255",
            "text" => "required"
        ]);

        $emailNotificationTemplate->fill($request->only("subject", "text"));
        $emailNotificationTemplate->save();

        return $this->resourceItemResponse("emailNotificationTemplate", $emailNotificationTemplate);
    }

    /**
     * @param EmailNotificationTemplate $emailNotificationTemplate
     * @return JsonResponse
     */
    public function destroy(EmailNotificationTemplate $emailNotificationTemplate): JsonResponse
    {
        $emailNotificationTemplate->delete();
        return $this->emptySuccessResponse();
    }
}
