<?php

namespace App\Filament\Resources\CareerCompanies;

use App\Filament\Resources\CareerCompanies\Pages\ManageCareerCompanies;
use App\Models\CareerCompany;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CareerCompanyResource extends Resource
{
    protected static ?string $model = CareerCompany::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBriefcase;

    protected static ?string $navigationLabel = 'Career Timeline';

    protected static ?string $modelLabel = 'company';

    protected static ?string $pluralModelLabel = 'Career timeline';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('location')
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Repeater::make('roles')
                    ->relationship('roles')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('started_at')
                            ->required(),
                        DatePicker::make('ended_at')
                            ->helperText('Leave empty for current role.'),
                        Textarea::make('summary')
                            ->rows(3)
                            ->helperText('Supports Markdown formatting.')
                            ->columnSpanFull(),
                        TagsInput::make('highlights')
                            ->helperText('Optional bullet highlights for this role.')
                            ->columnSpanFull(),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ])
                    ->orderColumn('sort_order')
                    ->defaultItems(1)
                    ->collapsible()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('location')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('roles_count')
                    ->counts('roles')
                    ->label('Roles'),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
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
            'index' => ManageCareerCompanies::route('/'),
        ];
    }
}
