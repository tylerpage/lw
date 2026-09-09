<?php

namespace App\Filament\Forms;

class BlockStateAdapter
{
    /**
     * Convert flat stored blocks to Filament Builder state ({ type, data }).
     *
     * @param  array<int, array<string, mixed>>|null  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function toBuilder(?array $blocks): array
    {
        return collect($blocks ?? [])
            ->map(function (array $block): array {
                $type = $block['type'] ?? null;
                $data = $block;
                unset($data['type']);

                return [
                    'type' => $type,
                    'data' => $data,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Convert Filament Builder state back to flat stored blocks.
     *
     * @param  array<int, array<string, mixed>>|null  $blocks
     * @return array<int, array<string, mixed>>
     */
    public static function fromBuilder(?array $blocks): array
    {
        return collect($blocks ?? [])
            ->map(function (array $block): array {
                $type = $block['type'] ?? null;
                $data = $block['data'] ?? [];

                return array_merge(['type' => $type], $data);
            })
            ->values()
            ->all();
    }
}
