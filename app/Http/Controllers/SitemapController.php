<?php

namespace App\Http\Controllers;

use App\Services\DiscoverableContentService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(DiscoverableContentService $discoverableContent): Response
    {
        $xml = view('sitemap', [
            'urls' => $discoverableContent->sitemapUrls(),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
