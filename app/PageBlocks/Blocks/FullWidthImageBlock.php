<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class FullWidthImageBlock extends Block
{
    public string $image = '';

    public string $image_alt = '';

    public ?string $caption = null;

    public static function type(): string
    {
        return 'full_width_image';
    }

    public static function label(): string
    {
        return 'Full Width Image';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'image' => $this->image,
            'image_alt' => $this->image_alt,
            'caption' => $this->caption,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->image = $data['image'] ?? $block->image;
        $block->image_alt = $data['image_alt'] ?? $block->image_alt;
        $block->caption = $data['caption'] ?? $block->caption;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['image'])) {
            $errors[] = 'image is required.';
        }
        if (empty($data['image_alt'])) {
            $errors[] = 'image_alt is required.';
        }

        return $errors;
    }
}
