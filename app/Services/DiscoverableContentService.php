<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Support\Collection;

class DiscoverableContentService
{
    /**
     * @return Collection<int, array{loc: string, lastmod: string}>
     */
    public function sitemapUrls(): Collection
    {
        return $this->coreUrls()
            ->merge($this->indexablePages()->map(fn (Page $page) => [
                'loc' => url('/'.$page->slug),
                'lastmod' => $page->updated_at->toAtomString(),
            ]))
            ->merge($this->indexableProjects()->map(fn (Project $project) => [
                'loc' => url('/work/'.$project->slug),
                'lastmod' => $project->updated_at->toAtomString(),
            ]))
            ->merge($this->indexablePosts()->map(fn (Post $post) => [
                'loc' => url('/insights/'.$post->slug),
                'lastmod' => $post->updated_at->toAtomString(),
            ]));
    }

    /**
     * @return Collection<int, array{title: string, url: string, description: ?string}>
     */
    public function publishedPages(): Collection
    {
        return $this->indexablePages()->map(fn (Page $page) => [
            'title' => $page->nav_label ?: $page->title,
            'url' => url('/'.$page->slug),
            'description' => $this->oneLine($page->seoDescription()),
        ]);
    }

    /**
     * @return Collection<int, array{title: string, url: string, description: ?string}>
     */
    public function publishedProjects(): Collection
    {
        return $this->indexableProjects()->map(fn (Project $project) => [
            'title' => $project->title,
            'url' => url('/work/'.$project->slug),
            'description' => $this->oneLine($project->seoDescription() ?: $project->card_summary),
        ]);
    }

    /**
     * @return Collection<int, array{title: string, url: string, description: ?string}>
     */
    public function publishedPosts(): Collection
    {
        return $this->indexablePosts()->map(fn (Post $post) => [
            'title' => $post->title,
            'url' => url('/insights/'.$post->slug),
            'description' => $this->oneLine($post->seoDescription() ?: $post->excerpt),
        ]);
    }

    /**
     * @return Collection<int, Page>
     */
    private function indexablePages(): Collection
    {
        return Page::query()
            ->published()
            ->whereNotIn('slug', ['home'])
            ->where('index', true)
            ->orderBy('slug')
            ->get();
    }

    /**
     * @return Collection<int, Project>
     */
    private function indexableProjects(): Collection
    {
        return Project::query()
            ->published()
            ->where('index', true)
            ->orderByDesc('published_at')
            ->get();
    }

    /**
     * @return Collection<int, Post>
     */
    private function indexablePosts(): Collection
    {
        return Post::query()
            ->published()
            ->where('index', true)
            ->orderByDesc('published_at')
            ->get();
    }

    /**
     * @return Collection<int, array{loc: string, lastmod: string}>
     */
    private function coreUrls(): Collection
    {
        $lastmod = now()->toAtomString();

        return collect([
            ['loc' => url('/'), 'lastmod' => $lastmod],
            ['loc' => url('/about'), 'lastmod' => $lastmod],
            ['loc' => url('/work'), 'lastmod' => $lastmod],
            ['loc' => url('/insights'), 'lastmod' => $lastmod],
            ['loc' => url('/contact'), 'lastmod' => $lastmod],
            ['loc' => url('/privacy'), 'lastmod' => $lastmod],
        ]);
    }

    private function oneLine(?string $text): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    }
}
