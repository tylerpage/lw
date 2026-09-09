<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class LogoStripBlock extends Block
{
    public ?string $heading = null;

    public array $logos = [];

    public static function type(): string
    {
        return 'logo_strip';
    }

    public static function label(): string
    {
        return 'Logo Strip';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'logos' => $this->logos,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->logos = $data['logos'] ?? $block->logos;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['logos'])) {
            $errors[] = 'logos is required.';
        }

        return $errors;
    }
}
