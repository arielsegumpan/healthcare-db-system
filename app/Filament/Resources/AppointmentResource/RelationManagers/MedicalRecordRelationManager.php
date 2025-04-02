<?php

namespace App\Filament\Resources\AppointmentResource\RelationManagers;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Student;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Medication;
use Filament\Tables\Table;
use App\Models\Appointment;
use App\Models\MedicalStaff;
use App\Models\MedicalRecord;
use App\Models\InventoryTransaction;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class MedicalRecordRelationManager extends RelationManager
{
    protected static string $relationship = 'medicalRecord';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Details')
                        ->icon('heroicon-o-information-circle')
                        ->schema(static::getMedRecFormSchema()),
                    Step::make('Vital Sign')
                        ->icon('heroicon-o-heart')
                        ->schema(static::getMedRecVitalSign()),
                    Step::make('Perscription')
                        ->icon('heroicon-o-numbered-list')
                        ->schema([static::getMedRecPerscriptionRepeater()]),
                ])
                ->columnSpanFull()

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('medical_record_num')
            ->columns([

                TextColumn::make('medical_record_num')
                ->label('Record #')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('warning')
                ->weight('bold'),

                TextColumn::make('staff.user.name')
                ->label('Assisted By')
                ->searchable()
                ->sortable(),

                TextColumn::make('record_date')
                ->label('Record Date')
                ->searchable()
                ->sortable()
                ->date(),

                TextColumn::make('follow_up_date')
                ->label('Follow Up')
                ->searchable()
                ->sortable()
                ->date()
                ->badge()
                ->color('success')
                ->weight('bold'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Get the current appointment
                    $appointment = Appointment::find($this->getOwnerRecord()->id);

                    if ($appointment) {
                        // Set student_id from the appointment
                        $data['student_id'] = $appointment->student_id;

                        $user = auth()->user();
                        $medicalStaff = MedicalStaff::where('user_id', $user->id)->first();


                        if ($medicalStaff) {
                            $data['staff_id'] = $medicalStaff->id;
                        } else {
                            MedicalStaff::create([
                                'user_id' => $user->id,
                                'staff_type' => 'nurse'
                            ]);
                            $data['staff_id'] = $user->id;
                        }
                        $data['appointment_id'] = $appointment->id;

                        $appointment->update([
                            'status' => 'completed'
                        ]);
                    }
                    return $data;
                })
                ->after(function (Model $record) {
                    // Process each prescription medication after the medical record is created
                    if (isset($record->prescriptions) && $record->prescriptions->isNotEmpty()) {
                        foreach ($record->prescriptions as $prescription) {
                            foreach ($prescription->prescriptionMedications as $prescriptionMedication) {
                                // Get medication and quantity
                                $medication = Medication::find($prescriptionMedication->medication_id);
                                $quantity = (int) $prescriptionMedication->quantity;

                                if ($medication && $quantity > 0) {
                                    // Update medication stock
                                    $medication->update([
                                        'stock' => $medication->stock - $quantity
                                    ]);

                                    // Create inventory transaction
                                    InventoryTransaction::create([
                                        'medication_id' => $medication->id,
                                        'quantity' => $quantity,
                                        'transaction_type' => 'outgoing',
                                        'notes' => "Prescription for Medical Record #{$record->id}",
                                        'performed_by' => auth()->id(),
                                    ]);
                                }
                            }
                        }
                    }
                })
            ])
            ->actions([
                // INDI PANI TAPOS RECONFIGURE ANG DATABASE DAPAT
                Tables\Actions\ViewAction::make('View')
                ->color('primary'),

                ActionGroup::make([

                    Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Get the current appointment
                        $appointment = Appointment::find($this->getOwnerRecord()->id);

                        if ($appointment) {
                            $appointment->update([
                                'status' => 'completed'
                            ]);
                        }


                        return $data;
                    })
                    ->using(function (Model $record, array $data): Model {
                        // Track original medications before updating the record
                        $originalPrescriptionMeds = [];

                        foreach ($record->prescriptions as $prescription) {
                            foreach ($prescription->prescriptionMedications as $med) {
                                $originalPrescriptionMeds[$med->id] = [
                                    'medication_id' => $med->medication_id,
                                    'quantity' => $med->quantity,
                                ];
                            }
                        }
                        // Update the record
                        $record->update($data);
                        $record->refresh();

                        // Process medication changes for inventory updates
                        foreach ($record->prescriptions as $prescription) {
                            foreach ($prescription->prescriptionMedications as $med) {
                                // Check if this is a new medication or an update
                                if (isset($originalPrescriptionMeds[$med->id])) {
                                    // This is an existing med that was updated
                                    $original = $originalPrescriptionMeds[$med->id];
                                    $quantityDiff = $med->quantity - $original['quantity'];

                                    // Only create transaction if quantity changed
                                    if ($quantityDiff != 0) {
                                        $medication = Medication::find($med->medication_id);

                                        if ($medication) {
                                            // Update stock
                                            $medication->update([
                                                'stock' => $medication->stock - $quantityDiff
                                            ]);

                                            // Create inventory transaction
                                            InventoryTransaction::create([
                                                'medication_id' => $medication->id,
                                                'quantity' => abs($quantityDiff),
                                                'transaction_type' => $quantityDiff > 0 ? 'outgoing' : 'incoming',
                                                'notes' => "Updated prescription for Medical Record #{$record->id}",
                                                'performed_by' => auth()->id(),
                                            ]);
                                        }
                                    }

                                    // Remove from original array to track what's been processed
                                    unset($originalPrescriptionMeds[$med->id]);
                                } else {
                                    // This is a new medication
                                    $medication = Medication::find($med->medication_id);
                                    $quantity = (int) $med->quantity;

                                    if ($medication && $quantity > 0) {
                                        // Update medication stock
                                        $medication->update([
                                            'stock' => $medication->stock - $quantity
                                        ]);

                                        // Create inventory transaction
                                        InventoryTransaction::create([
                                            'medication_id' => $medication->id,
                                            'quantity' => $quantity,
                                            'transaction_type' => 'outgoing',
                                            'notes' => "New medication added to prescription for Medical Record #{$record->id}",
                                            'performed_by' => auth()->id(),
                                        ]);
                                    }
                                }
                            }
                        }

                        // Handle deleted medications (items still in originalPrescriptionMeds)
                        foreach ($originalPrescriptionMeds as $id => $original) {
                            $medication = Medication::find($original['medication_id']);
                            $quantity = (int) $original['quantity'];

                            if ($medication && $quantity > 0) {
                                // Return stock for deleted medications
                                $medication->update([
                                    'stock' => $medication->stock + $quantity
                                ]);

                                // Create inventory transaction
                                InventoryTransaction::create([
                                    'medication_id' => $medication->id,
                                    'quantity' => $quantity,
                                    'transaction_type' => 'incoming',
                                    'notes' => "Medication removed from prescription for Medical Record #{$record->id}",
                                    'performed_by' => auth()->id(),
                                ]);
                            }
                        }

                        return $record;
                    }),

                    Tables\Actions\DeleteAction::make(),
                ])

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /** @return Forms\Components\Component[] */
    public static function getMedRecFormSchema(): array
    {
        return [
           Group::make([
                   TextInput::make('medical_record_num')
                   ->label('Record Number')
                   ->default('MEDREC-'. date('His-') . random_int(100, 999))
                   ->disabled()
                   ->dehydrated()
                   ->required()
                   ->maxLength(255)
                   ->unique(MedicalRecord::class, 'medical_record_num', ignoreRecord: true)
                   ->columnSpanFull(),

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
               'md' => 2,
               'lg' => 2
            ])

        ];
    }

    /** @return Forms\Components\Component[] */
    public static function getMedRecVitalSign(): array
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

    public static function getMedRecPerscriptionRepeater(): Repeater
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
                       ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                       ->getOptionLabelFromRecordUsing(fn (Model $record) => "{$record->name} (Stock: {$record->stock})")
                       ->columnSpanFull(),

                       TextInput::make('dosage')
                       ->label('Dosage')
                       ->maxLength(255)
                       ->placeholder('ex. 500 mg')
                       ->columnSpanFull(),

                       TextInput::make('frequency')
                       ->label('Frequency')
                       ->maxLength(255),

                       TextInput::make('duration')
                       ->label('Duration')
                       ->maxLength(255),

                       TextInput::make('quantity')
                        ->label('Quantity')
                        ->maxLength(255)
                        ->numeric()
                        ->columnSpanFull()
                        ->rules([
                            fn ($get) => function (string $attribute, $value, Closure $fail) use ($get) {
                                $medication = Medication::find($get('medication_id'));

                                if ($medication && $value > $medication->stock) {
                                    $fail("Not enough stock available. Current stock: {$medication->stock}");
                                }
                            },
                        ]),

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


    private static function processInventoryTransaction(Medication $medication, int $quantity, string $type, string $notes)
    {
        // Update medication stock
        if ($type === 'outgoing') {
            $medication->update([
                'stock' => $medication->stock - $quantity
            ]);
        } else {
            $medication->update([
                'stock' => $medication->stock + $quantity
            ]);
        }

        // Create inventory transaction
        InventoryTransaction::create([
            'medication_id' => $medication->id,
            'quantity' => $quantity,
            'transaction_type' => $type,
            'notes' => $notes,
            'performed_by' => auth()->id(),
        ]);
    }
}
