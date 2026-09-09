<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class RichTextBlock extends Block
{
    public string $content = '';

    public static function type(): string
    {
        return 'rich_text';
    }

    public static function label(): string
    {
        return 'Rich Text';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'content' => $this->content,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->content = $data['content'] ?? $block->content;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['content'])) {
            $errors[] = 'content is required.';
        }

        return $errors;
    }
}
