<?php

namespace App\ContentAssistant\Services;

use App\Enums\ContentProposalStatus;
use App\Enums\PublishStatus;
use App\Models\ContentProposal;
use App\Models\Page;
use App\Models\PageRevision;
use App\Models\Post;
use App\Models\PostRevision;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\User;
use App\PageBlocks\BlockRegistry;
use Illuminate\Support\Facades\DB;

class ApplyProposalToDraft
{
    public function apply(ContentProposal $proposal, User $user): ContentProposal
    {
        if ($proposal->status !== ContentProposalStatus::Validated) {
            throw new \RuntimeException('Only validated proposals can be applied.');
        }

        $target = $proposal->target();

        if (! $target) {
            throw new \RuntimeException('Proposal target no longer exists.');
        }

        if ($proposal->isStale()) {
            $proposal->update(['status' => ContentProposalStatus::Stale]);

            throw new \RuntimeException('The underlying content changed. Regenerate the proposal.');
        }

        DB::transaction(function () use ($proposal, $target, $user): void {
            $this->snapshotRevision($target, $user);

            foreach ($proposal->operations as $operationModel) {
                $this->applyOperation($target, $operationModel->operation);
            }

            $target->status = PublishStatus::Draft;
            $target->save();

            $proposal->update(['status' => ContentProposalStatus::DraftSaved]);
        });

        return $proposal->fresh(['operations']);
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyOperation(Page|Post|Project $target, array $operation): void
    {
        match ($operation['op'] ?? null) {
            'replace_field' => $this->applyReplaceField($target, $operation),
            'replace_block_fields' => $this->applyReplaceBlockFields($target, $operation),
            'insert_block' => $this->applyInsertBlock($target, $operation),
            'remove_block' => $this->applyRemoveBlock($target, $operation),
            'move_block' => $this->applyMoveBlock($target, $operation),
            'change_slug' => $this->applyChangeSlug($target, $operation),
            default => throw new \RuntimeException('Unsupported operation during apply.'),
        };
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyReplaceField(Page|Post|Project $target, array $operation): void
    {
        $field = $operation['field'] ?? null;
        $target->{$field} = $operation['value'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyReplaceBlockFields(Page|Post|Project $target, array $operation): void
    {
        $blocks = $this->blocks($target);
        $index = $operation['block_index'] ?? null;

        if (! is_int($index) || ! isset($blocks[$index])) {
            throw new \RuntimeException('Block not found for replace_block_fields.');
        }

        $preserve = $operation['preserve'] ?? [];
        foreach ($operation['fields'] ?? [] as $key => $value) {
            if (! in_array($key, $preserve, true)) {
                $blocks[$index][$key] = $value;
            }
        }

        BlockRegistry::validate($blocks[$index]);

        $this->setBlocks($target, $blocks);
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyInsertBlock(Page|Post|Project $target, array $operation): void
    {
        $blocks = $this->blocks($target);
        $index = $operation['index'] ?? count($blocks);
        array_splice($blocks, $index, 0, [$operation['block']]);
        $this->setBlocks($target, $blocks);
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyRemoveBlock(Page|Post|Project $target, array $operation): void
    {
        $blocks = $this->blocks($target);
        $index = $operation['block_index'] ?? null;

        if (! is_int($index) || ! isset($blocks[$index])) {
            throw new \RuntimeException('Block not found for remove_block.');
        }

        array_splice($blocks, $index, 1);
        $this->setBlocks($target, $blocks);
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyMoveBlock(Page|Post|Project $target, array $operation): void
    {
        $blocks = $this->blocks($target);
        $from = $operation['block_index'] ?? null;
        $to = $operation['to_index'] ?? null;

        if (! is_int($from) || ! is_int($to) || ! isset($blocks[$from])) {
            throw new \RuntimeException('Invalid move_block operation.');
        }

        $block = $blocks[$from];
        array_splice($blocks, $from, 1);
        array_splice($blocks, $to, 0, [$block]);
        $this->setBlocks($target, $blocks);
    }

    /**
     * @param  array<string, mixed>  $operation
     */
    private function applyChangeSlug(Page|Post|Project $target, array $operation): void
    {
        $oldSlug = $target->slug;
        $newSlug = $operation['slug'] ?? null;

        if (! is_string($newSlug) || $newSlug === '') {
            throw new \RuntimeException('Invalid slug change operation.');
        }

        $target->slug = $newSlug;

        if ($operation['create_redirect'] ?? true) {
            Redirect::query()->updateOrCreate(
                ['from_path' => $this->publicPath($target, $oldSlug)],
                ['to_path' => $this->publicPath($target, $newSlug), 'status_code' => 301]
            );
        }
    }

    private function snapshotRevision(Page|Post|Project $target, User $user): void
    {
        match ($target::class) {
            Page::class => PageRevision::query()->create([
                'page_id' => $target->id,
                'user_id' => $user->id,
                'title' => $target->title,
                'blocks' => $target->blocks,
            ]),
            Post::class => PostRevision::query()->create([
                'post_id' => $target->id,
                'user_id' => $user->id,
                'data' => [
                    'title' => $target->title,
                    'excerpt' => $target->excerpt,
                    'body' => $target->body,
                ],
            ]),
            Project::class => null,
        };
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

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     */
    private function setBlocks(Page|Post|Project $target, array $blocks): void
    {
        if ($target instanceof Post) {
            $target->body = $blocks;
        } else {
            $target->blocks = $blocks;
        }
    }

    private function publicPath(Page|Post|Project $target, string $slug): string
    {
        return match ($target::class) {
            Page::class => $slug === 'home' ? '/' : '/'.$slug,
            Post::class => '/insights/'.$slug,
            Project::class => '/work/'.$slug,
        };
    }
}
