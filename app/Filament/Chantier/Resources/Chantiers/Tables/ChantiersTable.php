<?php

namespace App\Filament\Chantier\Resources\Chantiers\Tables;

use App\Enums\Chantier\ChantierStatus;
use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierBudgetService;
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

class ChantiersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nom')
                    ->label('Chantier')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Chantier $record) => $record->ville),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('date_fin_prevue')
                    ->label('Fin prévue')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Chantier $record) => $record->date_fin_prevue?->isPast()
                    && ! in_array($record->status, [ChantierStatus::CLOSED, ChantierStatus::ARCHIVED])
                        ? 'danger' : null
                    )
                    ->placeholder('—'),

                TextColumn::make('budget_total')
                    ->label('Budget')
                    ->state(fn (Chantier $record) => app(ChantierBudgetService::class)
                        ->getBudgetTotal($record)
                    )
                    ->money('EUR'),

                TextColumn::make('avancement')
                    ->label('Avancement')
                    ->state(fn (Chantier $record) => app(ChantierBudgetService::class)
                        ->getAvancementGlobal($record).' %'
                    ),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ChantierStatus::class),

                SelectFilter::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'name'),

                SelectFilter::make('responsable_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'name'),

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
