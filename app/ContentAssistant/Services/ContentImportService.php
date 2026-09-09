<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\DTO\ContentImportData;
use App\ContentAssistant\Enums\ContentImportMode;
use App\ContentAssistant\Support\ContentTargetResolver;
use App\Enums\PublishStatus;
use App\Models\ContentAssistantAuditEvent;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;
use App\PageBlocks\BlockRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ContentImportService
{
    private const ALLOWED_TOP_LEVEL_KEYS = [
        'import_version',
        'content_type',
        'summary',
        'general',
        'blocks',
        'warnings',
        'unverified_claims',
    ];

    public function __construct(
        private ContentRevisionService $revisionService,
        private ContentClaimValidator $claimValidator,
    ) {}

    /**
     * @return array{data: ?ContentImportData, errors: array<int, string>, warnings: array<int, string>, diff: array<int, array<string, mixed>>}
     */
    public function validatePayload(Model $target, string $json, ContentImportMode $mode): array
    {
        $errors = [];
        $warnings = [];

        try {
            $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            return [
                'data' => null,
                'errors' => ['Invalid JSON: '.$exception->getMessage()],
                'warnings' => [],
                'diff' => [],
            ];
        }

        if (! is_array($payload)) {
            return [
                'data' => null,
                'errors' => ['Import payload must be a JSON object.'],
                'warnings' => [],
                'diff' => [],
            ];
        }

        foreach (array_keys($payload) as $key) {
            if (! in_array($key, self::ALLOWED_TOP_LEVEL_KEYS, true)) {
                $errors[] = "Unknown top-level key: {$key}";
            }
        }

        if (($payload['import_version'] ?? null) !== 1) {
            $errors[] = 'import_version must be 1.';
        }

        $expectedType = ContentTargetResolver::fromModel($target);

        if (($payload['content_type'] ?? null) !== $expectedType->value) {
            $errors[] = "content_type must be {$expectedType->value} for this record.";
        }

        if (! is_string($payload['summary'] ?? null) || trim($payload['summary']) === '') {
            $errors[] = 'summary is required.';
        }

        if (! isset($payload['blocks']) || ! is_array($payload['blocks'])) {
            $errors[] = 'blocks must be an array.';
        } else {
            foreach ($payload['blocks'] as $index => $block) {
                if (! is_array($block)) {
                    $errors[] = "Block {$index} must be an object.";

                    continue;
                }

                $type = $block['type'] ?? 'unknown';

                foreach (BlockRegistry::validate($block) as $error) {
                    $errors[] = "Block {$index} ({$type}): {$error}";
                }
            }
        }

        if ($errors !== []) {
            return [
                'data' => null,
                'errors' => $errors,
                'warnings' => [],
                'diff' => [],
            ];
        }

        $data = ContentImportData::fromArray($payload);

        $claimWarnings = $this->claimValidator->validate([
            'general' => $data->general,
            'blocks' => $data->blocks,
        ]);

        $warnings = array_values(array_unique(array_merge(
            $data->warnings,
            $data->unverifiedClaims,
            $claimWarnings,
        )));

        if ($mode !== ContentImportMode::BlocksOnly && isset($data->general['slug']) && $data->general['slug'] !== $target->slug) {
            $warnings[] = 'Slug change included in general fields; verify redirects before publishing.';
        }

        return [
            'data' => $data,
            'errors' => [],
            'warnings' => $warnings,
            'diff' => $this->buildDiff($target, $data, $mode),
        ];
    }

    public function apply(Model $target, ContentImportData $data, User $user, ContentImportMode $mode): Model
    {
        $validation = $this->validatePayload($target, json_encode($data->toArray()), $mode);

        if ($validation['errors'] !== []) {
            throw new InvalidArgumentException(implode(' ', $validation['errors']));
        }

        DB::transaction(function () use ($target, $data, $user, $mode): void {
            $wasPublic = $target->isPubliclyVisible();

            if ($target instanceof Page || $target instanceof Post) {
                if ($wasPublic && ! $target->has_unpublished_changes) {
                    $revision = $this->revisionService->record($target, $user, 'content_import', 'Live version (on site)');
                    $this->revisionService->pinPublishedRevision($target, $revision);
                } else {
                    $this->revisionService->record($target, $user, 'content_import');
                }
            }

            if ($mode !== ContentImportMode::BlocksOnly) {
                $this->applyGeneralFields($target, $data->general);
            }

            $this->applyBlocks($target, $data->blocks, $mode);

            if ($wasPublic && ($target instanceof Page || $target instanceof Post)) {
                $target->status = PublishStatus::Published;
            } else {
                $target->status = PublishStatus::Draft;
            }

            $target->save();
        });

        ContentAssistantAuditEvent::record('content_import_applied', $user, $target, [
            'summary' => $data->summary,
            'mode' => $mode->value,
            'block_count' => count($data->blocks),
        ]);

        return $target->fresh();
    }

    /**
     * @param  array<string, mixed>  $general
     */
    private function applyGeneralFields(Page|Post|Project $target, array $general): void
    {
        $allowed = match ($target::class) {
            Page::class => ['title', 'nav_label', 'slug', 'template', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description'],
            Post::class => ['title', 'slug', 'excerpt', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description'],
            Project::class => ['title', 'slug', 'card_summary', 'seo_title', 'seo_description', 'canonical_url', 'og_title', 'og_description'],
        };

        foreach ($general as $field => $value) {
            if (in_array($field, $allowed, true)) {
                $target->{$field} = $value;
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function applyBlocks(Page|Post|Project $target, array $blocks, ContentImportMode $mode): void
    {
        $current = $this->blocks($target);

        $next = match ($mode) {
            ContentImportMode::Append => array_merge($current, $blocks),
            default => $blocks,
        };

        if ($target instanceof Post) {
            $target->body = $next;
        } else {
            $target->blocks = $next;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildDiff(Page|Post|Project $target, ContentImportData $data, ContentImportMode $mode): array
    {
        $diffs = [];

        if ($mode !== ContentImportMode::BlocksOnly) {
            foreach ($data->general as $field => $after) {
                $before = $target->{$field} ?? null;

                if ($before !== $after) {
                    $diffs[] = [
                        'type' => 'field',
                        'field' => $field,
                        'before' => $before,
                        'after' => $after,
                    ];
                }
            }
        }

        $beforeBlocks = $this->blocks($target);
        $afterBlocks = match ($mode) {
            ContentImportMode::Append => array_merge($beforeBlocks, $data->blocks),
            default => $data->blocks,
        };

        $diffs[] = [
            'type' => 'blocks',
            'before_count' => count($beforeBlocks),
            'after_count' => count($afterBlocks),
            'before_types' => collect($beforeBlocks)->pluck('type')->all(),
            'after_types' => collect($afterBlocks)->pluck('type')->all(),
        ];

        return $diffs;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function blocks(Page|Post|Project $target): array
    {
        return match ($target::class) {
            Page::class, Project::class => $target->blocks ?? [],
            Post::class => $target->body ?? [],
        };
    }
}
