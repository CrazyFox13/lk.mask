<?php

namespace App\Http\Controllers;

use App\Http\Requests\Page\PageRequest;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin', ['except' => ['index', 'show', 'pageBySlug']]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $pages = Page::query();

        if ($request->get("active")) {
            $pages = $pages->active();
        }

        if ($type = $request->get("type")) {
            $pages = $pages->type($type);

            if ($type === "material") {
                $pages = $pages->select(['id', 'title', 'path']);
            }
        }

        if ($path = $request->get("path")) {
            $pages = $pages->where("path", $path);
        }

        $pages = $pages->orderBy("order");

        $pages = $pages->get();

        return $this->resourceListResponse('pages', $pages, $pages->count(), 1);
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
     * @param PageRequest $request
     * @return JsonResponse
     */
    public function store(PageRequest $request): JsonResponse
    {
        $page = new Page($request->all());
        $page->save();

        return $this->resourceItemResponse('page', $page);
    }

    /**
     * @param Page $page
     * @return JsonResponse
     */
    public function show(Page $page): JsonResponse
    {
        return $this->resourceItemResponse('page', $page);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Page $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        //
    }

    /**
     * @param PageRequest $request
     * @param Page $page
     * @return JsonResponse
     */
    public function update(PageRequest $request, Page $page): JsonResponse
    {
        $page->fill($request->all());
        $page->save();

        return $this->resourceItemResponse('page', $page);
    }

    /**
     * @param Page $page
     * @return JsonResponse
     */
    public function destroy(Page $page): JsonResponse
    {
        $page->delete();
        return $this->emptySuccessResponse();
    }

    public function move(Request $request): JsonResponse
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:pages,id'
        ]);

        $order = implode(",", $request->get("order"));

        $pages = Page::query()->orderByRaw("FIELD(id,$order)")->get();

        DB::transaction(function () use ($pages) {
            foreach ($pages as $k => $page) {
                $page->order = $k;
                $page->save();
            }
        });

        return $this->emptySuccessResponse();
    }

    public function pageBySlug(string $slug): JsonResponse
    {
        $page = Page::query()->active()->type("material")->where("path", $slug)->firstOrFail();
        return $this->resourceItemResponse('page', $page);
    }
}
