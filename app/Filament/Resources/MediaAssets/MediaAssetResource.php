<?php

namespace App\Filament\Resources\MediaAssets;

use App\Filament\Resources\MediaAssets\Pages\ManageMediaAssets;
use App\Models\MediaAsset;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Media Library';

    protected static ?string $modelLabel = 'media file';

    protected static ?string $pluralModelLabel = 'Media Library';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        $disk = config('content-assistant.media_library.disk', 'public');
        $directory = config('content-assistant.media_library.directory', 'media-library');

        return $schema
            ->components([
                FileUpload::make('path')
                    ->label('File')
                    ->disk($disk)
                    ->directory($directory)
                    ->image()
                    ->required(fn (?MediaAsset $record): bool => $record === null)
                    ->maxSize(config('content-assistant.media_library.max_file_size_kb', 10240))
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->maxLength(255),
                TextInput::make('alt_text')
                    ->label('Alt text')
                    ->maxLength(255)
                    ->helperText('Used when this image is inserted into content blocks.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Preview')
                    ->disk(fn (MediaAsset $record): string => $record->disk)
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('public_path')
                    ->label('CMS path')
                    ->state(fn (MediaAsset $record): string => $record->publicPath())
                    ->copyable()
                    ->copyMessage('Path copied')
                    ->wrap(),
                TextColumn::make('mime_type')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('size')
                    ->formatStateUsing(fn (int $state): string => number_format($state / 1024, 1).' KB')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMediaAssets::route('/'),
        ];
    }
}
