<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\AppointmentResource;


class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {

        if(auth()->user()->hasRole('student')) {
            $data['student_id'] = auth()->user()->student->id;
            $data['created_by'] = auth()->user()->id;
            $data['status'] = 'scheduled';
        }else{
            $data['created_by'] = auth()->user()->id;
            $data['status'] = 'scheduled';
        }

        return $data;
    }
}
