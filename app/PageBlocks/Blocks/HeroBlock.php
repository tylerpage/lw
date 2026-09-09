<?php

namespace App\PageBlocks\Blocks;

use App\PageBlocks\Block;

class HeroBlock extends Block
{
    public string $headline = '';

    public ?string $subheadline = null;

    public ?string $image = null;

    public ?string $image_alt = null;

    public ?string $primary_cta_label = null;

    public ?string $primary_cta_url = null;

    public ?string $secondary_cta_label = null;

    public ?string $secondary_cta_url = null;

    public static function type(): string
    {
        return 'hero';
    }

    public static function label(): string
    {
        return 'Hero';
    }

    public function toArray(): array
    {
        return array_merge($this->baseArray(), [
            'headline' => $this->headline,
            'subheadline' => $this->subheadline,
            'image' => $this->image,
            'image_alt' => $this->image_alt,
            'primary_cta_label' => $this->primary_cta_label,
            'primary_cta_url' => $this->primary_cta_url,
            'secondary_cta_label' => $this->secondary_cta_label,
            'secondary_cta_url' => $this->secondary_cta_url,
        ]);
    }

    public static function fromArray(array $data): static
    {
        $block = new static;
        static::baseFromArray($data, $block);
        $block->headline = $data['headline'] ?? $block->headline;
        $block->subheadline = $data['subheadline'] ?? $block->subheadline;
        $block->image = $data['image'] ?? $block->image;
        $block->image_alt = $data['image_alt'] ?? $block->image_alt;
        $block->primary_cta_label = $data['primary_cta_label'] ?? $block->primary_cta_label;
        $block->primary_cta_url = $data['primary_cta_url'] ?? $block->primary_cta_url;
        $block->secondary_cta_label = $data['secondary_cta_label'] ?? $block->secondary_cta_label;
        $block->secondary_cta_url = $data['secondary_cta_url'] ?? $block->secondary_cta_url;

        return $block;
    }

    public static function validate(array $data): array
    {
        $errors = parent::validate($data);
        if (empty($data['headline'])) {
            $errors[] = 'headline is required.';
        }

        return $errors;
    }
}
