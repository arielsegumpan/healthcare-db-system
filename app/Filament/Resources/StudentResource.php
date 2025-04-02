<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Student;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use App\Enums\StudentGradeLevelEnum;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Components\Tabs;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use Filament\Tables\Columns\ImageColumn;
use App\Filament\Imports\StudentImporter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\StudentResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Infolists\Components\Group as InfoGroup;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Filament\Resources\StudentResource\Pages\EditStudent;
use App\Filament\Resources\StudentResource\Pages\ViewStudent;
use Filament\Infolists\Components\Section as StudentSectionInfo;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function getNavigationGroup(): string
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'super-admin', 'super admin'])) {
            return 'Staff';
        }

        if (auth()->user()->hasAnyRole(['medical_staff', 'medical staff', 'staff'])) {
            return 'Student';
        }

        return 'My Info';
    }

    // protected static ?string $navigationGroup = 'Saff';


    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getModelLabel(): string
    {
        if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'super-admin', 'super admin'])) {
            return 'Students';
        }

        if (auth()->user()->hasAnyRole(['medical_staff', 'medical staff', 'staff'])) {
            return 'Students';
        }

        return 'Personal Details';
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
                            ->searchable()
                            ->native(false)
                            ->preload()
                            ->optionsLimit(6)
                            ->required()
                            ->visible(function () {
                                return auth()->user()->hasAnyRole(['super_admin', 'admin', 'super-admin', 'super admin']);
                            }),

                        TextInput::make('lrn_number')
                            ->label('LRN Number')
                            ->required()
                            ->dehydrated()
                            ->maxLength(255),

                        Select::make('gender')
                            ->label('Gender')
                            ->options([
                                'male' => 'Male',
                                'female' => 'Female',
                            ])
                            ->native(false)
                            ->required(),

                        TextInput::make('first_name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('last_name')
                            ->required()
                            ->maxLength(255),

                        Select::make('grade_level')
                            ->label('Grade Level')
                            ->options(StudentGradeLevelEnum::class)
                            ->native(false),

                        TextInput::make('section')
                            ->required()
                            ->maxLength(255),

                        DatePicker::make('date_of_birth')
                            ->label('Birth Date')
                            ->native(false)
                            ->required(),

                        Select::make('blood_group')
                            ->label('Blood Group')
                            ->options([
                                'A+' => 'A+',
                                'A-' => 'A-',
                                'B+' => 'B+',
                                'B-' => 'B-',
                                'O+' => 'O+',
                                'O-' => 'O-',
                                'AB+' => 'AB+',
                                'AB-' => 'AB-',
                            ])
                            ->native(false),

                        TextInput::make('parent_name')
                            ->label('Parent Name')
                            ->maxLength(255),

                        TextInput::make('parent_contact')
                            ->label('Parent Contact #')
                            ->tel()
                            ->maxLength(255),

                        TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->columnSpan([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 2
                            ]),

                        RichEditor::make('allergies')
                            ->label('Allergies')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])
                    ->columns([
                        'sm' => 1,
                        'md' => 2,
                        'lg' => 3
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('user.avatar_url')
                    ->label('Avatar')
                    ->circular()
                    ->width(50)
                    ->height(50)
                    ->defaultImageUrl(url('https://avatar.iran.liara.run/public/1')),

                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn(Student $record): string => $record->lrn_number),

                TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->searchable()
                    ->dateTime('F j, Y'),

                TextColumn::make('section')
                    ->label('Section')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('grade_level')
                    ->label('Grade Level')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gender')
                    ->label('Gender')
                    ->searchable()
                    ->badge()
                    ->icon(function ($state) {
                        return match ($state) {
                            'male' => 'heroicon-m-user-plus',
                            'female' => 'heroicon-m-user-minus',
                            default => 'heroicon-m-question-mark-circle',
                        };
                    })
                    ->color(function ($state) {
                        return match ($state) {
                            'male' => 'warning',
                            'female' => 'success',
                            default => 'gray',
                        };
                    })
                    ->formatStateUsing(fn($state): string => ucwords($state)),


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
            ->headerActions([
                // ImportAction::make('Import Students')
                //     ->importer(StudentImporter::class)
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
                    ->label(__('New Student')),
            ])
            ->emptyStateIcon('heroicon-o-users')
            ->emptyStateHeading('No Students are created')
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if ($user->hasRole('student')) {
                    return $query->where('user_id', $user->id);
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'view' => Pages\ViewStudent::route('/{record}'),
        ];
    }


    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            ViewStudent::class,
            EditStudent::class,
        ]);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Details')
                            ->icon('heroicon-m-document-text')
                            ->iconPosition(IconPosition::Before)
                            ->schema([
                                InfoGroup::make([
                                    ImageEntry::make('user.avatar_url')
                                        ->hiddenLabel()
                                        ->size(300)
                                        ->circular()
                                        ->columnSpan([
                                            'sm' => 1,
                                            'md' => 1,
                                            'lg' => 2
                                        ])
                                        ->defaultImageUrl(url('https://avatar.iran.liara.run/public/1')),

                                    InfoGroup::make([

                                        InfoGroup::make([
                                            TextEntry::make('full_name')
                                                ->label('Name')
                                                ->size(TextEntry\TextEntrySize::Large)
                                                ->weight(FontWeight::Bold)
                                                ->formatStateUsing(fn($state): string => ucwords($state))
                                                ->hiddenlabel()
                                                ->tooltip('Full Name'),

                                            TextEntry::make('grade_level_section')
                                                ->label('Grade Level - Section')
                                                ->hiddenlabel()
                                                ->tooltip('Grade Level - Section')
                                                ->size(TextEntry\TextEntrySize::Large)
                                                ->weight(FontWeight::Bold),
                                        ])
                                            ->columns(['sm' => 1, 'md' => 2, 'lg' => 2])
                                            ->columnSpanFull(),

                                        TextEntry::make('lrn_number')
                                            ->hiddenlabel()
                                            ->badge()
                                            ->color('success')
                                            ->tooltip('LRN Number'),

                                        InfoGroup::make([
                                            TextEntry::make('date_of_birth')
                                                ->label('Date of Birth')
                                                ->date()
                                                ->hiddenlabel()
                                                ->tooltip('Date of Birth')
                                                ->icon('heroicon-m-calendar-days'),

                                            TextEntry::make('gender')
                                                ->label('Gender')
                                                ->hiddenlabel()
                                                ->tooltip('Gender')
                                                ->icon(function ($state) {
                                                    return match ($state) {
                                                        'male' => 'heroicon-m-user-plus',
                                                        'female' => 'heroicon-m-user-minus',
                                                        default => 'heroicon-m-question-mark-circle',
                                                    };
                                                })
                                                ->badge()
                                                ->color(function ($state) {
                                                    return match ($state) {
                                                        'male' => 'danger',
                                                        'female' => 'success',
                                                        default => 'gray',
                                                    };
                                                })
                                                ->formatStateUsing(fn($state): string => ucwords($state)),

                                            TextEntry::make('address')
                                                ->label('Address')
                                                ->hiddenlabel()
                                                ->tooltip('Address')
                                                ->icon('heroicon-m-map-pin')
                                                ->placeholder('Address')
                                                ->columnSpanFull(),

                                            TextEntry::make('parent_name')
                                                ->label('Parent Name')
                                                ->hiddenlabel()
                                                ->tooltip('Parent Name')
                                                ->icon('heroicon-m-user-group')
                                                ->formatStateUsing(fn($state): string => ucwords($state))
                                                ->placeholder('Parent Name'),

                                            TextEntry::make('parent_contact')
                                                ->label('Contact Number')
                                                ->hiddenlabel()
                                                ->tooltip('Parent Number')
                                                ->icon('heroicon-m-phone'),

                                            TextEntry::make('blood_group')
                                                ->label('Blood Group')
                                                ->badge()
                                                ->color('primary')

                                        ])
                                            ->columns([
                                                'sm' => 1,
                                                'md' => 2,
                                                'lg' => 2
                                            ])

                                    ])
                                        ->columnSpan([
                                            'sm' => 1,
                                            'md' => 2,
                                            'lg' => 3
                                        ]),

                                ])
                                    ->columns([
                                        'sm' => 1,
                                        'md' => 3,
                                        'lg' => 5
                                    ]),
                            ]),
                        Tabs\Tab::make('Allergies')
                            ->icon('heroicon-m-information-circle')
                            ->iconPosition(IconPosition::Before)
                            ->schema([
                                TextEntry::make('allergies')
                                    ->markdown()
                                    ->label('Allergies')
                            ])
                    ])
                    ->columnSpanFull(),




            ]);
    }


    public static function canCreate(): bool
    {
        $user = Auth::user();

        // If user is admin, always allow creating students
        if ($user->hasAnyRole(['super_admin', 'admin', 'super-admin'])) {
            return true;
        }

        // For regular users, check if they already have a student record
        // Assuming you have a relationship between User and Student
        return !Student::where('user_id', $user->id)->exists();

        // Alternative if you have the relationship defined on your User model:
        // return !$user->student()->exists();
    }
}
