<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AppointmentStatusEnum: string implements HasIcon, HasColor, HasLabel
{
    case SCHEDULED = 'scheduled';

    case COMPLETED = 'completed';

    case CANCELLED = 'cancelled';

    case RESCHEDULED = 'rescheduled';

    public function getLabel(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::RESCHEDULED => 'Rescheduled',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::SCHEDULED => 'primary',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::RESCHEDULED => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::SCHEDULED => 'heroicon-m-calendar',
            self::COMPLETED => 'heroicon-m-check-circle',
            self::CANCELLED => 'heroicon-m-x-circle',
            self::RESCHEDULED => 'heroicon-m-calendar',
        };
    }
}



