<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class FaqBlock extends Block
{
    public ?string $heading = null;

    public array $items = [];

    public static function type(): string
    {
        return 'faq';
    }

    public static function label(): string
    {
        return 'Faq';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'items' => $this->items,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->items = $data['items'] ?? $block->items;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['items'])) {
            $errors[] = 'items is required.';
        }

        return $errors;
    }
}
