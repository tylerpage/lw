<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Concerns\InteractsWithAiSeoGeneration;
use App\Filament\Resources\Concerns\InteractsWithContentRevisions;
use App\Filament\Resources\Concerns\InteractsWithContentWorkshop;
use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditPage extends EditRecord
{
    use InteractsWithAiSeoGeneration;
    use InteractsWithContentRevisions;
    use InteractsWithContentWorkshop;

    protected static string $resource = PageResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Page);
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getContentWorkshopHeaderActions(),
            DeleteAction::make(),
        ];
    }
}
