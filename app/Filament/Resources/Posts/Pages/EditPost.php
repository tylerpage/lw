<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Concerns\InteractsWithAiSeoGeneration;
use App\Filament\Resources\Concerns\InteractsWithContentRevisions;
use App\Filament\Resources\Concerns\InteractsWithContentSlug;
use App\Filament\Resources\Concerns\InteractsWithContentWorkshop;
use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditPost extends EditRecord
{
    use InteractsWithAiSeoGeneration;
    use InteractsWithContentRevisions;
    use InteractsWithContentSlug;
    use InteractsWithContentWorkshop;

    protected static string $resource = PostResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Post);
    }

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getContentWorkshopHeaderActions(),
            DeleteAction::make(),
        ];
    }
}
