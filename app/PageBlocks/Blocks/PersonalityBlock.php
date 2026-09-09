<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class PersonalityBlock extends Block
{
    public ?string $status_line = null;

    public ?string $body = null;

    public static function type(): string
    {
        return 'personality';
    }

    public static function label(): string
    {
        return 'Personality';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'status_line' => $this->status_line,
            'body' => $this->body,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->status_line = $data['status_line'] ?? $block->status_line;
        $block->body = $data['body'] ?? $block->body;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);

        return $errors;
    }
}
