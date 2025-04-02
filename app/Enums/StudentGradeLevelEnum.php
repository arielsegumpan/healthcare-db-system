<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StudentGradeLevelEnum: string implements HasLabel
{
    case GRADE1 = 'Grade 1';
    case GRADE2 = 'Grade 2';
    case GRADE3 = 'Grade 3';
    case GRADE4 = 'Grade 4';
    case GRADE5 = 'Grade 5';
    case GRADE6 = 'Grade 6';
    case GRADE7 = 'Grade 7';
    case GRADE8 = 'Grade 8';
    case GRADE9 = 'Grade 9';
    case GRADE10 = 'Grade 10';
    case GRADE11 = 'Grade 11';
    case GRADE12 = 'Grade 12';

    public function getLabel(): string
    {
        return match ($this) {
            self::GRADE1 => 'Grade 1',
            self::GRADE2 => 'Grade 2',
            self::GRADE3 => 'Grade 3',
            self::GRADE4 => 'Grade 4',
            self::GRADE5 => 'Grade 5',
            self::GRADE6 => 'Grade 6',
            self::GRADE7 => 'Grade 7',
            self::GRADE8 => 'Grade 8',
            self::GRADE9 => 'Grade 9',
            self::GRADE10 => 'Grade 10',
            self::GRADE11 => 'Grade 11',
            self::GRADE12 => 'Grade 12',
        };
    }
}



