<?php

namespace App\Filament\Chantier\Widgets;

use App\Enums\Chantier\ChantierStatus;
use App\Models\Chantier\Chantier;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ChantiersEnRetardWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Chantier::with(['client', 'responsable'])
                ->whereNotIn('status', [
                    ChantierStatus::CLOSED,
                    ChantierStatus::ARCHIVED,
                ])
                ->whereNotNull('date_fin_prevue')
                ->where('date_fin_prevue', '<', now())
                ->orderBy('date_fin_prevue'))
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('nom')
                    ->label('Chantier')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->limit(30),

                TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->placeholder('—'),

                TextColumn::make('date_fin_prevue')
                    ->label('Fin prévue')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('retard')
                    ->label('Retard')
                    ->state(function (Chantier $record) {
                        $jours = now()->diffInDays($record->date_fin_prevue);

                        return $jours.' jour'.($jours > 1 ? 's' : '');
                    })
                    ->badge()
                    ->color('danger'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn (Chantier $record) => route(
                        'filament.chantier.resources.chantiers.view',
                        ['record' => $record]
                    )),
            ])
            ->heading('Chantiers en retard')
            ->description('Chantiers dont la date de fin prévue est dépassée')
            ->emptyStateHeading('Aucun chantier en retard')
            ->emptyStateDescription('Tous les chantiers respectent leurs délais')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->paginated([5, 10]);
    }
}
