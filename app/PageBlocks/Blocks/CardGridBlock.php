<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class CardGridBlock extends Block
{
    public ?string $heading = null;

    public array $cards = [];

    public static function type(): string
    {
        return 'card_grid';
    }

    public static function label(): string
    {
        return 'Card Grid';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'cards' => $this->cards,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->cards = $data['cards'] ?? $block->cards;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['cards'])) {
            $errors[] = 'cards is required.';
        }

        return $errors;
    }
}
