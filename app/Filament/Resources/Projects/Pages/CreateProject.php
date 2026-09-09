<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Enums\ContentTargetType;
use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Concerns\InteractsWithAiSeoGeneration;
use App\Filament\Resources\Concerns\InteractsWithContentWorkshopOnCreate;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateProject extends CreateRecord
{
    use InteractsWithAiSeoGeneration;
    use InteractsWithContentWorkshopOnCreate;

    protected static string $resource = ProjectResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Project);
    }

    protected function contentWorkshopTargetType(): ContentTargetType
    {
        return ContentTargetType::Project;
    }

    protected function getHeaderActions(): array
    {
        return $this->getContentWorkshopCreateHeaderActions();
    }
}
