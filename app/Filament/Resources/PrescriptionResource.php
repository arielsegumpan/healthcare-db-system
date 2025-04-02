<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use App\Models\Prescription;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Group as InfoGroup;
use App\Filament\Resources\PrescriptionResource\Pages;
use Filament\Infolists\Components\Section as InfoSection;
use App\Filament\Resources\PrescriptionResource\RelationManagers;
use App\Filament\Resources\PrescriptionResource\Pages\EditPrescription;
use App\Filament\Resources\PrescriptionResource\Pages\ViewPrescription;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Prescription';

    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                ->schema(static::getPresDetailsFormSchema()),

                Section::make()
                ->schema([static::getMedications()]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('medicalRecord.medical_record_num')
                ->label('Record #')
                ->sortable()
                ->searchable()
                ->badge()
                ->color('warning')
                ->description(fn (Prescription $record): string => $record->medicalRecord->student->full_name),

                TextColumn::make('createdBy.name')
                ->label('Created By')
                ->sortable()
                ->searchable(),

                TextColumn::make('prescription_date')
                ->label('Prescription Date')
                ->sortable()
                ->searchable()
                ->badge()
                ->color('danger'),

                TextColumn::make('notes')
                ->label('Notes')
                ->wrap()
                ->markdown()
                ->limit(50),

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
                ->label(__('New Prescription')),
            ])
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No Prescriptions are created')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();

                if ($user->hasRole('student')) {
                    // Get the student associated with this user
                    $student = $user->student;

                    if ($student) {
                        // Get the medical record IDs for this student
                        $medicalRecordIds = $student->medicalRecords->pluck('id')->toArray();

                        // Filter prescriptions by these medical record IDs
                        return $query->whereIn('record_id', $medicalRecordIds);
                    }

                    // If no student record or no medical records, return no results
                    return $query->where('id', 0); // This will return no results
                }

                // For non-student users, return all prescriptions (no filtering)
                return $query;
            });
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
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
            'view' => Pages\ViewPrescription::route('/{record}'),
        ];
    }


    /** @return Forms\Components\Component[] */
    public static function getPresDetailsFormSchema(): array
    {
        return [
           Group::make([
                Group::make([
                    Select::make('record_id')
                    ->label('Record')
                    ->relationship(name: 'medicalRecord', titleAttribute: 'medical_record_num')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->optionsLimit(6)
                    ->native(false),

                    Select::make('created_by')
                    ->label('Created By')
                    ->relationship(name: 'createdBy', titleAttribute: 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->optionsLimit(6)
                    ->native(false),

                    DateTimePicker::make('prescription_date')
                    ->label('Prescription Date')
                    ->required()
                    ->default(now())
                    ->native(false),
                ])
                ->columns([
                    'sm' => 1,
                    'md' => 3,
                    'lg' => 3
                ])
                ->columnSpanFull(),


                RichEditor::make('notes')
                ->label('Notes')
                ->required()
                ->maxlength(65535)
                ->columnSpanFull(),
              ])
              ->columns([
                'sm' => 1,
                'md' => 2,
                'lg' => 2
            ])
            ->columnSpanFull(),
        ];
    }


    public static function getMedications(): Repeater
    {
        return Repeater::make('prescriptionMedications')
        ->relationship()
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

            Group::make([
                TextInput::make('dosage')
                ->label('Dosage')
                ->maxLength(255),
                TextInput::make('frequency')
                ->label('Frequency')
                ->maxLength(255),
                TextInput::make('duration')
                ->label('Duration')
                ->maxLength(255),
            ])
            ->columns([
                'sm' => 1,
                'md' => 3,
                'lg' => 3
            ]),

            RichEditor::make('instructions')
            ->label('Instructions')
            ->maxLength(65535)
            ->columnSpanFull(),
        ])
        ->defaultItems(1)
        ->reorderableWithButtons()
        ->addActionAlignment(Alignment::Start)
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
        ->grid(2);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
            InfoSection::make()
            ->schema([
                InfoGroup::make([
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
                ->columnSpanFull()
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
            ]),

            InfoSection::make()
            ->schema([

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

                        InfoGroup::make([
                            TextEntry::make('dosage')
                            ->label('Dosage')
                            ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : '')
                            ->weight(FontWeight::Bold),

                            TextEntry::make('frequency')
                            ->label('Frequency')
                            ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : '')
                            ->weight(FontWeight::Bold),

                            TextEntry::make('duration')
                            ->label('Duration')
                            ->formatStateUsing(fn (?string $state): string => $state ? ucwords($state) : '')
                            ->weight(FontWeight::Bold),
                        ])
                        ->columnSpanFull()
                        ->columns([
                            'sm' => 1,
                            'md' => 3,
                            'lg' => 3
                        ]),

                        TextEntry::make('instructions')
                        ->label('Instructions')
                        ->columnSpanFull()
                        ->markdown()
                        ->formatStateUsing(fn (?string $state): string => $state ? ucfirst($state) : '')
                ])
                ->grid(2)


            ])
            // ->columns([
            //     'sm' => 1,
            //     'md' => 2,
            //     'lg' => 2
            // ])


        ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewPrescription::class,
            EditPrescription::class,
        ]);
    }



    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();

        // If user is a student, count only their prescriptions
        if ($user->hasRole('student')) {
            // Get the student's medical record IDs
            $student = $user->student;

            if ($student) {
                $medicalRecordIds = $student->medicalRecords()->pluck('id')->toArray();
                // Count prescriptions for those medical records
                return static::getModel()::whereIn('record_id', $medicalRecordIds)->count();
            }
            return '0';
        }

        // For staff, maybe count prescriptions they created
        if ($user->hasRole(['doctor', 'nurse', 'medical_staff'])) {
            return static::getModel()::where('created_by', $user->id)->count();
        }
        // For admins/superadmins, count all prescriptions
        return static::getModel()::count();
    }
}
