<?php

namespace App\Filament\Article\Resources\Stock\StockMouvements\Tables;

use App\Enums\Article\StockMouvementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMouvementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('article.name')
                    ->label('Article')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('warehouse.name')
                    ->label('Dépôt source')
                    ->searchable(),

                TextColumn::make('targetWarehouse.name')
                    ->label('Dépôt destination')
                    ->placeholder('—'),

                TextColumn::make('quantity')
                    ->label('Quantité')
                    ->numeric(decimalPlaces: 3),

                TextColumn::make('unit_cost_ht')
                    ->label('Coût U. HT')
                    ->money('EUR')
                    ->placeholder('—'),

                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('user.name')
                    ->label('Opérateur')
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('date_from')
                            ->label('Du')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('date_to')
                            ->label('Au')
                            ->default(now()),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['date_to'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->label('Période'),

                SelectFilter::make('type')
                    ->label('Type de mouvement')
                    ->options(StockMouvementType::class),

                SelectFilter::make('warehouse_id')
                    ->label('Dépôt')
                    ->relationship('warehouse', 'name'),

                SelectFilter::make('article_id')
                    ->label('Article')
                    ->relationship('article', 'name'),
            ]);
    }
}
