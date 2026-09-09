<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsightsController extends Controller
{
    public function index(Request $request, SeoService $seo): View
    {
        $query = Post::query()->published()->latest('published_at');

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('categories', fn ($q) => $q->where('slug', $category));
        }

        $posts = $query->paginate(12);
        $featured = Post::query()->published()->where('featured', true)->latest('published_at')->first();

        return view('insights.index', [
            'posts' => $posts,
            'featured' => $featured,
            'categories' => Category::query()->has('posts')->orderBy('name')->get(),
            'seo' => [
                'title' => 'Insights',
                'description' => 'Articles on ecommerce strategy, operations, and digital leadership.',
                'canonical' => url('/insights'),
                'robots' => 'index, follow',
            ],
        ]);
    }

    public function show(string $slug, PageBlockRenderer $renderer, SeoService $seo): View
    {
        $post = Post::query()->published()->where('slug', $slug)->with(['author', 'categories'])->firstOrFail();

        return view('insights.show', [
            'post' => $post,
            'content' => is_array($post->body) ? $renderer->render($post->body) : $post->body,
            'seo' => $seo->forPost($post),
            'jsonLd' => [$seo->articleJsonLd($post)],
            'related' => Post::query()
                ->published()
                ->where('id', '!=', $post->id)
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
