<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AppointmentPriorityEnum: string implements HasColor, HasLabel
{
    case LOW = 'low';

    case MEDIUM = 'medium';

    case HIGH = 'high';

    case EMERGENCY = 'emergency';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
            self::EMERGENCY => 'Emergency',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::LOW => 'primary',
            self::MEDIUM => 'success',
            self::HIGH => 'danger',
            self::EMERGENCY => 'warning',
        };
    }
}



