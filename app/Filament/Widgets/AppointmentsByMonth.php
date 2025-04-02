<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class AppointmentsByMonth extends ChartWidget
{
    protected static ?string $heading = 'Monthly Appointment Trends';

    protected static ?int $sort = 4;

    protected static ?string $maxHeight = '250px';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Get the last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $data[] = Appointment::whereYear('appointment_date', $month->year)
                ->whereMonth('appointment_date', $month->month)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Appointments',
                    'data' => $data,
                    'borderColor' => 'rgba(79, 70, 229, 0.9)',  // Modern indigo color
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)', // Lighter fill
                    'borderWidth' => 2,
                    'pointBackgroundColor' => 'rgba(79, 70, 229, 1)',
                    'pointBorderColor' => '#ffffff',
                    'pointHoverBackgroundColor' => '#ffffff',
                    'pointHoverBorderColor' => 'rgba(79, 70, 229, 1)',
                    'borderJoinStyle' => 'round',
                    'tension' => 0.4, // Smoother curve
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
