<?php

// namespace App\Filament\Widgets;

// use Filament\Tables;
// use Filament\Tables\Table;
// use App\Models\Appointment;
// use Filament\Tables\Columns\TextColumn;
// use Illuminate\Database\Eloquent\Builder;
// use Filament\Widgets\TableWidget as BaseWidget;

// class RecentAppointments extends BaseWidget
// {
//     protected static ?string $heading = 'Upcoming Appointments';

//     protected static ?int $sort = 6;

//     protected int | string | array $columnSpan = 'full';

//     protected function getTableQuery(): Builder
//     {
//         return Appointment::query()
//             ->with(['student', 'creator'])
//             ->where('appointment_date', '>=', now())
//             ->where('status', '!=', 'cancelled')
//             ->latest('appointment_date')
//             ->limit(5);
//     }

//     protected function getTableColumns(): array
//     {
//         return [
//             TextColumn::make('appointment_num')
//                 ->label('ID')
//                 ->searchable(),

//             TextColumn::make('student.first_name')
//                 ->label('Student')
//                 ->formatStateUsing(fn ($record) => $record->student->full_name)
//                 ->searchable(),

//             TextColumn::make('appointment_date')
//                 ->label('Date & Time')
//                 ->dateTime()
//                 ->sortable(),

//             TextColumn::make('status')
//             ->label('Status')
//             ->searchable()
//             ->sortable()
//             ->badge()
//             ->color(function ($state) {
//                 return match ($state){
//                     'scheduled' => 'primary',
//                     'completed' => 'success',
//                     'cancelled' => 'danger',
//                     'rescheduled' => 'warning'
//                 };
//             })
//             ->icon(function ($state){
//                 return match ($state){
//                     'scheduled' => 'heroicon-m-calendar',
//                     'completed' => 'heroicon-m-check-circle',
//                     'cancelled' => 'heroicon-m-x-circle',
//                     'rescheduled' => 'heroicon-m-calendar',
//                 };
//             })
//             ->formatStateUsing(fn(string $state): string => ucwords($state)),

//             TextColumn::make('priority')
//             ->label('Priority')
//             ->searchable()
//             ->sortable()
//             ->badge()
//             ->icon('heroicon-o-check')
//             ->color(function ($state) {
//                 return match ($state) {
//                     'low' => 'primary',
//                     'medium' => 'success',
//                     'high' => 'danger',
//                     'emergency' => 'warning',
//                 };
//             })
//             ->formatStateUsing(fn(string $state): string => ucwords($state)),

//             TextColumn::make('reason')
//                 ->limit(30),
//         ];
//     }
// }


namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Actions\Action;

use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentAppointments extends BaseWidget
{
    protected static ?string $heading = 'Upcoming Appointments';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->with(['student', 'creator'])
                    ->where('appointment_date', '>=', now())
                    ->where('status', '!=', 'cancelled')
                    ->latest('appointment_date')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('appointment_num')
                    ->label('Appointment #')
                    ->searchable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->searchable(),

                TextColumn::make('appointment_date')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'scheduled' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'rescheduled' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn ($state) => match ($state) {
                        'scheduled' => 'heroicon-m-calendar',
                        'completed' => 'heroicon-m-check-circle',
                        'cancelled' => 'heroicon-m-x-circle',
                        'rescheduled' => 'heroicon-m-calendar',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords($state)),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->icon('heroicon-o-check')
                    ->color(fn ($state) => match ($state) {
                        'low' => 'primary',
                        'medium' => 'success',
                        'high' => 'danger',
                        'emergency' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords($state)),

                TextColumn::make('reason')
                    ->limit(30)
                    ->markdown()
                    ->html(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('View')
                    ->url(fn (Appointment $record): string => route('filament.dashboard.resources.appointments.edit', ['record' => $record]))
                    ->icon('heroicon-m-eye')
                    ->button()
                    ->openUrlInNewTab(false)
                    ->color('warning'),
            ]);
    }
}
