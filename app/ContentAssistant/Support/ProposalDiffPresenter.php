<?php

namespace App\ContentAssistant\Support;

use App\PageBlocks\BlockRegistry;

class ProposalDiffPresenter
{
    /**
     * @param  array<int, array<string, mixed>>  $diff
     * @return array<int, array<string, mixed>>
     */
    public function present(array $diff): array
    {
        return collect($diff)
            ->map(fn (array $item): array => $this->presentItem($item))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentItem(array $item): array
    {
        $type = $item['type'] ?? 'unknown';

        return match ($type) {
            'field' => $this->presentField($item),
            'block_fields' => $this->presentBlockFields($item),
            'block_insert' => $this->presentBlockInsert($item),
            'block_remove' => $this->presentBlockRemove($item),
            'block_move' => $this->presentBlockMove($item),
            'slug_change' => $this->presentSlugChange($item),
            default => [
                'type' => $type,
                'title' => $item['label'] ?? 'Change',
                'description' => 'This change could not be displayed in a friendly format.',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentField(array $item): array
    {
        $field = (string) ($item['label'] ?? 'field');

        return [
            'type' => 'field',
            'title' => $this->fieldLabel($field),
            'before' => $this->formatValue($item['before'] ?? null),
            'after' => $this->formatValue($item['after'] ?? null),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentBlockFields(array $item): array
    {
        $before = $item['before'] ?? [];
        $after = $item['after'] ?? [];
        $blockType = (string) ($before['type'] ?? $after['type'] ?? 'block');
        $changes = [];

        foreach ($after as $key => $value) {
            if (in_array($key, ['type', 'enabled'], true)) {
                continue;
            }

            $beforeValue = $before[$key] ?? null;

            if ($beforeValue !== $value) {
                $changes[] = [
                    'field' => $this->fieldLabel($key),
                    'before' => $this->formatValue($beforeValue),
                    'after' => $this->formatValue($value),
                ];
            }
        }

        $preserved = collect($item['preserved'] ?? [])
            ->map(fn (string $key): string => $this->fieldLabel($key))
            ->values()
            ->all();

        return [
            'type' => 'block_fields',
            'title' => $this->blockLabel($blockType),
            'changes' => $changes,
            'preserved' => $preserved,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentBlockInsert(array $item): array
    {
        $block = $item['block'] ?? [];
        $blockType = (string) ($block['type'] ?? 'block');

        return [
            'type' => 'block_insert',
            'title' => 'Add '.$this->blockLabel($blockType, false),
            'description' => 'A new block will be inserted into the page content.',
            'preview' => $this->blockPreviewLines($block),
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentBlockRemove(array $item): array
    {
        $index = $item['block_index'] ?? null;

        return [
            'type' => 'block_remove',
            'title' => 'Remove block',
            'description' => is_int($index)
                ? 'Section '.($index + 1).' will be removed from the page.'
                : 'A block will be removed from the page.',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentBlockMove(array $item): array
    {
        $from = $item['from'] ?? null;
        $to = $item['to'] ?? null;

        return [
            'type' => 'block_move',
            'title' => 'Reorder block',
            'description' => (is_int($from) && is_int($to))
                ? 'Move section '.($from + 1).' to position '.($to + 1).'.'
                : 'A block will be reordered on the page.',
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function presentSlugChange(array $item): array
    {
        return [
            'type' => 'slug_change',
            'title' => 'URL slug',
            'before' => $this->formatValue($item['before'] ?? null),
            'after' => $this->formatValue($item['after'] ?? null),
            'description' => $item['warning'] ?? 'Changing the slug may require a redirect.',
        ];
    }

    private function blockLabel(string $type, bool $withSuffix = true): string
    {
        $class = BlockRegistry::all()[$type] ?? null;
        $label = $class ? $class::label() : str($type)->replace('_', ' ')->title()->toString();

        return $withSuffix ? "{$label} block" : $label;
    }

    private function fieldLabel(string $field): string
    {
        return match ($field) {
            'seo_title' => 'SEO title',
            'seo_description' => 'SEO description',
            'og_title' => 'Open Graph title',
            'og_description' => 'Open Graph description',
            'canonical_url' => 'Canonical URL',
            'headline' => 'Headline',
            'subheadline' => 'Subheadline',
            'primary_cta_label' => 'Primary button label',
            'primary_cta_url' => 'Primary button URL',
            'secondary_cta_label' => 'Secondary button label',
            'secondary_cta_url' => 'Secondary button URL',
            'content' => 'Body text',
            'cta_label' => 'Button label',
            'cta_url' => 'Button URL',
            'image' => 'Image path',
            'image_alt' => 'Image alt text',
            default => str($field)->replace('_', ' ')->title()->toString(),
        };
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '—';
        }

        return (string) $value;
    }

    /**
     * @param  array<string, mixed>  $block
     * @return array<int, string>
     */
    private function blockPreviewLines(array $block): array
    {
        $lines = [];
        $skip = ['type', 'enabled', 'anchor_id', 'background'];

        foreach ($block as $key => $value) {
            if (in_array($key, $skip, true) || $value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            $lines[] = $this->fieldLabel($key).': '.$this->formatValue($value);
        }

        return $lines;
    }
}
