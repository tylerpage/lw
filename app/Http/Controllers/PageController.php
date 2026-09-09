<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $renderer = app(PageBlockRenderer::class);
        $seo = app(SeoService::class);

        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'content' => $renderer->render($page->blocks ?? []),
            'seo' => $seo->forPage($page),
            'jsonLd' => [$seo->personJsonLd()],
        ]);
    }
}
