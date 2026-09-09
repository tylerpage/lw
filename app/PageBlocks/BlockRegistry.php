<?php

namespace App\PageBlocks;

use App\PageBlocks\Blocks\CapabilitiesGridBlock;
use App\PageBlocks\Blocks\CardGridBlock;
use App\PageBlocks\Blocks\CtaBannerBlock;
use App\PageBlocks\Blocks\EmbedBlock;
use App\PageBlocks\Blocks\FaqBlock;
use App\PageBlocks\Blocks\FeaturedPostsBlock;
use App\PageBlocks\Blocks\FeaturedProjectsBlock;
use App\PageBlocks\Blocks\FullWidthImageBlock;
use App\PageBlocks\Blocks\HeroBlock;
use App\PageBlocks\Blocks\ImageTextBlock;
use App\PageBlocks\Blocks\LogoStripBlock;
use App\PageBlocks\Blocks\PersonalityBlock;
use App\PageBlocks\Blocks\QuoteBlock;
use App\PageBlocks\Blocks\RichTextBlock;
use App\PageBlocks\Blocks\SpacerBlock;
use App\PageBlocks\Blocks\StatsBlock;
use App\PageBlocks\Blocks\TestimonialsBlock;
use App\PageBlocks\Blocks\TimelineBlock;
use InvalidArgumentException;

class BlockRegistry
{
    /** @var array<string, class-string<Block>> */
    protected static array $blocks = [
        'hero' => HeroBlock::class,
        'rich_text' => RichTextBlock::class,
        'image_text' => ImageTextBlock::class,
        'full_width_image' => FullWidthImageBlock::class,
        'cta_banner' => CtaBannerBlock::class,
        'card_grid' => CardGridBlock::class,
        'capabilities_grid' => CapabilitiesGridBlock::class,
        'featured_projects' => FeaturedProjectsBlock::class,
        'featured_posts' => FeaturedPostsBlock::class,
        'testimonials' => TestimonialsBlock::class,
        'stats' => StatsBlock::class,
        'timeline' => TimelineBlock::class,
        'logo_strip' => LogoStripBlock::class,
        'faq' => FaqBlock::class,
        'quote' => QuoteBlock::class,
        'embed' => EmbedBlock::class,
        'spacer' => SpacerBlock::class,
        'personality' => PersonalityBlock::class,
    ];

    public static function all(): array
    {
        return static::$blocks;
    }

    public static function fromArray(array $data): Block
    {
        $type = $data['type'] ?? null;

        if (! $type || ! isset(static::$blocks[$type])) {
            throw new InvalidArgumentException("Unknown block type: {$type}");
        }

        $class = static::$blocks[$type];

        return $class::fromArray($data);
    }

    public static function validate(array $data): array
    {
        $type = $data['type'] ?? null;

        if (! $type || ! isset(static::$blocks[$type])) {
            return ['Unknown block type.'];
        }

        return static::$blocks[$type]::validate($data);
    }
}
