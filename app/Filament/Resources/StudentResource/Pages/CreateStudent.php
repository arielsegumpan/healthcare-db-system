<?php

namespace App\Filament\Resources\StudentResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\StudentResource;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {

        $user = auth()->user();

        if($user->hasRole('student')) {
            $data['user_id'] = auth()->id();
        }


        return $data;
    }


}
