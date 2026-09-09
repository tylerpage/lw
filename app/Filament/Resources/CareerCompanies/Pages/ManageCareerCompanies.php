<?php

namespace App\Filament\Resources\CareerCompanies\Pages;

use App\Filament\Resources\CareerCompanies\CareerCompanyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCareerCompanies extends ManageRecords
{
    protected static string $resource = CareerCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
