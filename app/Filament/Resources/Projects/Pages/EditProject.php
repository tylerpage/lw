<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Concerns\InteractsWithAiSeoGeneration;
use App\Filament\Resources\Concerns\InteractsWithContentSlug;
use App\Filament\Resources\Concerns\InteractsWithContentWorkshop;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditProject extends EditRecord
{
    use InteractsWithAiSeoGeneration;
    use InteractsWithContentSlug;
    use InteractsWithContentWorkshop;

    protected static string $resource = ProjectResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Project);
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getContentWorkshopHeaderActions(),
            DeleteAction::make(),
        ];
    }
}
