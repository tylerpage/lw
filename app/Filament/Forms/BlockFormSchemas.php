<?php

namespace App\Filament\Forms;

use App\PageBlocks\BlockRegistry;
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
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class BlockFormSchemas
{
    private static function markdownHelperText(): string
    {
        return 'Supports Markdown formatting (headings, bold, lists, links).';
    }

    /**
     * @return array<int, Block>
     */
    public static function allBlocks(): array
    {
        $blocks = [];

        foreach (BlockRegistry::all() as $type => $class) {
            $blocks[] = match ($type) {
                'hero' => self::heroBlock(),
                'rich_text' => self::richTextBlock(),
                'image_text' => self::imageTextBlock(),
                'full_width_image' => self::fullWidthImageBlock(),
                'cta_banner' => self::ctaBannerBlock(),
                'card_grid' => self::cardGridBlock(),
                'capabilities_grid' => self::capabilitiesGridBlock(),
                'featured_projects' => self::featuredProjectsBlock(),
                'featured_posts' => self::featuredPostsBlock(),
                'testimonials' => self::testimonialsBlock(),
                'stats' => self::statsBlock(),
                'timeline' => self::timelineBlock(),
                'logo_strip' => self::logoStripBlock(),
                'faq' => self::faqBlock(),
                'quote' => self::quoteBlock(),
                'embed' => self::embedBlock(),
                'spacer' => self::spacerBlock(),
                'personality' => self::personalityBlock(),
                default => Block::make($type)->label($class::label())->schema(self::baseFields()),
            };
        }

        return $blocks;
    }

    /**
     * @return array<int, Component>
     */
    public static function baseFields(): array
    {
        return [
            Toggle::make('enabled')
                ->default(true),
            TextInput::make('anchor_id')
                ->label('Anchor ID'),
            TextInput::make('background')
                ->label('Background class'),
        ];
    }

    private static function heroBlock(): Block
    {
        return Block::make('hero')
            ->label(HeroBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('headline')
                    ->required(),
                Textarea::make('subheadline')
                    ->rows(3)
                    ->helperText(self::markdownHelperText())
                    ->columnSpanFull(),
                self::imagePath('image'),
                TextInput::make('image_alt'),
                TextInput::make('primary_cta_label'),
                TextInput::make('primary_cta_url'),
                TextInput::make('secondary_cta_label'),
                TextInput::make('secondary_cta_url'),
            ]));
    }

    private static function richTextBlock(): Block
    {
        return Block::make('rich_text')
            ->label(RichTextBlock::label())
            ->schema(array_merge(self::baseFields(), [
                Textarea::make('content')
                    ->required()
                    ->rows(6)
                    ->helperText(self::markdownHelperText())
                    ->columnSpanFull(),
            ]));
    }

    private static function imageTextBlock(): Block
    {
        return Block::make('image_text')
            ->label(ImageTextBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                Textarea::make('content')
                    ->required()
                    ->rows(4)
                    ->helperText(self::markdownHelperText())
                    ->columnSpanFull(),
                self::imagePath('image')
                    ->required(),
                TextInput::make('image_alt')
                    ->required(),
                Select::make('image_position')
                    ->options([
                        'left' => 'Left',
                        'right' => 'Right',
                    ])
                    ->default('left'),
            ]));
    }

    private static function fullWidthImageBlock(): Block
    {
        return Block::make('full_width_image')
            ->label(FullWidthImageBlock::label())
            ->schema(array_merge(self::baseFields(), [
                self::imagePath('image')
                    ->required(),
                TextInput::make('image_alt')
                    ->required(),
                TextInput::make('caption'),
            ]));
    }

    private static function ctaBannerBlock(): Block
    {
        return Block::make('cta_banner')
            ->label(CtaBannerBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading')
                    ->required(),
                Textarea::make('body')
                    ->rows(3)
                    ->helperText(self::markdownHelperText())
                    ->columnSpanFull(),
                TextInput::make('cta_label')
                    ->required(),
                TextInput::make('cta_url')
                    ->required(),
            ]));
    }

    private static function cardGridBlock(): Block
    {
        return Block::make('card_grid')
            ->label(CardGridBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                Repeater::make('cards')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        Textarea::make('body')
                            ->rows(2)
                            ->helperText(self::markdownHelperText()),
                        TextInput::make('url'),
                        self::imagePath('image'),
                    ])
                    ->defaultItems(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]));
    }

    private static function capabilitiesGridBlock(): Block
    {
        return Block::make('capabilities_grid')
            ->label(CapabilitiesGridBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading')
                    ->helperText('Capability groups are managed under Admin → Capabilities.'),
            ]));
    }

    private static function featuredProjectsBlock(): Block
    {
        return Block::make('featured_projects')
            ->label(FeaturedProjectsBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                TextInput::make('limit')
                    ->numeric()
                    ->default(3)
                    ->helperText('Shows featured projects from Admin → Projects.'),
            ]));
    }

    private static function featuredPostsBlock(): Block
    {
        return Block::make('featured_posts')
            ->label(FeaturedPostsBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                TextInput::make('limit')
                    ->numeric()
                    ->default(3)
                    ->helperText('Shows featured posts from Admin → Posts.'),
            ]));
    }

    private static function testimonialsBlock(): Block
    {
        return Block::make('testimonials')
            ->label(TestimonialsBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                TextInput::make('limit')
                    ->numeric()
                    ->default(3)
                    ->helperText('Shows testimonials from Admin → Testimonials.'),
            ]));
    }

    private static function statsBlock(): Block
    {
        return Block::make('stats')
            ->label(StatsBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                Repeater::make('stats')
                    ->schema([
                        TextInput::make('label')
                            ->required(),
                        TextInput::make('value')
                            ->required(),
                    ])
                    ->defaultItems(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]));
    }

    private static function timelineBlock(): Block
    {
        return Block::make('timeline')
            ->label(TimelineBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading')
                    ->helperText('Career entries are managed under Admin → Career Timeline.'),
            ]));
    }

    private static function logoStripBlock(): Block
    {
        return Block::make('logo_strip')
            ->label(LogoStripBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                Repeater::make('logos')
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        self::imagePath('image'),
                        TextInput::make('url'),
                    ])
                    ->defaultItems(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]));
    }

    private static function faqBlock(): Block
    {
        return Block::make('faq')
            ->label(FaqBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('heading'),
                Repeater::make('items')
                    ->schema([
                        TextInput::make('question')
                            ->required(),
                        Textarea::make('answer')
                            ->required()
                            ->rows(3)
                            ->helperText(self::markdownHelperText()),
                    ])
                    ->defaultItems(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]));
    }

    private static function quoteBlock(): Block
    {
        return Block::make('quote')
            ->label(QuoteBlock::label())
            ->schema(array_merge(self::baseFields(), [
                Textarea::make('quote')
                    ->required()
                    ->rows(4)
                    ->helperText(self::markdownHelperText())
                    ->columnSpanFull(),
                TextInput::make('attribution'),
            ]));
    }

    private static function embedBlock(): Block
    {
        return Block::make('embed')
            ->label(EmbedBlock::label())
            ->schema(array_merge(self::baseFields(), [
                Select::make('provider')
                    ->options([
                        'youtube' => 'YouTube',
                        'vimeo' => 'Vimeo',
                        'spotify' => 'Spotify',
                        'other' => 'Other',
                    ])
                    ->required(),
                TextInput::make('url')
                    ->url()
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('title'),
            ]));
    }

    private static function spacerBlock(): Block
    {
        return Block::make('spacer')
            ->label(SpacerBlock::label())
            ->schema(array_merge(self::baseFields(), [
                Select::make('size')
                    ->options([
                        'sm' => 'Small',
                        'md' => 'Medium',
                        'lg' => 'Large',
                    ])
                    ->required()
                    ->default('md'),
            ]));
    }

    private static function personalityBlock(): Block
    {
        return Block::make('personality')
            ->label(PersonalityBlock::label())
            ->schema(array_merge(self::baseFields(), [
                TextInput::make('status_line'),
                Textarea::make('body')
                    ->rows(4)
                    ->helperText(self::markdownHelperText())
                    ->columnSpanFull(),
            ]));
    }

    private static function imagePath(string $name): TextInput
    {
        return TextInput::make($name)
            ->helperText('Path relative to the public disk, e.g. images/photo.jpg');
    }
}
