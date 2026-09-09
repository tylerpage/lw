<?php

namespace App\Http\Controllers;

use App\ContentAssistant\Services\ContentRevisionService;
use App\Models\Page;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PageBlockRenderer $renderer, SeoService $seo): View
    {
        $page = Page::query()->published()->where('slug', 'home')->firstOrFail();
        $page = app(ContentRevisionService::class)->forPublicDisplay($page);

        return view('pages.show', [
            'page' => $page,
            'content' => $renderer->render($page->blocks ?? []),
            'seo' => $seo->forPage($page),
            'jsonLd' => [$seo->personJsonLd(), $seo->websiteJsonLd()],
        ]);
    }
}
