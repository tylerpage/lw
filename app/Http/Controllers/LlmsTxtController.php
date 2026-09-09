<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use App\Services\DiscoverableContentService;
use Illuminate\Http\Response;

class LlmsTxtController extends Controller
{
    public function __invoke(DiscoverableContentService $discoverableContent): Response
    {
        $markdown = view('llms-txt', [
            'siteName' => SiteSetting::get('site_name', config('app.name')),
            'jobTitle' => SiteSetting::get('job_title'),
            'shortBio' => SiteSetting::get('short_bio'),
            'location' => SiteSetting::get('location'),
            'linkedinUrl' => SiteSetting::get('linkedin_url'),
            'corePages' => [
                ['title' => 'Home', 'url' => url('/'), 'description' => 'Portfolio homepage and introduction.'],
                ['title' => 'About', 'url' => url('/about'), 'description' => 'Background, capabilities, and career story.'],
                ['title' => 'Work', 'url' => url('/work'), 'description' => 'Case studies and selected project work.'],
                ['title' => 'Insights', 'url' => url('/insights'), 'description' => 'Articles and notes on digital strategy and commerce.'],
                ['title' => 'Contact', 'url' => url('/contact'), 'description' => 'Get in touch for roles, consulting, or speaking.'],
            ],
            'pages' => $discoverableContent->publishedPages(),
            'projects' => $discoverableContent->publishedProjects(),
            'posts' => $discoverableContent->publishedPosts(),
        ])->render();

        return response($markdown, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
