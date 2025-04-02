<?php

namespace App\Filament\Resources\MedicalStaffResource\Pages;

use App\Filament\Resources\MedicalStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalStaff extends EditRecord
{
    protected static string $resource = MedicalStaffResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
