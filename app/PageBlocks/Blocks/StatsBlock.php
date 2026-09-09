<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class StatsBlock extends Block
{
    public ?string $heading = null;

    public array $stats = [];

    public static function type(): string
    {
        return 'stats';
    }

    public static function label(): string
    {
        return 'Stats';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'stats' => $this->stats,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->stats = $data['stats'] ?? $block->stats;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['stats'])) {
            $errors[] = 'stats is required.';
        }

        return $errors;
    }
}
