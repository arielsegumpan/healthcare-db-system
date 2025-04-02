<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum RecordTypeEnum: string implements HasIcon, HasColor, HasLabel
{
    case REGULARCHECKUP = 'Regular Checkup';

    case EMERGENCY = 'Emergency';

    case FOLLOWUP = 'Follow-up';

    case VACCINATION = 'Vaccination';


    public function getLabel(): string
    {
        return match ($this) {
            self::REGULARCHECKUP => 'Regular Checkup',
            self::EMERGENCY => 'Emergency',
            self::FOLLOWUP => 'Follow-up',
            self::VACCINATION => 'Vaccination',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::REGULARCHECKUP => 'primary',
            self::EMERGENCY => 'success',
            self::FOLLOWUP => 'danger',
            self::VACCINATION => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::REGULARCHECKUP => 'heroicon-m-clipboard-document-list',
            self::EMERGENCY => 'heroicon-m-exclamation-triangle',
            self::FOLLOWUP => 'heroicon-m-clock',
            self::VACCINATION => 'heroicon-m-credit-card',
        };
    }
}



