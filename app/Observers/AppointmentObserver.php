<?php

namespace App\Observers;

use App\Filament\Resources\AppointmentResource;
use App\Models\User;
use App\Models\Appointment;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        $creator = auth()->user();

        if ($creator->hasRole(['student'])) {
            $this->notifyAdmin($appointment);
        }

        if ($creator->hasAnyRole(['super-admin', 'admin', 'super_admin', 'super admin','nurse','doctor', 'medical_staff','medical staff'])) {
            $this->notifyStudent($appointment);
        }


    }

    private function notifyStudent(Appointment $appointment): void
    {
        $student = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['student']);
        })->get();

        $notification = $this->createNotification(
            'New Appointment',
            "Your have a new appointment",
            $appointment,
            'primary'
        );

        foreach ($student as $stdnt) {
            $notification->sendToDatabase($stdnt);
        }
    }

    private function notifyAdmin(Appointment $appointment): void
    {
        $superAdmin = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['super_admin', 'super admin', 'admin','nurse','doctor', 'medical_staff','medical staff']);
        })->get();

        $notification = $this->createNotification(
            'New Appointment',
            "A new appointment has been created by {$appointment->student->user->name} ({$appointment->student->user->roles->pluck('name')->implode(', ')})",
            $appointment,
            'primary'
        );

        foreach ($superAdmin as $user) {
            $notification->sendToDatabase($user);
        }
    }


    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {

        $updater = auth()->user();

        if ($appointment->isDirty('status')) {
            // Check if the user has the required roles
            $allowedRoles = ['super-admin', 'admin', 'super_admin', 'super admin', 'nurse', 'doctor','medical_staff','medical staff'];

            if ($updater->hasAnyRole($allowedRoles)) {
                // Notify student for status changes
                $this->notifyStudentAptUpdate($appointment);
            }
        }

    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "restored" event.
     */
    public function restored(Appointment $appointment): void
    {
        //
    }

    /**
     * Handle the Appointment "force deleted" event.
     */
    public function forceDeleted(Appointment $appointment): void
    {
        //
    }


    private function createNotification(string $title, string $body, Appointment $appointment, string $color): Notification
    {



        if($color === 'primary'){
           return Notification::make()
            ->title($title)
            ->icon('heroicon-o-calendar-date-range')
            ->body($body)
            ->actions([
                Action::make('View')
                    ->button()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->url(AppointmentResource::getUrl('edit', ['record' => $appointment])),
            ]);
        }

        if($color === 'success'){
            return  Notification::make()
            ->title($title)
            ->icon('heroicon-o-calendar-date-range')
            ->body($body)
            ->success()
            ->actions([
                Action::make('View')
                    ->button()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->url(AppointmentResource::getUrl('edit', ['record' => $appointment])),
            ]);
        }

        if($color === 'danger'){
            return Notification::make()
            ->title($title)
            ->icon('heroicon-o-calendar-date-range')
            ->body($body)
            ->danger()
            ->actions([
                Action::make('View')
                    ->button()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->url(AppointmentResource::getUrl('edit', ['record' => $appointment])),
            ]);
        }

        if($color === 'warning'){
            return Notification::make()
            ->title($title)
            ->icon('heroicon-o-calendar-date-range')
            ->body($body)
            ->warning()
            ->actions([
                Action::make('View')
                    ->button()
                    ->icon('heroicon-o-eye')
                    ->label('View')
                    ->url(AppointmentResource::getUrl('edit', ['record' => $appointment])),
            ]);
        }


       return Notification::make()
        ->title($title)
        ->icon('heroicon-o-calendar-date-range')
        ->body($body)
        ->actions([
            Action::make('View')
                ->button()
                ->icon('heroicon-o-eye')
                ->label('View')
                ->url(AppointmentResource::getUrl('edit', ['record' => $appointment])),
        ]);

    }

    private function notifyStudentAptUpdate(Appointment $appointment): void
    {
        // Ensure the appointment has a student
        if (!$appointment->student) {
            return;
        }

        $checkedBy = auth()->user()->name;

        $statusMap = [
            'scheduled' => [
                'label' => 'Scheduled',
                'color' => 'primary'
            ],
            'completed' => [
                'label' => 'Completed',
                'color' => 'success'
            ],
            'cancelled' => [
                'label' => 'Cancelled',
                'color' => 'danger'
            ],
            'rescheduled' => [
                'label' => 'Rescheduled',
                'color' => 'warning'
            ]
        ];

        $statusInfo = $statusMap[$appointment->status] ?? null;

        if ($statusInfo) {
            $notification = $this->createNotification(
                "Appointment - {$statusInfo['label']}",
                "Your appointment #: {$appointment->appointment_num} has been {$statusInfo['label']} by {$checkedBy}",
                $appointment,
                $statusInfo['color'] // Pass the color to the notification
            );

            // Send notification specifically to the student associated with this appointment
            $notification->sendToDatabase($appointment->student->user);
        }
    }
}
