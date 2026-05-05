<?php

namespace App\Filament\Article\Resources\Article\Articles\Tables;

use App\Filament\Article\Resources\Article\Articles\Actions\StockAdjustmentAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockEntryAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockExitAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockReturnAction;
use App\Filament\Article\Resources\Article\Articles\Actions\StockTransferAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ArticlesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Aucun article dans la base de donnée')
            ->emptyStateIcon(Phosphor::Empty)
            ->emptyStateActions([
                CreateAction::make()
                    ->label('Nouvelle Article')
                    ->icon(Phosphor::PlusCircle),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('articleCategory.name')
                    ->label('Categorie')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_price_customer')
                    ->label('Prix Client')
                    ->money('EUR'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ActionGroup::make([
                    StockEntryAction::make(),
                    StockExitAction::make(),
                    StockTransferAction::make(),
                    StockAdjustmentAction::make(),
                    StockReturnAction::make(),
                ])
                    ->icon('heroicon-s-arrows-right-left')
                    ->tooltip('Mouvements de stock'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
