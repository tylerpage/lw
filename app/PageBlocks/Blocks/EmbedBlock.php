<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class EmbedBlock extends Block
{
    public string $provider = '';

    public string $url = '';

    public ?string $title = null;

    public static function type(): string
    {
        return 'embed';
    }

    public static function label(): string
    {
        return 'Embed';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'provider' => $this->provider,
            'url' => $this->url,
            'title' => $this->title,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->provider = $data['provider'] ?? $block->provider;
        $block->url = $data['url'] ?? $block->url;
        $block->title = $data['title'] ?? $block->title;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['provider'])) {
            $errors[] = 'provider is required.';
        }
        if (empty($data['url'])) {
            $errors[] = 'url is required.';
        }

        return $errors;
    }
}
