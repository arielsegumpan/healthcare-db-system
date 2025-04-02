<?php

namespace App\Filament\Resources;

use DateTime;
use Filament\Forms;
use Filament\Tables;
use App\Models\Student;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Appointment;
use App\Models\MedicalStaff;
use App\Models\MedicalRecord;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\Tabs;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Split;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Infolists\Components\ImageEntry;
use Filament\Forms\Components\Group as FormGroup;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MedicalRecordResource\Pages;
use App\Filament\Resources\MedicalRecordResource\RelationManagers;
use App\Filament\Resources\MedicalRecordResource\Pages\EditMedicalRecord;
use App\Filament\Resources\MedicalRecordResource\Pages\ViewMedicalRecord;

class MedicalRecordResource extends Resource
{
    protected static ?string $model = MedicalRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Medical';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewMedicalRecord::class,
            EditMedicalRecord::class,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                ->schema(static::getDetailsFormSchema()),

                Section::make()
                ->schema(static::getVitalSign()),

                Section::make()
                ->schema([static::getPerscriptionRepeater()]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('medical_record_num')
                ->label('Record #')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('warning')
                ->weight('bold'),

                ImageColumn::make('student.user.avatar_url')
                ->label('Avatar')
                ->circular()
                ->width(50)
                ->height(50)
                ->defaultImageUrl(url('https://avatar.iran.liara.run/public/1')),

                TextColumn::make('student.full_name')
                ->label('Name')
                ->searchable()
                ->sortable()
                ->description(fn (MedicalRecord $record): string => $record->student->lrn_number),

                TextColumn::make('staff.user.name')
                ->label('Medical Staff')
                ->searchable()
                ->sortable(),

                TextColumn::make('record_date')
                ->label('Date')
                ->searchable()
                ->sortable()
                ->date(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->tooltip('Actions')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->deferLoading()
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                ->icon('heroicon-m-plus')
                ->label(__('New Medical Record')),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No Medical Records are created')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
               if($user->hasRole('student')) {
                    return $query->where('student_id', $user?->student->id);
               }
            })
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalRecords::route('/'),
            'create' => Pages\CreateMedicalRecord::route('/create'),
            'edit' => Pages\EditMedicalRecord::route('/{record}/edit'),
            'view' => Pages\ViewMedicalRecord::route('/{record}'),
        ];
    }



     /** @return Forms\Components\Component[] */
     public static function getDetailsFormSchema(): array
     {
         return [
            Section::make('')
               ->schema([
                    TextInput::make('medical_record_num')
                    ->label('Record Number')
                    ->default('MEDREC-'. date('His-') . random_int(100, 999))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255)
                    ->unique(MedicalRecord::class, 'medical_record_num', ignoreRecord: true),


                    Select::make('appointment_id')
                    ->label('Appointment')
                    ->relationship(name: 'appointment', titleAttribute: 'appointment_num')
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->optionsLimit(6)
                    ->required()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            // Get the appointment
                            $appointment = Appointment::find($state);

                            if ($appointment && $appointment->student_id) {
                                // Set the student_id for saving
                                $set('student_id', $appointment->student_id);

                                // Get the student's full name and set it for display
                                $student = Student::find($appointment->student_id);
                                if ($student) {
                                    $set('student_name', $student->full_name);
                                }
                            }
                        }
                    }),

                    Select::make('student_id')
                    ->label('Student')
                    ->relationship(name: 'student', titleAttribute: 'full_name')
                    ->searchable()
                    ->native(false)
                    ->required(),

                    Select::make('staff_id')
                    ->label('Medical Staff')
                    ->relationship(name: 'staff')
                    ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->user?->name}")
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->optionsLimit(6)
                    ->required()
                    ->default(function () {
                        return auth()->user()->id;
                    }),


                    DateTimePicker::make('record_date')
                    ->label('Record Date')
                    ->dehydrated()
                    ->default(now())
                    ->native(false)
                    ->required(),

                    DatePicker::make('follow_up_date')
                    ->label('Follow Up Date')
                    ->native(false)
                    ->dehydrated()
                    ->default(now()),

                    Textarea::make('diagnosis')
                    ->label('Diagnosis')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->rows(4),

                    Textarea::make('symptoms')
                    ->label('Symptoms')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->rows(4),

                    Textarea::make('notes')
                    ->label('Notes')
                    ->maxLength(65535)
                    ->rows(4)
                    ->columnSpanFull(),

                    RichEditor::make('treatment')
                    ->label('Treatment')
                    ->maxLength(65535)
                    ->columnSpanFull(),

               ])
               ->columnSpanFull()
               ->columns([
                'sm' => 1,
                'md' => 3,
                'lg' => 3
                ])

         ];
     }

     /** @return Forms\Components\Component[] */
     public static function getVitalSign(): array
     {
         return [

             Fieldset::make()
             ->relationship('vitalSign')
             ->schema([

                 TextInput::make('temperature')
                 ->label('Temperature')
                 ->suffix('°C')
                 ->numeric()
                 ->minValue(0)
                 ->maxValue(100),

                 TextInput::make('blood_pressure')
                 ->label('Blood Pressure')
                 ->suffix('mmHg')
                 ->maxlength(255),

                 TextInput::make('pulse_rate')
                 ->label('Pulse Rate')
                 ->suffix('bpm')
                 ->numeric()
                 ->minValue(0)
                 ->maxValue(200),

                 TextInput::make('respiratory_rate')
                 ->label('Respiratory Rate')
                 ->suffix('breaths/min')
                 ->numeric()
                 ->minValue(0)
                 ->maxValue(200),

                 TextInput::make('height')
                 ->label('Height')
                 ->suffix('cm')
                 ->numeric()
                 ->minValue(1),

                 TextInput::make('weight')
                 ->label('Weight')
                 ->suffix('kg')
                 ->numeric()
                 ->minValue(1),

             ])
     ];
     }


     public static function getPerscriptionRepeater(): Repeater
     {
         return Repeater::make('prescriptions')
                ->relationship('prescriptions')
                ->hiddenLabel()
                ->schema([

                    Select::make('created_by')
                    ->label('Created By')
                    ->relationship(name: 'createdBy', titleAttribute: 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->optionsLimit(6)
                    ->native(false)
                    ->default(fn () => auth()->id())
                    ->disabled()
                    ->dehydrated(),

                    DateTimePicker::make('prescription_date')
                    ->label('Prescription Date')
                    ->required()
                    ->default(now())
                    ->native(false),

                    Textarea::make('notes')
                    ->label('Notes')
                    ->maxlength(65535)
                    ->columnSpanFull()
                    ->rows(4),

                    Repeater::make('prescriptionMedications')
                    ->relationship('prescriptionMedications')
                    ->schema([
                        Select::make('medication_id')
                        ->label('Medication')
                        ->relationship(name: 'medication', titleAttribute: 'name')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->optionsLimit(6)
                        ->native(false)
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems(),

                        TextInput::make('dosage')
                        ->label('Dosage')
                        ->maxLength(255)
                        ->placeholder('ex. 500 mg'),

                        TextInput::make('frequency')
                        ->label('Frequency')
                        ->maxLength(255),

                        TextInput::make('duration')
                        ->label('Duration')
                        ->maxLength(255),

                        MarkdownEditor::make('instructions')
                        ->label('Instructions')
                        ->maxLength(65535)
                        ->columnSpanFull()
                        ->disableToolbarButtons([
                            'blockquote',
                            'strike',
                        ]),
                    ])
                    ->addActionLabel('Add Medication')
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2
                    ])
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->cloneable(false)
                    ->reorderableWithDragAndDrop(true)
                    ->itemLabel(fn (array $state): ?string =>
                    isset($state['medication_id'])
                        ? \App\Models\Medication::find($state['medication_id'])?->name ?? "Medication #{$state['medication_id']}"
                        : 'New Medication'
                    )
                    ->live()
                    ->deleteAction(
                        fn (Action $action) => $action->requiresConfirmation(),
                    )
                    ->grid(2)



                ])
                ->addActionLabel('Add Prescription')
                ->reorderableWithButtons()
                ->addActionAlignment(Alignment::Start)
                ->collapsible()
                ->cloneable()
                ->reorderableWithDragAndDrop(true)
                ->live()
                ->columns([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2
                ]);
     }



     public static function infolist(Infolist $infolist): Infolist
     {
         return $infolist
             ->schema([
                Tabs::make('Tabs')
                ->tabs([
                    Tabs\Tab::make('Diagnosis & Symptoms')
                        ->icon('heroicon-m-clipboard-document-list')
                        ->schema([
                            Group::make([
                                Group::make([
                                    TextEntry::make('medical_record_num')
                                    ->label('Record #')
                                    ->hiddenLabel()
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('warning')
                                    ->icon('heroicon-m-hashtag')
                                    ->placeholder('N/A'),

                                    TextEntry::make('staff.user.name')
                                    ->label('Medical Staff Assisted')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-user')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->placeholder('N/A'),

                                    TextEntry::make('record_date')
                                    ->label('Record Date')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-calendar')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->placeholder('N/A'),

                                    TextEntry::make('follow_up_date')
                                    ->label('Follow Up Date')
                                    ->weight(FontWeight::Bold)
                                    ->icon('heroicon-m-calendar')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('danger')
                                    ->placeholder('N/A'),
                                ])
                                ->columns([
                                        'sm' => 1,
                                        'md' => 2,
                                        'lg' => 2
                                ]),
                            ]),

                            TextEntry::make('diagnosis')
                            ->label('Diagnosis')
                            ->markdown()
                            ->placeholder('N/A'),

                            TextEntry::make('symptoms')
                            ->label('Symptoms')
                            ->markdown()
                            ->placeholder('N/A'),
                        ]),
                    Tabs\Tab::make('Notes')
                        ->icon('heroicon-m-pencil-square')
                        ->schema([
                            TextEntry::make('notes')
                            ->label('Notes')
                            ->hiddenLabel()
                            ->markdown()
                            ->placeholder('No Notes'),
                        ]),
                    Tabs\Tab::make('Treatment')
                        ->icon('heroicon-m-chart-pie')
                        ->schema([
                            TextEntry::make('treatment')
                            ->label('Treatment')
                            ->hiddenLabel()
                            ->markdown()
                            ->html(),
                        ]),
                    Tabs\Tab::make('Vital Signs')
                        ->icon('heroicon-m-heart')
                        ->schema([

                            TextEntry::make('vitalSign.temperature')
                            ->label('Temperature')
                            ->suffix(' °C')
                            ->badge()
                            ->color('danger')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->placeholder('N/A'),

                            TextEntry::make('vitalSign.blood_pressure')
                            ->label('Blood Pressure')
                            ->suffix('mmHg')
                            ->badge()
                            ->color('danger')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->placeholder('N/A'),

                            TextEntry::make('vitalSign.pulse_rate')
                            ->label('Pulse Rate')
                            ->suffix('bpm')
                            ->badge()
                            ->color('success')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->placeholder('N/A'),

                            TextEntry::make('vitalSign.respiratory_rate')
                            ->label('Respiratory Rate')
                            ->suffix('breaths/min')
                            ->badge()
                            ->color('primary')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->placeholder('N/A'),

                            TextEntry::make('vitalSign.height')
                            ->label('Height')
                            ->suffix('cm')
                            ->placeholder('N/A'),

                            TextEntry::make('vitalSign.weight')
                            ->label('Weight')
                            ->suffix('kg')
                            ->placeholder('N/A'),

                            TextEntry::make('bmi')
                            ->label('BMI')
                            ->suffix(' kg/m²')
                            ->badge()
                            ->color(function ($record) {
                                if (!$record->vitalSign || !$record->vitalSign->height || !$record->vitalSign->weight) {
                                    return 'gray';
                                }

                                $heightInMeters = $record->vitalSign->height / 100;
                                $bmi = round($record->vitalSign->weight / ($heightInMeters * $heightInMeters), 2);

                                if ($bmi < 18.5) {
                                    return 'warning'; // Underweight - yellow
                                } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
                                    return 'success'; // Normal weight - green
                                } elseif ($bmi >= 25 && $bmi <= 29.9) {
                                    return 'warning'; // Overweight - yellow
                                } else {
                                    return 'danger'; // Obese - red
                                }
                            })
                            ->size(TextEntry\TextEntrySize::Large)
                            ->placeholder('N/A')
                            ->state(function ($record) {
                                if ($record->vitalSign && $record->vitalSign->height && $record->vitalSign->weight) {
                                    $heightInMeters = $record->vitalSign->height / 100;
                                    $bmi = round($record->vitalSign->weight / ($heightInMeters * $heightInMeters), 2);

                                    $category = '';
                                    if ($bmi < 18.5) {
                                        $category = ' (Underweight)';
                                    } elseif ($bmi >= 18.5 && $bmi <= 24.9) {
                                        $category = ' (Normal weight)';
                                    } elseif ($bmi >= 25 && $bmi <= 29.9) {
                                        $category = ' (Overweight)';
                                    } else {
                                        $category = ' (Obese)';
                                    }

                                    return $bmi . $category;
                                }
                                return null;
                            }),

                        ])
                        ->columns([
                            'sm' => 1,
                            'md' => 3,
                            'lg' => 6
                    ])
                    ->columns([
                        'sm' => 1,
                        'md' => 3,
                        'lg' => 3
                    ]),

                    Tabs\Tab::make('Perscription')
                    ->icon('heroicon-m-numbered-list')
                    ->schema([

                    RepeatableEntry::make('prescriptions')
                    ->hiddenLabel()
                    ->schema([
                        Group::make([
                            TextEntry::make('medicalRecord.medical_record_num')
                            ->label('Record #')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->color('warning')
                            ->tooltip('Record Number'),

                            TextEntry::make('createdBy.name')
                            ->label('Prescribed By')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->tooltip('Prescribed By')
                            ->formatStateUsing(fn (string $state): string => ucwords($state)),

                            TextEntry::make('prescription_date')
                            ->label('Prescription Date')
                            ->weight(FontWeight::Bold)
                            ->badge()
                            ->color('danger')
                            ->tooltip('Prescription Date')
                            ->dateTime(),
                        ])
                        ->columns([
                            'sm' => 1,
                            'md' => 3,
                            'lg' => 3
                        ]),

                        TextEntry::make('notes')
                        ->label('Notes')
                        ->columnSpanFull()
                        ->formatStateUsing(fn (string $state): string => ucfirst($state))
                        ->markdown(),


                        RepeatableEntry::make('prescriptionMedications')
                        ->label('Medications')
                        ->schema([
                            TextEntry::make('medication.name')
                            ->label('Medicine Name')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->weight(FontWeight::Bold)
                            ->badge()
                            ->color('primary')
                            ->formatStateUsing(fn (string $state): string => ucwords($state))
                            ->columnSpanFull(),

                            Group::make([
                                TextEntry::make('dosage')
                                ->label('Dosage')
                                ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : '')
                                ->weight(FontWeight::Bold)
                                ->placeholder('N/A'),

                                TextEntry::make('frequency')
                                ->label('Frequency')
                                ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : '')
                                ->weight(FontWeight::Bold)
                                ->placeholder('N/A'),

                                TextEntry::make('duration')
                                ->label('Duration')
                                ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : '')
                                ->weight(FontWeight::Bold)
                                ->placeholder('N/A'),
                            ])
                            ->columnSpanFull()
                            ->columns([
                                'sm' => 1,
                                'md' => 3,
                                'lg' => 3
                            ]),

                            TextEntry::make('instructions')
                            ->label('Instructions')
                            ->placeholder('N/A')
                            ->columnSpanFull()
                            ->markdown()
                            ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '')
                        ])
                        ->grid(2)
                    ])

                    ])
                ])
                ->columnSpanFull()
             ]);
     }
}
