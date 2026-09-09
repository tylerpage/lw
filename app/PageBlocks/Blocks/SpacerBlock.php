<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class SpacerBlock extends Block
{
    public string $size = '';

    public static function type(): string
    {
        return 'spacer';
    }

    public static function label(): string
    {
        return 'Spacer';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'size' => $this->size,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->size = $data['size'] ?? $block->size;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['size'])) {
            $errors[] = 'size is required.';
        }

        return $errors;
    }
}
