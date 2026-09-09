<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class QuoteBlock extends Block
{
    public string $quote = '';

    public ?string $attribution = null;

    public static function type(): string
    {
        return 'quote';
    }

    public static function label(): string
    {
        return 'Quote';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'quote' => $this->quote,
            'attribution' => $this->attribution,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->quote = $data['quote'] ?? $block->quote;
        $block->attribution = $data['attribution'] ?? $block->attribution;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['quote'])) {
            $errors[] = 'quote is required.';
        }

        return $errors;
    }
}
