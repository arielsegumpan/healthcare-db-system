<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\MedicalStaff;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Split;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TimePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Group as InfoGroup;
use App\Filament\Resources\MedicalStaffResource\Pages;
use App\Filament\Resources\MedicalStaffResource\RelationManagers;

class MedicalStaffResource extends Resource
{
    protected static ?string $model = MedicalStaff::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    // protected static ?string $navigationGroup = 'Staff';

    public static function getNavigationGroup(): string
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'super-admin', 'super admin', 'student'])) {
            return 'Staff';
        }
        else {
            return 'My Info';
        }
    }

    public static function getModelLabel(): string
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'super-admin', 'super admin','student'])) {
            return 'Medical Staff';
        } else {
            return 'Personal Details';
        }
    }



    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make()
                ->schema([
                    Select::make('user_id')
                    ->label('User')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->native(false)
                    ->preload()
                    ->optionsLimit(6)
                    ->searchable()
                    ->required(),

                    Select::make('staff_type')
                    ->label('Staff Role')
                    ->options([
                        'nurse' => 'Nurse',
                        'doctor' => 'Doctor'
                    ])
                    ->native(false)
                    ->preload()
                    ->optionsLimit(6)
                    ->searchable()
                    ->required(),

                    TextInput::make('specialization')
                    ->label('Specialization')
                    ->maxlength(255),

                    TextInput::make('license_number')
                    ->label('License #')
                    ->maxlength(255),

                    RichEditor::make('qualification')
                    ->label('Qualification')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                    Group::make([
                        DatePicker::make('from')
                        ->label('From Date')
                        ->locale('ph')
                        ->required()
                        ->native(false)
                        ->live()
                        ->default(now())
                        ->dehydrated()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::calculateExperience($get, $set);
                        }),

                        DatePicker::make('to')
                        ->label('To Date')
                        ->locale('ph')
                        ->required()
                        ->native(false)
                        ->live()
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            static::calculateExperience($get, $set);
                        }),

                        TextInput::make('experience')
                        ->label('Experience')
                        ->disabled()
                        ->dehydrated()
                        ->maxlength(255),
                    ])
                    ->columnSpanFull()
                    ->columns([
                        'sm' => 1,
                        'md' => 3,
                        'lg' => 3,
                    ])


                ])
                ->columns([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2
                ]),


                Section::make()
                ->schema([

                    Repeater::make('availability')
                    ->schema([

                        DatePicker::make('date')
                        ->date()
                        ->label('Date')
                        ->native(false)
                        ->required()
                        ->default(now())
                        ->dehydrated(),

                        TimePicker::make('from')
                        ->label('From (AM)')
                        ->required()
                        ->native(false)
                        ->format('g:i A')  // 12-hour format with AM/PM
                        ->seconds(false)
                        ->beforeStateDehydrated(function ($state, callable $set, $get) {
                            // Ensure the time is set to AM format
                            if ($state) {
                                $time = \Carbon\Carbon::parse($state);
                                // Force the time to be AM if it's PM
                                if ($time->format('A') === 'PM' && $time->hour >= 12) {
                                    $time->subHours(12);
                                }
                                $set('from', $time->format('H:i:s'));
                            }
                        }),

                        TimePicker::make('to')
                        ->label('To (PM)')
                        ->required()
                        ->native(false)
                        ->format('g:i A')  // 12-hour format with AM/PM
                        ->seconds(false)
                        ->beforeStateDehydrated(function ($state, callable $set, $get) {
                            // Ensure the time is set to PM format
                            if ($state) {
                                $time = \Carbon\Carbon::parse($state);
                                // Force the time to be PM if it's AM
                                if ($time->format('A') === 'AM' && $time->hour < 12) {
                                    $time->addHours(12);
                                }
                                $set('to', $time->format('H:i:s'));
                            }
                        })


                    ])
                    ->reorderableWithButtons()
                    ->addActionLabel('Add Availability')
                    ->itemLabel(fn (array $state): ?string =>
                        isset($state['date']) && !empty($state['date'])
                            ? \Carbon\Carbon::parse($state['date'])->format('F d, Y')
                            : null
                    )
                    ->cloneable()
                    ->columns([
                        'sm' => 1,
                        'md' => 3,
                        'lg' => 3
                    ])
                    ->grid(2)


                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('user.name')
                ->label('User')
                ->searchable()
                ->sortable(),

                TextColumn::make('staff_type')
                ->label('Staff')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn(string $state): string => ucwords($state)),

                TextColumn::make('specialization')
                ->label('Specialization')
                ->searchable()
                ->sortable()
                ->badge()
                ->color('primary')
                ->formatStateUsing(fn(string $state): string => ucwords($state)),

                TextColumn::make('experience')
                ->label('Experience/s')

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
                ->label(__('New Medical Staff')),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('No Medical Staff are created')
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(function (Builder $query) {

                if (auth()->user()->hasAnyRole(['medical staff', 'medical_staff', 'nurse', 'doctor'])) {
                    return $query->where('user_id', auth()->id());
                }

                if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'super-admin', 'super admin', 'staff', 'student'])) {
                    return $query;
                }

                // For other roles, return an empty query
                return $query->whereNull('id');
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
            'index' => Pages\ListMedicalStaff::route('/'),
            'create' => Pages\CreateMedicalStaff::route('/create'),
            'edit' => Pages\EditMedicalStaff::route('/{record}/edit'),
        ];
    }


    protected static function calculateExperience(Get $get, Set $set): void
    {
        $fromDate = $get('from');
        $toDate = $get('to');

        if (empty($fromDate) || empty($toDate)) {
            $set('experience', null);
            return;
        }

        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        // Check if dates are valid
        if ($from->gt($to)) {
            $set('experience', 'Invalid date range');
            return;
        }

        // Calculate total difference in months
        $totalMonths = $from->diffInMonths($to);

        // Calculate years and remaining months
        $years = floor($totalMonths / 12);
        $months = $totalMonths % 12;

        // Get remaining days by using diff() method
        $remainingDays = round($from->addMonths($totalMonths)->diffInDays($to));

        $experience = '';

        if ($years > 0) {
            $experience .= $years . ' ' . ($years == 1 ? 'year' : 'years');
        }

        if ($months > 0) {
            if (!empty($experience)) $experience .= ' and ';
            $experience .= $months . ' ' . ($months == 1 ? 'month' : 'months');
        }

        if ($remainingDays > 0) {
            if (!empty($experience)) $experience .= ' and ';
            $experience .= $remainingDays . ' ' . ($remainingDays == 1 ? 'day' : 'days');
        }

        $set('experience', $experience);
    }


    public static function infolist(Infolist $infolist): Infolist
     {
         return $infolist
             ->schema([

                TextEntry::make('license_number')
                ->label('License #')
                ->size(TextEntry\TextEntrySize::Large)
                ->badge()
                ->color('success')
                ->formatStateUsing(fn (string $state): string => strtoupper($state))
                ->columnSpanFull(),

                Split::make([
                    ImageEntry::make('user.avatar_url')
                    ->hiddenLabel()
                    ->square()
                    ->defaultImageUrl(url('https://avatar.iran.liara.run/public/1'))
                    ->grow(false),

                    InfoGroup::make([
                        TextEntry::make('user.name')
                        ->label('Staff')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->formatStateUsing(fn (string $state): string => ucwords($state)),

                        TextEntry::make('staff_type')
                        ->label('Designation')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->badge()
                        ->color('warning')
                        ->formatStateUsing(fn (string $state): string => ucwords($state)),

                        TextEntry::make('experience')
                        ->label('Experience')
                        ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                        TextEntry::make('specialization')
                        ->label('Specialization')
                        ->size(TextEntry\TextEntrySize::Large)
                        ->weight(FontWeight::Bold)
                        ->badge()
                        ->color('primary')
                        ->formatStateUsing(fn (string $state): string => ucwords($state)),
                    ])
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 2
                    ]),
                ])
                ->from('md')
                ->columnSpanFull(),


                TextEntry::make('qualification')
                ->label('Qualification')
                ->markdown()
                ->columnSpanFull(),
                RepeatableEntry::make('availability')
                ->label('Availability')
                ->schema([

                    TextEntry::make('date')
                    ->hiddenlabel()
                    ->date('l')
                    ->size(TextEntry\TextEntrySize::Large)
                    ->weight(FontWeight::Bold)
                    ->columnSpanFull(),

                    TextEntry::make('from')
                    ->label('from')
                    ->time('g:i a')
                    ->badge()
                    ->color('danger'),

                    TextEntry::make('to')
                    ->label('To')
                    ->time('g:i a')
                    ->badge()
                    ->color('primary'),


                ])
                ->grid(2)
                ->columns([
                    'sm' => 1,
                    'md' => 2,
                ])
                ->columnSpanFull()
             ]);
     }
}
