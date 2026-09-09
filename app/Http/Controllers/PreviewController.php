<?php

namespace App\Http\Controllers;

use App\ContentAssistant\Services\ContentRevisionService;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Project;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreviewController extends Controller
{
    public function __invoke(
        Request $request,
        string $type,
        int $id,
        PageBlockRenderer $renderer,
        SeoService $seo,
    ): View {
        if (! $request->hasValidSignature()) {
            abort(403, 'This preview link has expired or is invalid.');
        }

        $revisionId = $request->integer('revision') ?: null;

        return match ($type) {
            'page' => $this->previewPage($id, $renderer, $seo, $revisionId),
            'post' => $this->previewPost($id, $renderer, $seo, $revisionId),
            'project' => $this->previewProject($id, $renderer, $seo),
            default => abort(404),
        };
    }

    protected function previewPage(int $id, PageBlockRenderer $renderer, SeoService $seo, ?int $revisionId = null): View
    {
        $page = Page::query()->findOrFail($id);

        if ($revisionId) {
            $revision = PageRevision::query()->where('page_id', $page->id)->findOrFail($revisionId);
            $page = app(ContentRevisionService::class)->hydratePreviewRevision($page, $revision);
        }

        return view('pages.show', [
            'page' => $page,
            'content' => $renderer->render($page->blocks ?? []),
            'seo' => array_merge($seo->forPage($page), ['robots' => 'noindex, nofollow']),
            'jsonLd' => [],
            'isPreview' => true,
        ]);
    }

    protected function previewPost(int $id, PageBlockRenderer $renderer, SeoService $seo, ?int $revisionId = null): View
    {
        $post = Post::query()->with(['author', 'categories'])->findOrFail($id);

        if ($revisionId) {
            $revision = PostRevision::query()->where('post_id', $post->id)->findOrFail($revisionId);
            $post = app(ContentRevisionService::class)->hydratePreviewRevision($post, $revision);
        }

        return view('insights.show', [
            'post' => $post,
            'content' => $renderer->render($post->body ?? []),
            'seo' => array_merge($seo->forPost($post), ['robots' => 'noindex, nofollow']),
            'jsonLd' => [],
            'isPreview' => true,
        ]);
    }

    protected function previewProject(int $id, PageBlockRenderer $renderer, SeoService $seo): View
    {
        $project = Project::query()->with(['metrics', 'disciplines', 'industries'])->findOrFail($id);

        return view('work.show', [
            'project' => $project,
            'content' => $renderer->render($project->blocks ?? []),
            'seo' => array_merge($seo->forProject($project), ['robots' => 'noindex, nofollow']),
            'jsonLd' => [],
            'isPreview' => true,
        ]);
    }
}
