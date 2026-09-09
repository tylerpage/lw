<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class TimelineBlock extends Block
{
    public ?string $heading = null;

    public static function type(): string
    {
        return 'timeline';
    }

    public static function label(): string
    {
        return 'Timeline';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);

        return $errors;
    }
}
