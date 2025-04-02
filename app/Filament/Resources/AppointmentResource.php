<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Student;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Appointment;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Enums\AppointmentStatusEnum;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use App\Enums\AppointmentPriorityEnum;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Split;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Collection;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\AppointmentResource\Pages;
use Filament\Infolists\Components\Section as InfoSection;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Filament\Resources\AppointmentResource\RelationManagers\MedicalRecordRelationManager;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';

    protected static ?string $navigationGroup = 'Medical';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                ->schema([

                    TextInput::make('appointment_num')
                        ->label('Appointment Number')
                        ->default('A#-'. date('His-') . random_int(100, 999))
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->maxLength(255)
                        ->unique(Appointment::class, 'appointment_num', ignoreRecord: true)
                        ->columnSpanFull(),

                    Select::make('student_id')
                        ->label('Student')
                        ->relationship(name: 'student', titleAttribute: 'full_name')
                        ->searchable()
                        ->native(false)
                        ->preload()
                        ->optionsLimit(6)
                        ->required()
                        ->visible(function () {
                            return auth()->user()->hasAnyRole(['super_admin','admin','super-admin','super admin','medical staff','medical-staff', 'staff', 'medical staff', 'nurse', 'doctor']);
                        }),

                    DateTimePicker::make('appointment_date')
                        ->label('Appointment Date')
                        ->native(false)
                        ->minDate(now()->startOfYear())
                        ->maxDate(now()->endOfYear())
                        ->default(now()->startOfHour())
                        ->dehydrated()
                        ->required(),

                    ToggleButtons::make('priority')
                        ->label('Priority')
                        ->options(AppointmentPriorityEnum::class)
                        ->default(AppointmentPriorityEnum::MEDIUM)
                        ->inline()
                        ->required()
                        ->dehydrated(),

                    ToggleButtons::make('status')
                        ->label('Status')
                        ->options(AppointmentStatusEnum::class)
                        ->default(AppointmentStatusEnum::SCHEDULED)
                        ->inline()
                        ->required()
                        ->dehydrated()
                        ->columnSpanFull()
                        ->visible(function () {
                            return auth()->user()->hasAnyRole(['super_admin','admin','super-admin','super admin','medical staff','medical-staff', 'staff', 'medical staff', 'nurse', 'doctor']);
                        }),

                    RichEditor::make('reason')
                        ->label('Reason')
                        ->maxLength(65535)
                        ->columnSpanFull(),

                ])
                ->columns([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2
                ]),

                Section::make()
                ->schema([
                    Group::make([

                        RichEditor::make('notes')
                            ->label('Notes')
                            ->maxLength(65535),

                        RichEditor::make('cancelled_reason')
                            ->label('Cancelled Reason')
                            ->maxLength(65535),
                    ])
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2
                    ])
                    ->columnSpanFull()
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_num')
                ->label('Appointment #')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('warning'),

                TextColumn::make('student.full_name')
                ->label('Student')
                ->searchable()
                ->sortable(),

                // TextColumn::make('staff.user.name')
                // ->label('Medical Staff')
                // ->searchable()
                // ->sortable(),

                TextColumn::make('appointment_date')
                ->label('Appointment Date')
                ->searchable()
                ->sortable()
                ->dateTime(),

                TextColumn::make('priority')
                ->label('Priority')
                ->searchable()
                ->sortable()
                ->badge()
                ->icon('heroicon-o-check')
                ->color(function ($state) {
                    return match ($state) {
                        'low' => 'primary',
                        'medium' => 'success',
                        'high' => 'danger',
                        'emergency' => 'warning',
                    };
                })
                ->formatStateUsing(fn(string $state): string => ucwords($state)),

                TextColumn::make('status')
                ->label('Status')
                ->searchable()
                ->sortable()
                ->badge()
                ->color(function ($state) {
                    return match ($state){
                        'scheduled' => 'primary',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'rescheduled' => 'warning'
                    };
                })
                ->icon(function ($state){
                    return match ($state){
                        'scheduled' => 'heroicon-m-calendar',
                        'completed' => 'heroicon-m-check-circle',
                        'cancelled' => 'heroicon-m-x-circle',
                        'rescheduled' => 'heroicon-m-calendar',
                    };
                })
                ->formatStateUsing(fn(string $state): string => ucwords($state)),
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
                ->label(__('New Appointment')),
            ])
            ->emptyStateIcon('heroicon-o-calendar-date-range')
            ->emptyStateHeading('No Appointments are created')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {
                if(auth()->user()->hasRole('student')) {
                    $query->where('student_id', auth()->user()->student->id); //change to user id dapat!
                }
            });
    }

    public static function getRelations(): array
    {

        return [
            MedicalRecordRelationManager::class
        ];

    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('appointment_num')
                    ->hiddenlabel()
                    ->size(TextEntry\TextEntrySize::Large)
                    ->weight(FontWeight::Bold)
                    ->color('warning')
                    ->columnSpanFull(),
                TextEntry::make('student.full_name')
                    ->label('Student')
                    ->size(TextEntry\TextEntrySize::Large)
                    ->weight(FontWeight::Bold),
                TextEntry::make('appointment_date')
                    ->label('Appointment Date')
                    ->badge()
                    ->color('success')
                    ->dateTime()
                    ->color('success'),
                TextEntry::make('priority')
                    ->label('Priority')
                    ->formatStateUsing(fn(string $state): string => ucwords($state))
                    ->badge()
                    ->color(function ($state) {
                        return match ($state) {
                            'low' => 'primary',
                            'medium' => 'success',
                            'high' => 'danger',
                            'emergency' => 'warning',
                        };
                    })
                    ->icon('heroicon-o-check'),
                TextEntry::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn(string $state): string => ucwords($state))
                    ->badge()
                    ->color(function ($state) {
                        return match ($state){
                            'scheduled' => 'primary',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            'rescheduled' => 'warning'
                        };
                    })
                    ->icon(function ($state){
                        return match ($state){
                            'scheduled' => 'heroicon-m-calendar',
                            'completed' => 'heroicon-m-check-circle',
                            'cancelled' => 'heroicon-m-x-circle',
                            'rescheduled' => 'heroicon-m-calendar',
                        };
                    }),

                Split::make([
                    InfoSection::make()
                        ->schema([
                            TextEntry::make('reason')
                                ->label('Reason')
                                ->markdown()
                                ->columnSpanFull(),
                        ]),
                    InfoSection::make()
                        ->schema([
                            TextEntry::make('notes')
                                ->label('Notes')
                                ->markdown()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull()

            ]);
    }
}
