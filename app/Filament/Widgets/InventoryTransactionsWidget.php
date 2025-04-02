<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Forms\Get;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use App\Models\InventoryTransaction;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class InventoryTransactionsWidget extends BaseWidget
{

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 9;

    protected static ?string $maxHeight = '250px';

    public static function canView(): bool
    {
        return auth()->user()->hasAnyRole(['super admin','super_admin','admin']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent Inventory Transactions')
            ->description('Overview of medication inventory movements')
            ->query(
                InventoryTransaction::query()
                ->with(['medication', 'performedBy'])
                ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medication.name')
                    ->label('Medication')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Inward' => 'success',
                        'Outward' => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Quantity')
                    ->sortable(),
                Tables\Columns\TextColumn::make('performedBy.name')
                    ->label('Performed By')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(30)
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('transaction_type')
                    ->options([
                        'ingoing' => 'Ingoing',
                        'outgoing' => 'Outgoing',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Grid::make()
                            ->schema([
                                DatePicker::make('from_date')
                                    ->label('From Date')
                                    ->native(false)
                                    ->rules(['required_with:to_date', 'date'])
                                    ->live()
                                    ->afterStateUpdated(function (Get $get, $state, callable $set) {
                                        // If to_date exists and is before from_date, clear to_date
                                        $toDate = $get('to_date');
                                        if ($toDate && $state && strtotime($state) > strtotime($toDate)) {
                                            $set('to_date', null);
                                        }
                                    }),
                                DatePicker::make('to_date')
                                    ->label('To Date')
                                    ->native(false)
                                    ->rules([
                                        'required_with:from_date',
                                        'date',
                                        function (Get $get) {
                                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $fromDate = $get('from_date');
                                                if ($fromDate && $value && strtotime($value) < strtotime($fromDate)) {
                                                    $fail('The to date must be after or equal to the from date.');
                                                }
                                            };
                                        }
                                    ])
                                    ->after(fn (Get $get): ?string => $get('from_date')),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from_date'],
                                fn (Builder $query, $fromDate): Builder => $query->whereDate('created_at', '>=', $fromDate)
                            )
                            ->when(
                                $data['to_date'],
                                fn (Builder $query, $toDate): Builder => $query->whereDate('created_at', '<=', $toDate)
                            );
                    }),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
