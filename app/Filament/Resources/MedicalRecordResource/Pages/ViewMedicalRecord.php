<?php

namespace App\Filament\Resources\MedicalRecordResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\MedicalRecordResource;

class ViewMedicalRecord extends ViewRecord
{
    protected static string $resource = MedicalRecordResource::class;

    protected static ?string $recordTitleAttribute = 'medical_record_num';

    public function getTitle(): string | Htmlable
    {
        /** @var MedicalRecord */
        $record = $this->getRecord();
        return $record->medical_record_num;
    }

    protected function getActions(): array
    {
        return [];
    }
}
