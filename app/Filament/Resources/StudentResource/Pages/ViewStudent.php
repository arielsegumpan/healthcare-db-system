<?php

namespace App\Filament\Resources\StudentResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\StudentResource;

class ViewStudent extends ViewRecord
{
    protected static string $resource = StudentResource::class;

    protected static ?string $recordTitleAttribute = 'full_name';

    public function getTitle(): string | Htmlable
    {
        /** @var Student */
        $record = $this->getRecord();
        return $record->full_name;
    }

    protected function getActions(): array
    {
        return [];
    }

}
