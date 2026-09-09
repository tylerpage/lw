<?php

namespace App\Filament\Resources\MediaAssets;

use App\Filament\Resources\MediaAssets\Pages\ManageMediaAssets;
use App\Models\MediaAsset;
use App\Support\MediaLibraryMimeTypes;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

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
                    ->acceptedFileTypes(MediaLibraryMimeTypes::forFileUpload())
                    ->required(fn (?MediaAsset $record): bool => $record === null)
                    ->maxSize(config('content-assistant.media_library.max_file_size_kb', 20480))
                    ->helperText(MediaLibraryMimeTypes::helperText())
                    ->columnSpanFull(),
                TextInput::make('title')
                    ->maxLength(255),
                TextInput::make('alt_text')
                    ->label('Alt text')
                    ->maxLength(255)
                    ->helperText('Used when this image is inserted into content blocks.')
                    ->visible(fn (?MediaAsset $record): bool => $record?->isImage() ?? true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('path')
                    ->label('Preview')
                    ->disk(fn (MediaAsset $record): string => $record->disk)
                    ->square()
                    ->visible(fn (MediaAsset $record): bool => $record->isImage()),
                IconColumn::make('file_type')
                    ->label('Type')
                    ->state(fn (): string => 'file')
                    ->icon(fn (MediaAsset $record): string|BackedEnum => match ($record->fileTypeLabel()) {
                        'PDF' => Heroicon::OutlinedDocumentText,
                        'Document' => Heroicon::OutlinedDocument,
                        'Text' => Heroicon::OutlinedDocumentText,
                        default => Heroicon::OutlinedPaperClip,
                    })
                    ->visible(fn (MediaAsset $record): bool => ! $record->isImage()),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('format')
                    ->label('Format')
                    ->state(fn (MediaAsset $record): string => $record->fileTypeLabel())
                    ->badge(),
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
