<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Http\Response;

class RssController extends Controller
{
    public function __invoke(): Response
    {
        $posts = Post::query()->published()->latest('published_at')->limit(20)->get();

        $xml = view('rss', [
            'posts' => $posts,
            'siteName' => SiteSetting::get('site_name', config('app.name')),
            'siteUrl' => config('app.url'),
        ])->render();

        return response($xml, 200, ['Content-Type' => 'application/rss+xml']);
    }
}
