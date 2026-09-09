<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        $posts = collect();
        $projects = collect();

        if ($query !== '') {
            $posts = Post::query()
                ->published()
                ->where(fn ($q) => $q->where('title', 'like', "%{$query}%")->orWhere('excerpt', 'like', "%{$query}%"))
                ->limit(10)
                ->get();

            $projects = Project::query()
                ->published()
                ->where(fn ($q) => $q->where('title', 'like', "%{$query}%")->orWhere('card_summary', 'like', "%{$query}%"))
                ->limit(10)
                ->get();
        }

        return view('search.index', [
            'query' => $query,
            'posts' => $posts,
            'projects' => $projects,
            'seo' => [
                'title' => 'Search',
                'description' => 'Search insights and work.',
                'canonical' => url('/search'),
                'robots' => 'noindex, follow',
            ],
        ]);
    }
}
