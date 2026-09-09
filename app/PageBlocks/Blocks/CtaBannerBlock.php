<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class CtaBannerBlock extends Block
{
    public string $heading = '';

    public ?string $body = null;

    public string $cta_label = '';

    public string $cta_url = '';

    public static function type(): string
    {
        return 'cta_banner';
    }

    public static function label(): string
    {
        return 'Cta Banner';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'heading' => $this->heading,
            'body' => $this->body,
            'cta_label' => $this->cta_label,
            'cta_url' => $this->cta_url,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->heading = $data['heading'] ?? $block->heading;
        $block->body = $data['body'] ?? $block->body;
        $block->cta_label = $data['cta_label'] ?? $block->cta_label;
        $block->cta_url = $data['cta_url'] ?? $block->cta_url;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['heading'])) {
            $errors[] = 'heading is required.';
        }
        if (empty($data['cta_label'])) {
            $errors[] = 'cta_label is required.';
        }
        if (empty($data['cta_url'])) {
            $errors[] = 'cta_url is required.';
        }

        return $errors;
    }
}
