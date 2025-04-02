<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Medication;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockMedications extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Medications';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        return Medication::query()
            ->where('stock', '<', 10)
            ->orderBy('stock')
            ->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name')
                ->searchable(),

            TextColumn::make('generic_name')
                ->searchable(),

            TextColumn::make('stock')
                ->sortable(),

            TextColumn::make('expiry_date')
                ->date()
                ->sortable()
                ->color(fn ($record) => $record->expiry_date->isPast() ? 'danger' : ($record->expiry_date->diffInMonths(now()) < 3 ? 'warning' : 'success')),
        ];
    }
}
