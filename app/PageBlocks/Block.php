<?php

namespace App\PageBlocks;

abstract class Block
{
    public function __construct(
        public bool $enabled = true,
        public ?string $anchorId = null,
        public ?string $background = null,
    ) {}

    abstract public static function type(): string;

    abstract public static function label(): string;

    abstract public function toArray(): array;

    abstract public static function fromArray(array $data): static;

    public static function validate(array $data): array
    {
        $errors = [];

        if (! array_key_exists('type', $data) || $data['type'] !== static::type()) {
            $errors[] = 'Invalid block type.';
        }

        return $errors;
    }

    protected function baseArray(): array
    {
        return [
            'type' => static::type(),
            'enabled' => $this->enabled,
            'anchor_id' => $this->anchorId,
            'background' => $this->background,
        ];
    }

    protected static function baseFromArray(array $data, self $block): self
    {
        $block->enabled = $data['enabled'] ?? true;
        $block->anchorId = $data['anchor_id'] ?? null;
        $block->background = $data['background'] ?? null;

        return $block;
    }
}
