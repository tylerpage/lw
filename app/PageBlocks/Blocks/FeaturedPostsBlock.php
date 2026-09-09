<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class FeaturedPostsBlock extends Block
{
    public ?string $heading = null;

    public int $limit = 3;

    public static function type(): string
    {
        return 'featured_posts';
    }

    public static function label(): string
    {
        return 'Featured Posts';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'limit' => $this->limit,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->limit = $data['limit'] ?? $block->limit;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);

        return $errors;
    }
}
