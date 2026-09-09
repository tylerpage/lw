<?php

namespace App\Rules;

use App\Filament\Forms\BlockStateAdapter;
use App\PageBlocks\BlockRegistry;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidBlockArray implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('Content blocks must be an array.');

            return;
        }

        $blocks = $this->normalizeBlocks($value);

        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                $fail("Block {$index}: must be an object.");

                continue;
            }

            $type = $block['type'] ?? 'unknown';

            foreach (BlockRegistry::validate($block) as $error) {
                $fail("Block {$index} ({$type}): {$error}");
            }
        }
    }

    /**
     * Filament Builder validates before dehydrateStateUsing, so blocks arrive as
     * { type, data } rather than the flat persisted shape.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function normalizeBlocks(array $blocks): array
    {
        $usesBuilderShape = collect($blocks)->contains(
            fn (mixed $block): bool => is_array($block) && array_key_exists('data', $block),
        );

        return $usesBuilderShape
            ? BlockStateAdapter::fromBuilder($blocks)
            : $blocks;
    }
}
