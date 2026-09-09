<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Services\MediaLibraryService;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\ContentTargetType;
use App\Models\ApprovedSource;
use App\Models\AssistantProfile;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Model;

class ContentContextBuilder
{
    public function build(Model $target): array
    {
        $base = [
            'target_type' => ContentTargetResolver::fromModel($target)->value,
            'target_label' => ContentTargetResolver::label($target),
            'public_url' => ContentTargetResolver::publicUrl($target),
            'status' => (string) $target->status->value,
            'site' => [
                'name' => SiteSetting::get('site_name'),
                'job_title' => SiteSetting::get('job_title'),
                'short_bio' => SiteSetting::get('short_bio'),
            ],
        ];

        return array_merge($base, match ($target::class) {
            Page::class => $this->pageContext($target),
            Post::class => $this->postContext($target),
            Project::class => $this->projectContext($target),
            default => [],
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function approvedSourcesFor(ContentTargetType $type): array
    {
        return ApprovedSource::query()
            ->where('approval_status', 'approved')
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->filter(fn (ApprovedSource $source) => $this->sourceAppliesTo($source, $type))
            ->map(fn (ApprovedSource $source) => [
                'id' => $source->id,
                'title' => $source->title,
                'type' => $source->source_type,
                'excerpt' => str($source->content)->limit(500)->toString(),
            ])
            ->values()
            ->all();
    }

    public function assistantInstructions(): ?string
    {
        return AssistantProfile::active()?->instructions;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mediaLibraryAssets(int $limit = 50): array
    {
        return app(MediaLibraryService::class)->assetsForContext($limit);
    }

    private function pageContext(Page $page): array
    {
        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'blocks' => $page->blocks ?? [],
            'seo' => [
                'title' => $page->seo_title,
                'description' => $page->seo_description,
            ],
        ];
    }

    private function postContext(Post $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'body' => $post->body ?? [],
            'seo' => [
                'title' => $post->seo_title,
                'description' => $post->seo_description,
            ],
        ];
    }

    private function projectContext(Project $project): array
    {
        return [
            'title' => $project->title,
            'slug' => $project->slug,
            'card_summary' => $project->card_summary,
            'blocks' => $project->blocks ?? [],
            'seo' => [
                'title' => $project->seo_title,
                'description' => $project->seo_description,
            ],
        ];
    }

    private function sourceAppliesTo(ApprovedSource $source, ContentTargetType $type): bool
    {
        $areas = $source->applicable_areas ?? [];

        if ($areas === []) {
            return true;
        }

        return in_array($type->value, $areas, true) || in_array('all', $areas, true);
    }
}
