<?php

namespace App\Http\Controllers;

use App\ContentAssistant\Services\ApplyProposalToDraft;
use App\ContentAssistant\Services\ContentRevisionService;
use App\Enums\ContentTargetType;
use App\Models\ContentProposal;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Project;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
        $proposalId = $request->integer('proposal') ?: null;

        return match ($type) {
            'page' => $this->previewPage($id, $renderer, $seo, $revisionId, $proposalId),
            'post' => $this->previewPost($id, $renderer, $seo, $revisionId, $proposalId),
            'project' => $this->previewProject($id, $renderer, $seo, $proposalId),
            default => abort(404),
        };
    }

    protected function previewPage(
        int $id,
        PageBlockRenderer $renderer,
        SeoService $seo,
        ?int $revisionId = null,
        ?int $proposalId = null,
    ): View {
        $page = Page::query()->findOrFail($id);

        if ($proposalId) {
            $page = $this->applyProposalPreview($page, $proposalId, ContentTargetType::Page, $id);
        } elseif ($revisionId) {
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

    protected function previewPost(
        int $id,
        PageBlockRenderer $renderer,
        SeoService $seo,
        ?int $revisionId = null,
        ?int $proposalId = null,
    ): View {
        $post = Post::query()->with(['author', 'categories'])->findOrFail($id);

        if ($proposalId) {
            $post = $this->applyProposalPreview($post, $proposalId, ContentTargetType::Post, $id);
        } elseif ($revisionId) {
            $revision = PostRevision::query()->where('post_id', $post->id)->findOrFail($revisionId);
            $post = app(ContentRevisionService::class)->hydratePreviewRevision($post, $revision);
        }

        return view('insights.show', [
            'post' => $post,
            'content' => $renderer->render($post->body ?? []),
            'seo' => array_merge($seo->forPost($post), ['robots' => 'noindex, nofollow']),
            'jsonLd' => [],
            'related' => Collection::make(),
            'isPreview' => true,
        ]);
    }

    protected function previewProject(
        int $id,
        PageBlockRenderer $renderer,
        SeoService $seo,
        ?int $proposalId = null,
    ): View {
        $project = Project::query()->with(['metrics', 'disciplines', 'industries'])->findOrFail($id);

        if ($proposalId) {
            $project = $this->applyProposalPreview($project, $proposalId, ContentTargetType::Project, $id);
        }

        return view('work.show', [
            'project' => $project,
            'content' => $renderer->render($project->blocks ?? []),
            'seo' => array_merge($seo->forProject($project), ['robots' => 'noindex, nofollow']),
            'jsonLd' => [],
            'related' => Collection::make(),
            'isPreview' => true,
        ]);
    }

    /**
     * @template T of Page|Post|Project
     *
     * @param  T  $target
     * @return T
     */
    protected function applyProposalPreview(Page|Post|Project $target, int $proposalId, ContentTargetType $type, int $targetId): Page|Post|Project
    {
        $proposal = ContentProposal::query()->with('operations')->findOrFail($proposalId);

        abort_unless(
            $proposal->target_type === $type && $proposal->target_id === $targetId,
            403,
            'This preview link does not match the selected content.',
        );

        return app(ApplyProposalToDraft::class)->hydratePreview($target, $proposal);
    }
}
