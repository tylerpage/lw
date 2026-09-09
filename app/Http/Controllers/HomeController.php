<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PageBlockRenderer $renderer, SeoService $seo): View
    {
        $page = Page::query()->published()->where('slug', 'home')->firstOrFail();

        return view('pages.show', [
            'page' => $page,
            'content' => $renderer->render($page->blocks ?? []),
            'seo' => $seo->forPage($page),
            'jsonLd' => [$seo->personJsonLd(), $seo->websiteJsonLd()],
        ]);
    }
}
