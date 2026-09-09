<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Enums\ContentTargetType;
use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Concerns\InteractsWithAiSeoGeneration;
use App\Filament\Resources\Concerns\InteractsWithContentSlug;
use App\Filament\Resources\Concerns\InteractsWithContentWorkshopOnCreate;
use App\Filament\Resources\Pages\PageResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreatePage extends CreateRecord
{
    use InteractsWithAiSeoGeneration;
    use InteractsWithContentSlug;
    use InteractsWithContentWorkshopOnCreate;

    protected static string $resource = PageResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Page);
    }

    protected function contentWorkshopTargetType(): ContentTargetType
    {
        return ContentTargetType::Page;
    }

    protected function getHeaderActions(): array
    {
        return $this->getContentWorkshopCreateHeaderActions();
    }
}
