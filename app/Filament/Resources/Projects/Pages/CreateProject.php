<?php

namespace App\Filament\Resources\Projects\Pages;

use App\Filament\Forms\ContentFormType;
use App\Filament\Forms\ContentResourceForm;
use App\Filament\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Schemas\Schema;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    public function form(Schema $schema): Schema
    {
        return ContentResourceForm::configure($schema, ContentFormType::Project);
    }
}
