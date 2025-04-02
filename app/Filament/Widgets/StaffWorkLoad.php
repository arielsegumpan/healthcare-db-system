<?php

namespace App\Filament\Widgets;

use App\Models\MedicalStaff;
use Filament\Widgets\ChartWidget;

class StaffWorkLoad extends ChartWidget
{
    protected static ?string $heading = 'Staff Workload (Records Created)';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '250px';

    protected function getData(): array
    {
        $staff = MedicalStaff::with('user')
            ->withCount('medicalRecords')
            ->orderByDesc('medical_records_count')
            ->limit(10)
            ->get();

        // Generate gradient colors for each bar
        $backgroundColors = [];
        for ($i = 0; $i < count($staff); $i++) {
            $backgroundColors[] = 'rgba(59, 130, 246, 0.85)'; // FilamentPHP v3 blue
        }

        return [
            'datasets' => [
                [
                    'label' => 'Records Created',
                    'data' => $staff->pluck('medical_records_count')->toArray(),
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 0,
                    'borderRadius' => 10,
                    'maxBarThickness' => 15,
                    'hoverBackgroundColor' => 'rgba(37, 99, 235, 0.9)', // Slightly darker on hover
                ],
            ],
            'labels' => $staff->map(function ($staff) {
                return $staff->user->name;
            })->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'color' => 'rgba(107, 114, 128, 0.1)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
