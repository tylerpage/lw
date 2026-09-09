<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\ContentTargetType;
use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Concerns\InteractsWithAiSeoGeneration;
use App\Filament\Resources\Concerns\InteractsWithContentWorkshopOnCreate;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreatePost extends CreateRecord
{
    use InteractsWithAiSeoGeneration;
    use InteractsWithContentWorkshopOnCreate;

    protected static string $resource = PostResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Post);
    }

    protected function contentWorkshopTargetType(): ContentTargetType
    {
        return ContentTargetType::Post;
    }

    protected function getHeaderActions(): array
    {
        return $this->getContentWorkshopCreateHeaderActions();
    }
}
