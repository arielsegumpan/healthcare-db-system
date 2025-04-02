<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class AppointmentChart extends ChartWidget
{
    protected static ?string $heading = 'Appointments by Status';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $statuses = ['scheduled', 'completed', 'cancelled', 'rescheduled'];
        $statusLabels = ['Scheduled', 'Completed', 'Cancelled', 'Rescheduled'];

        $datasets = [];

        $colors = [
            'rgba(22, 163, 74, 0.8)',   // Green - Confirmed
            'rgba(245, 158, 11, 0.8)',  // Amber - Pending
            'rgba(59, 130, 246, 0.8)',  // Blue - Completed
            'rgba(239, 68, 68, 0.8)',   // Red - Cancelled
            'rgba(107, 114, 128, 0.8)', // Gray - No Show
        ];

        $counts = [];
        foreach ($statuses as $status) {
            $counts[] = Appointment::where('status', $status)->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Appointments',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,

                ],
            ],
            'labels' => $statusLabels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
