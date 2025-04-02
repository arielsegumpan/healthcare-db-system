<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use App\Models\Medication;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Grouping\Group as TableGroup;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MedicationResource\Pages;
use App\Filament\Resources\MedicationResource\RelationManagers;

class MedicationResource extends Resource
{
    protected static ?string $model = Medication::class;

    protected static ?string $navigationIcon = 'heroicon-o-battery-100';

    protected static ?string $navigationGroup = 'Prescription';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                FileUpload::make('med_img')
                ->image()
                ->imageEditor()
                ->columnspanFull()
                ->imageEditorAspectRatios([
                    '1:1',
                ])
                ->imageCropAspectRatio('1:1')
                ->imageResizeTargetWidth('200')
                ->imageResizeTargetHeight('200'),

               Group::make([
                    TextInput::make('name')
                    ->label('Name')
                    ->maxLength(255)
                    ->required()
                    ->unique(Medication::class, 'name', ignoreRecord: true),

                    TextInput::make('generic_name')
                    ->label('Generic Name')
                    ->maxLength(255)
                    ->required()
                    ->unique(Medication::class, 'generic_name', ignoreRecord: true),

                    TextInput::make('brand_name')
                    ->label('Brand Name')
                    ->maxLength(255)
                    ->required()
                    ->unique(Medication::class, 'brand_name', ignoreRecord: true),

               ])
               ->columnSpanFull()
               ->columns([
                    'sm' => 1,
                    'md' => 3,
                    'lg' => 3
               ]),

                RichEditor::make('description')
                ->maxLength(65535)
                ->label('Description')
                ->columnSpanFull(),

                Select::make('dosage_form')
                ->label('Dosage Form')
                ->native(false)
                ->options([
                    'Syrup',
                    'Injection',
                    'Capsule',
                    'Tablet',
                ]),

                TextInput::make('stock')
                ->label('Stock')
                ->numeric()
                ->required(),

                DatePicker::make('expiry_date')
                ->label('Expiry Date')
                ->required()
                ->dehydrated()
                ->default(now())
                ->native(false),

                TextInput::make('manufacturer')
                ->label('Manufacturer')
                ->maxLength(255)
                ->required(),

                RichEditor::make('notes')
                ->maxLength(65535)
                ->label('Notes')
                ->columnSpanFull(),



            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Grid::make([
                    'lg' => 2,
                    '2xl' => 2,
                ]),

                Stack::make([

                    TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable()
                    ->size(TextColumn\TextColumnSize::Large)
                    ->weight('bold')
                    ->formatStateUsing(fn ($state): string => ucwords($state)),

                    Split::make([
                        ImageColumn::make('med_img')
                        ->label('Image')
                        ->square()
                        ->height('100%')
                        ->width('100%'),

                       Stack::make([

                            TextColumn::make('generic_name')
                            ->label('Generic Name')
                            ->sortable()
                            ->searchable()
                            ->size(TextColumn\TextColumnSize::Large)
                            ->weight('bold')
                            ->formatStateUsing(fn ($state): string => ucwords($state))
                            ->tooltip('Generic Name'),

                            TextColumn::make('brand_name')
                            ->label('Brand Name')
                            ->sortable()
                            ->searchable()
                            ->badge()
                            ->color('primary')
                            ->icon('heroicon-m-check-badge')
                            ->formatStateUsing(fn ($state): string => ucwords($state))
                            ->tooltip('Brand name'),

                            TextColumn::make('stock')
                            ->label('Stock')
                            ->sortable()
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-m-archive-box')
                            ->tooltip('Remaining stock/s'),
                       ])
                       ->space(3)
                    ]),




                ])->space(3),
                Panel::make([

                    Stack::make([
                        Split::make([
                            TextColumn::make('dosage_form')
                            ->label('Dosage')
                            ->sortable()
                            ->searchable()
                            ->icon('heroicon-m-eye-dropper')
                            ->tooltip('Dosage form')
                            ->formatStateUsing(fn ($state): string => ucwords($state)),

                            TextColumn::make('expiry_date')
                            ->label('Expiry Date')
                            ->icon('heroicon-m-calendar-days')
                            ->sortable()
                            ->searchable()
                            ->date()
                            ->tooltip('Expiration date'),
                        ]),

                        TextColumn::make('manufacturer')
                        ->label('Manufacturer')
                        ->sortable()
                        ->searchable()
                        ->icon('heroicon-m-cog')
                        ->formatStateUsing(fn ($state): string => ucwords($state))
                        ->tooltip('Manufacturer'),


                        TextColumn::make('notes')
                        ->label('Notes')
                        ->markdown()
                        ->limit(100),
                    ])->space(3)

                ])->collapsible(),

            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 2,
            ])
            ->paginated([
                6,
                12,
                24,
                'all',
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

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
                ->label(__('New Medication')),
            ])
            ->emptyStateIcon('heroicon-o-battery-100')
            ->emptyStateHeading('No Medications are created')
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
            'index' => Pages\ListMedications::route('/'),
            'create' => Pages\CreateMedication::route('/create'),
            'edit' => Pages\EditMedication::route('/{record}/edit'),
        ];
    }
}
