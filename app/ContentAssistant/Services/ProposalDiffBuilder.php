<?php

namespace App\ContentAssistant\Services;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;

class ProposalDiffBuilder
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, array<string, mixed>>
     */
    public function build(Model $target, array $payload): array
    {
        $diffs = [];
        $operations = $payload['operations'] ?? [];

        foreach ($operations as $index => $operation) {
            $diffs[] = match ($operation['op'] ?? null) {
                'replace_field' => $this->fieldDiff($target, $operation, $index),
                'replace_block_fields' => $this->blockFieldDiff($target, $operation, $index),
                'insert_block' => [
                    'type' => 'block_insert',
                    'label' => 'Insert block',
                    'operation_index' => $index,
                    'after' => null,
                    'block' => $operation['block'] ?? [],
                ],
                'remove_block' => [
                    'type' => 'block_remove',
                    'label' => 'Remove block',
                    'operation_index' => $index,
                    'block_index' => $operation['block_index'] ?? null,
                ],
                'move_block' => [
                    'type' => 'block_move',
                    'label' => 'Move block',
                    'operation_index' => $index,
                    'from' => $operation['block_index'] ?? null,
                    'to' => $operation['to_index'] ?? null,
                ],
                'change_slug' => [
                    'type' => 'slug_change',
                    'label' => 'Slug change',
                    'operation_index' => $index,
                    'before' => $target->slug,
                    'after' => $operation['slug'] ?? null,
                    'warning' => 'Changing a slug may require a redirect.',
                ],
                default => [
                    'type' => 'unsupported',
                    'label' => $operation['op'] ?? 'unknown',
                    'operation_index' => $index,
                ],
            };
        }

        return $diffs;
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function fieldDiff(Model $target, array $operation, int $index): array
    {
        $field = $operation['field'] ?? 'unknown';

        return [
            'type' => 'field',
            'label' => $field,
            'operation_index' => $index,
            'before' => $target->{$field} ?? null,
            'after' => $operation['value'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>
     */
    private function blockFieldDiff(Model $target, array $operation, int $index): array
    {
        $blocks = match ($target::class) {
            Page::class, Project::class => $target->blocks ?? [],
            Post::class => $target->body ?? [],
            default => [],
        };

        $blockIndex = $operation['block_index'] ?? null;
        $before = is_int($blockIndex) ? ($blocks[$blockIndex] ?? []) : [];
        $after = $before;
        $preserve = $operation['preserve'] ?? [];

        foreach ($operation['fields'] ?? [] as $key => $value) {
            if (! in_array($key, $preserve, true)) {
                $after[$key] = $value;
            }
        }

        return [
            'type' => 'block_fields',
            'label' => ($before['type'] ?? 'block').' #'.($blockIndex ?? '?'),
            'operation_index' => $index,
            'before' => $before,
            'after' => $after,
            'preserved' => $preserve,
        ];
    }
}
