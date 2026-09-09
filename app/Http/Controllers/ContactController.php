<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\SeoService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(SeoService $seo): View
    {
        $page = Page::query()->where('slug', 'contact')->first();

        return view('contact.show', [
            'page' => $page,
            'seo' => $page ? $seo->forPage($page) : [
                'title' => 'Contact',
                'description' => 'Get in touch with Lindsey Wegmann.',
                'canonical' => url('/contact'),
                'robots' => 'index, follow',
            ],
        ]);
    }
}
