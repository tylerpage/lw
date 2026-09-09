<?php

namespace App\Filament\Forms;

use App\Enums\PublishStatus;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ContentResourceForm
{
    public static function configure(Schema $schema, ContentFormType $type): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Tabs::make('Content editor')
                    ->tabs([
                        Tab::make('General')
                            ->schema(self::generalFields($type)),
                        Tab::make('Content')
                            ->schema([
                                PageBlockBuilder::make(self::blocksField($type)),
                            ]),
                        Tab::make('SEO')
                            ->schema(self::seoFields()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, Component>
     */
    private static function generalFields(ContentFormType $type): array
    {
        $shared = [
            TextInput::make('title')
                ->required()
                ->columnSpanFull(),
            TextInput::make('slug')
                ->required(),
            Select::make('status')
                ->options(PublishStatus::class)
                ->default('draft')
                ->required(),
            DateTimePicker::make('published_at'),
        ];

        return match ($type) {
            ContentFormType::Page => array_merge($shared, [
                TextInput::make('nav_label'),
                TextInput::make('template')
                    ->required()
                    ->default('default'),
            ]),
            ContentFormType::Post => array_merge($shared, [
                Textarea::make('excerpt')
                    ->rows(3)
                    ->helperText('Supports Markdown formatting.')
                    ->columnSpanFull(),
                DateTimePicker::make('display_updated_at'),
                Select::make('author_id')
                    ->relationship('author', 'name'),
                Toggle::make('featured')
                    ->default(false),
                TextInput::make('reading_time_minutes')
                    ->numeric(),
                FileUpload::make('hero_image')
                    ->image(),
            ]),
            ContentFormType::Project => array_merge($shared, [
                Textarea::make('card_summary')
                    ->rows(3)
                    ->helperText('Supports Markdown formatting.')
                    ->columnSpanFull(),
                Toggle::make('featured')
                    ->default(false),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                DatePicker::make('project_date'),
                Toggle::make('confidential')
                    ->default(false),
                TextInput::make('client_display_name'),
                TextInput::make('role'),
                Textarea::make('collaborators')
                    ->rows(2)
                    ->columnSpanFull(),
                FileUpload::make('hero_image')
                    ->image(),
            ]),
        };
    }

    /**
     * @return array<int, Component>
     */
    private static function seoFields(): array
    {
        return [
            ViewField::make('ai_seo_actions')
                ->hiddenLabel()
                ->view('filament.forms.components.ai-seo-actions')
                ->dehydrated(false)
                ->columnSpanFull(),
            TextInput::make('seo_title'),
            Textarea::make('seo_description')
                ->rows(3)
                ->columnSpanFull(),
            TextInput::make('canonical_url')
                ->url(),
            TextInput::make('og_title'),
            Textarea::make('og_description')
                ->rows(3)
                ->columnSpanFull(),
            FileUpload::make('og_image')
                ->image(),
            Toggle::make('index')
                ->default(true),
            Toggle::make('follow')
                ->default(true),
        ];
    }

    public static function blocksField(ContentFormType $type): string
    {
        return match ($type) {
            ContentFormType::Post => 'body',
            default => 'blocks',
        };
    }
}
