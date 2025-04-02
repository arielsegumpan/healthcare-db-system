<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Student;
use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    // protected static ?string $heading = 'Health Center Statistics';
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Appointments', Appointment::count())
                ->description('Total appointments recorded')
                ->descriptionIcon('heroicon-m-calendar')
                ->chart(
                    $this->getAppointmentsTrend()
                )
                ->color('primary'),

            Stat::make('Pending Appointments', Appointment::where('status', 'pending')->count())
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Completed Appointments', Appointment::where('status', 'completed')->count())
                ->description(Carbon::now()->format('F Y'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Students', Student::count())
                ->description('Registered students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }

    protected function getAppointmentsTrend(): array
    {
        // Get appointments for the last 7 days
        $appointments = Appointment::where('appointment_date', '>=', now()->subDays(7))
            ->selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $count = $appointments->where('date', $date)->first()?->count ?? 0;
            $trend[] = $count;
        }

        return $trend;
    }
}
