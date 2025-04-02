<?php

namespace App\Filament\Resources\MedicalStaffResource\Pages;

use App\Filament\Resources\MedicalStaffResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMedicalStaff extends CreateRecord
{
    protected static string $resource = MedicalStaffResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
