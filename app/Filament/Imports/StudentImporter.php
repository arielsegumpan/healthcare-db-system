<?php

namespace App\Filament\Imports;

use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [


            ImportColumn::make('first_name')
                ->example('Juan')
                ->rules(['max:255']),
            ImportColumn::make('last_name')
                ->example('Dela Cruz')
                ->rules(['max:255']),

            ImportColumn::make('user_email')
                ->example('juan.delacruz@dbti-victorias.edu.ph')
                ->fillRecordUsing(function (Student $record, string $state, array $data): void {
                    // Set email for the associated user
                    $record->user->email = $state;
                })
                ->rules(['email', 'max:255', 'unique:users,email']),

            ImportColumn::make('user_password')
                ->example('qwerty12345')
                ->fillRecordUsing(function (Student $record, string $state): void {
                    // Hash the password for the associated user
                    $record->user->password = Hash::make($state);
                })
                ->rules(['min:8', 'max:255']),
            // Existing columns remain the same
            ImportColumn::make('lrn_number')
                ->example('123456789')
                ->rules(['max:255']),

            ImportColumn::make('gender')
                ->example('male')
                ->rules(['max:255']),
            ImportColumn::make('section')
                ->example('Occhiena')
                ->rules(['max:10']),
            ImportColumn::make('grade_level')
                ->example('12')
                ->rules(['max:255']),
            ImportColumn::make('date_of_birth')
                ->example('2000-01-01')
                ->rules(['date']),
            ImportColumn::make('blood_group')
                ->example('O+')
                ->rules(['max:255']),
            ImportColumn::make('address')
                ->example('123 Main Street, Victorias City')
                ->rules(['max:255']),
            ImportColumn::make('parent_name')
                ->example('Juanito Dela Cruz')
                ->rules(['max:255']),
            ImportColumn::make('parent_contact')
                ->example('09123456789')
                ->rules(['max:20']),
            ImportColumn::make('allergies')
                ->example('Peanut, Shellfish, Eggs')
        ];
    }

    public function resolveRecord(): ?Student
    {
       // Find or create a user first
       $user = User::firstOrNew(
            ['email' => $this->data['user_email']],
            [
                'name' => trim($this->data['first_name'] . ' ' . $this->data['last_name']),
                'password' => Hash::make($this->data['user_password'])
            ]
        );

        // If the user already exists, update the name and password
        if ($user->exists) {
            $user->name = trim($this->data['first_name'] . ' ' . $this->data['last_name']);
            $user->password = Hash::make($this->data['user_password']);
            $user->save();
        } else {
            $user->save();
        }

        // Find or create a student record
        $student = Student::firstOrNew(
            ['email' => $this->data['user_email']],
            [
                'user_id' => $user->id,
                'first_name' => $this->data['first_name'],
                'last_name' => $this->data['last_name'],
                // Add other fields from your import data
                'lrn_number' => $this->data['lrn_number'] ?? null,
                'gender' => $this->data['gender'] ?? null,
                'section' => $this->data['section'] ?? null,
                'grade_level' => $this->data['grade_level'] ?? null,
                'date_of_birth' => $this->data['date_of_birth'] ?? null,
                'blood_group' => $this->data['blood_group'] ?? null,
                'address' => $this->data['address'] ?? null,
                'parent_name' => $this->data['parent_name'] ?? null,
                'parent_contact' => $this->data['parent_contact'] ?? null,
                'allergies' => $this->data['allergies'] ?? null
            ]
        );

        // If the student already exists, update the fields
        if ($student->exists) {
            $student->user_id = $user->id;
            $student->first_name = $this->data['first_name'];
            $student->last_name = $this->data['last_name'];
            // Update other fields similarly
            $student->save();
        } else {
            $student->save();
        }

        return $student;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
