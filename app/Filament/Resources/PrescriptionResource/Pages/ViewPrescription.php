<?php

namespace App\Filament\Resources\PrescriptionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\PrescriptionResource;

class ViewPrescription extends ViewRecord
{
    protected static string $resource = PrescriptionResource::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    public function getTitle(): string | Htmlable
    {
        /** @var Prescription */
        $record = $this->getRecord();
        return $record->medicalRecord->student->full_name;
    }

    protected function getActions(): array
    {
        return [];
    }
}
