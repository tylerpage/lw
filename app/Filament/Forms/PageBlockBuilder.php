<?php

namespace App\Filament\Forms;

use App\Rules\ValidBlockArray;
use Filament\Forms\Components\Builder;

class PageBlockBuilder
{
    public static function make(string $name): Builder
    {
        return Builder::make($name)
            ->label('Content blocks')
            ->blocks(BlockFormSchemas::allBlocks())
            ->blockPickerColumns(3)
            ->collapsible()
            ->cloneable()
            ->reorderable()
            ->formatStateUsing(fn (?array $state): array => BlockStateAdapter::toBuilder($state))
            ->dehydrateStateUsing(fn (?array $state): array => BlockStateAdapter::fromBuilder($state))
            ->rules([new ValidBlockArray])
            ->columnSpanFull();
    }
}
