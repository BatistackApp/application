<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Tables;

use App\Enums\Article\InventorySessionStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class InventorySessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('warehouse.name')
                    ->label('Dépôt')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('opened_at')
                    ->label('Ouvert le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('lines_count')
                    ->label('Articles')
                    ->counts('lines'),

                TextColumn::make('creator.name')
                    ->label('Créé par'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(InventorySessionStatus::class),

                SelectFilter::make('warehouse_id')
                    ->label('Dépôt')
                    ->relationship('warehouse', 'name'),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
