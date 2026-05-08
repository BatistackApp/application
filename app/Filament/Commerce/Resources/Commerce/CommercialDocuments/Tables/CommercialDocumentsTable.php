<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Tables;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Commerce\CommercialDocument;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommercialDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date_document', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('chantier.reference')
                    ->label('Chantier')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('date_document')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_ttc')
                    ->label('Montant TTC')
                    ->money('EUR')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('solde')
                    ->label('Solde')
                    ->state(fn (CommercialDocument $record) => $record->solde)
                    ->money('EUR')
                    ->color(fn (CommercialDocument $record) => $record->solde > 0 ? 'danger' : 'success')
                    ->visible(fn () => request()->get('type') === 'factures'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(DocumentType::class)
                    ->multiple(),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(DocumentStatus::class)
                    ->multiple(),

                Filter::make('impayes')
                    ->label('Impayés uniquement')
                    ->query(fn ($query) => $query->impayes())
                    ->toggle(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
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
