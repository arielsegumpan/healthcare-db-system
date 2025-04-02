<?php

namespace App\Filament\Resources\MedicalStaffResource\Pages;

use App\Filament\Resources\MedicalStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicalStaff extends ListRecords
{
    protected static string $resource = MedicalStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->icon('heroicon-m-plus')->label(__('New Medical Staff')),
        ];
    }

}
