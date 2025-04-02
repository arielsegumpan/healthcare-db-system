<?php

namespace App\Filament\Pages\Auth;


use App\Models\User;
use App\Models\Student;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('lrn_number')
                ->label('LRN Number')
                ->unique('students', 'lrn_number')
                ->maxLength(255)
                ->nullable()
                ->helperText('Optional: Learner Reference Number'),

                Group::make([
                    TextInput::make('first_name')
                    ->label('First Name')
                    ->maxLength(255)
                    ->required(),

                    TextInput::make('last_name')
                    ->label('Last Name')
                    ->maxLength(255)
                    ->required(),
                ])
                ->columns([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2
                ]),

                Select::make('gender')
                ->label('Gender')
                ->options([
                    'male' => 'Male',
                    'female' => 'Female',
                ])
                ->native(false)
                ->required(),

                // Default Filament Fields
                // $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),

            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $sanitizedData = $this->sanitizeInputData($data);

         // Create User
         $user = $this->createUser($sanitizedData);

         // Create Student Profile
         $this->createStudentProfile($user, $sanitizedData);

         // Assign Student Role
         $this->assignStudentRole($user);

        return $user;

    }

    // public function form(Form $form): Form
    // {
    //     return $form
    //         ->schema([
    //             TextInput::make('lrn_number')
    //                 ->label('LRN Number')
    //                 ->unique('students', 'lrn_number')
    //                 ->maxLength(255)
    //                 ->nullable()
    //                 ->helperText('Optional: Learner Reference Number'),

    //             Group::make([
    //                 TextInput::make('first_name')
    //                     ->label('First Name')
    //                     ->maxLength(255)
    //                     ->required()
    //                     ->validationAttribute('first name')
    //                     ->rules([
    //                         'regex:/^[a-zA-Z\s\'-]+$/',
    //                         'min:2',
    //                     ]),

    //                 TextInput::make('last_name')
    //                     ->label('Last Name')
    //                     ->maxLength(255)
    //                     ->required()
    //                     ->validationAttribute('last name')
    //                     ->rules([
    //                         'regex:/^[a-zA-Z\s\'-]+$/',
    //                         'min:2',
    //                     ]),
    //             ])
    //             ->columns([
    //                 'sm' => 1,
    //                 'md' => 2,
    //                 'lg' => 2
    //             ]),

    //             Select::make('gender')
    //                 ->label('Gender')
    //                 ->options([
    //                     'male' => 'Male',
    //                     'female' => 'Female',
    //                 ])
    //                 ->native(false)
    //                 ->required(),

    //             // Default Filament Fields
    //             $this->getEmailFormComponent(),
    //             $this->getPasswordFormComponent(),
    //             $this->getPasswordConfirmationFormComponent(),
    //         ]);
    // }

    protected function sanitizeInputData(array $data): array
    {
        return [
            'first_name' => trim(strip_tags($data['first_name'])),
            'last_name' => trim(strip_tags($data['last_name'])),
            'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
            'password' => $data['password'],
            'gender' => strtolower(trim(strip_tags($data['gender']))),
            'lrn_number' => isset($data['lrn_number'])
                ? trim(strip_tags($data['lrn_number']))
                : null,
        ];
    }

    protected function createUser(array $data): User
    {
        return User::create([
            'name' => Str::title($data['first_name'] . ' ' . $data['last_name']),
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }

    protected function createStudentProfile(User $user, array $data): Student
    {
        return Student::create([
            'user_id' => $user->id,
            'lrn_number' => $data['lrn_number'],
            'first_name' => Str::title($data['first_name']),
            'last_name' => Str::title($data['last_name']),
            'gender' => $data['gender'],
        ]);
    }

    protected function assignStudentRole(User $user): void
    {
        $studentRole = Role::firstOrCreate(['name' => 'student']);
        $user->assignRole($studentRole);
    }

}
