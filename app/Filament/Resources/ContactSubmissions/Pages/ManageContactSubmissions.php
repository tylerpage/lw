<?php

namespace App\Filament\Resources\ContactSubmissions\Pages;

use App\Filament\Resources\ContactSubmissions\ContactSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageContactSubmissions extends ManageRecords
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
