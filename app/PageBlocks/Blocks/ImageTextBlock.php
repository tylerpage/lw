<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class ImageTextBlock extends Block
{
    public ?string $heading = null;

    public string $content = '';

    public string $image = '';

    public string $image_alt = '';

    public string $image_position = '';

    public static function type(): string
    {
        return 'image_text';
    }

    public static function label(): string
    {
        return 'Image Text';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'content' => $this->content,
            'image' => $this->image,
            'image_alt' => $this->image_alt,
            'image_position' => $this->image_position,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->content = $data['content'] ?? $block->content;
        $block->image = $data['image'] ?? $block->image;
        $block->image_alt = $data['image_alt'] ?? $block->image_alt;
        $block->image_position = $data['image_position'] ?? $block->image_position;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['content'])) {
            $errors[] = 'content is required.';
        }
        if (empty($data['image'])) {
            $errors[] = 'image is required.';
        }
        if (empty($data['image_alt'])) {
            $errors[] = 'image_alt is required.';
        }

        return $errors;
    }
}
