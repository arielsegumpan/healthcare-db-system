<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class PriorityAppointmentChart extends ChartWidget
{
    protected static ?string $heading = 'Appointments by Priority';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '250px';
    protected function getData(): array
    {
        $priorities = ['low', 'medium', 'high', 'emergency'];
        $priorityLabels = ['Low', 'Medium', 'High', 'emergency'];

        $datasets = [];

        // FilamentPHP v3 modern colors with proper opacity
        $colors = [
            'rgba(34, 197, 94, 0.8)',   // Green (Low)
            'rgba(245, 158, 11, 0.8)',  // Amber (Medium)
            'rgba(249, 115, 22, 0.8)',  // Orange (High)
            'rgba(239, 68, 68, 0.8)',   // Red (Urgent)
        ];

        $counts = [];
        foreach ($priorities as $priority) {
            $counts[] = Appointment::where('priority', $priority)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Priority',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'borderColor' => 'rgba(255, 255, 255, 0.6)',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $priorityLabels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
