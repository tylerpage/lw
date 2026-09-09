<?php

namespace App\ContentAssistant\Services;

use App\ContentAssistant\Support\ContentTargetResolver;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\PageBlocks\BlockRegistry;
use Illuminate\Database\Eloquent\Model;

class ProposalSchemaValidator
{
    private const ALLOWED_OPS = [
        'replace_field',
        'replace_block_fields',
        'insert_block',
        'move_block',
        'remove_block',
        'set_taxonomy',
        'set_related_content',
        'change_slug',
        'create_redirect',
    ];

    /** @var array<string, array<int, string>> */
    private const FIELD_WHITELIST = [
        'page' => ['title', 'nav_label', 'seo_title', 'seo_description', 'og_title', 'og_description', 'canonical_url'],
        'post' => ['title', 'excerpt', 'seo_title', 'seo_description', 'og_title', 'og_description', 'canonical_url'],
        'project' => ['title', 'card_summary', 'seo_title', 'seo_description', 'og_title', 'og_description', 'canonical_url', 'role', 'client_display_name'],
    ];

    /**
     * @param  array<string, mixed>  $payload
     * @return array<int, string>
     */
    public function validate(Model $target, array $payload): array
    {
        $errors = [];

        if (($payload['target']['id'] ?? null) != $target->getKey()) {
            $errors[] = 'Proposal target id does not match selected content.';
        }

        $operations = $payload['operations'] ?? [];

        if (! is_array($operations)) {
            return ['Operations must be an array.'];
        }

        if ($operations === []) {
            $errors[] = 'Proposal contains no operations.';
        }

        foreach ($operations as $index => $operation) {
            $errors = array_merge($errors, $this->validateOperation($target, $operation, $index));
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateOperation(Model $target, array $operation, int $index): array
    {
        $errors = [];
        $op = $operation['op'] ?? null;

        if (! in_array($op, self::ALLOWED_OPS, true)) {
            return ["Operation {$index}: unknown op [{$op}]."];
        }

        return match ($op) {
            'replace_field' => $this->validateReplaceField($target, $operation, $index),
            'replace_block_fields' => $this->validateReplaceBlockFields($target, $operation, $index),
            'insert_block' => $this->validateInsertBlock($target, $operation, $index),
            'remove_block' => $this->validateRemoveBlock($target, $operation, $index),
            'move_block' => $this->validateMoveBlock($target, $operation, $index),
            'change_slug' => $this->validateSlugChange($operation, $index),
            default => ["Operation {$index}: {$op} is not enabled in version one."],
        };
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateReplaceField(Model $target, array $operation, int $index): array
    {
        $type = ContentTargetResolver::fromModel($target)->value;
        $field = $operation['field'] ?? null;
        $value = $operation['value'] ?? null;

        if (! is_string($field) || ! in_array($field, self::FIELD_WHITELIST[$type] ?? [], true)) {
            return ["Operation {$index}: field [{$field}] is not allowed."];
        }

        if (! is_string($value)) {
            return ["Operation {$index}: value must be a string."];
        }

        return $this->unsafeContentErrors($value, "Operation {$index}");
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateReplaceBlockFields(Model $target, array $operation, int $index): array
    {
        $blocks = $this->blocksForTarget($target);
        $block = $this->resolveBlock($blocks, $operation);

        if ($block === null) {
            return ["Operation {$index}: target block not found."];
        }

        $fields = $operation['fields'] ?? [];
        $preserve = $operation['preserve'] ?? [];

        if (! is_array($fields) || $fields === []) {
            return ["Operation {$index}: fields are required."];
        }

        $merged = $block;
        foreach ($fields as $key => $value) {
            if (in_array($key, $preserve, true)) {
                continue;
            }
            if (! is_string($value) && ! is_bool($value) && ! is_null($value)) {
                return ["Operation {$index}: field [{$key}] has invalid type."];
            }
            $merged[$key] = $value;
            $errors = $this->unsafeContentErrors(is_string($value) ? $value : '', "Operation {$index}");
            if ($errors !== []) {
                return $errors;
            }
        }

        return BlockRegistry::validate($merged);
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateInsertBlock(Model $target, array $operation, int $index): array
    {
        $block = $operation['block'] ?? null;

        if (! is_array($block)) {
            return ["Operation {$index}: block payload is required."];
        }

        return BlockRegistry::validate($block);
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateRemoveBlock(Model $target, array $operation, int $index): array
    {
        $blocks = $this->blocksForTarget($target);

        return $this->resolveBlock($blocks, $operation) === null
            ? ["Operation {$index}: target block not found."]
            : [];
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateMoveBlock(Model $target, array $operation, int $index): array
    {
        $blocks = $this->blocksForTarget($target);
        $toIndex = $operation['to_index'] ?? null;

        if (! is_int($toIndex)) {
            return ["Operation {$index}: to_index must be an integer."];
        }

        return $this->resolveBlock($blocks, $operation) === null
            ? ["Operation {$index}: target block not found."]
            : [];
    }

    /**
     * @param  array<string, mixed>  $operation
     * @return array<int, string>
     */
    private function validateSlugChange(array $operation, int $index): array
    {
        $slug = $operation['slug'] ?? '';

        if (! is_string($slug) || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return ["Operation {$index}: slug format is invalid."];
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function blocksForTarget(Model $target): array
    {
        return match ($target::class) {
            Page::class, Project::class => $target->blocks ?? [],
            Post::class => $target->body ?? [],
            default => [],
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @param  array<string, mixed>  $operation
     * @return array<string, mixed>|null
     */
    private function resolveBlock(array $blocks, array $operation): ?array
    {
        if (isset($operation['block_index']) && isset($blocks[$operation['block_index']])) {
            return $blocks[$operation['block_index']];
        }

        if (! empty($operation['block_id'])) {
            foreach ($blocks as $block) {
                if (($block['anchor_id'] ?? null) === $operation['block_id']) {
                    return $block;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function unsafeContentErrors(string $value, string $prefix): array
    {
        $errors = [];
        $lower = strtolower($value);

        if (str_contains($lower, '<script') || str_contains($lower, 'javascript:') || str_contains($lower, 'onerror=')) {
            $errors[] = "{$prefix}: unsafe HTML or script content is not allowed.";
        }

        return $errors;
    }
}
