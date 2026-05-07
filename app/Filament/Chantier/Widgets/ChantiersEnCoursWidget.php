<?php

namespace App\Filament\Chantier\Widgets;

use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierBudgetService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ChantiersEnCoursWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Chantier::with(['client', 'responsable'])
                ->enCours()
                ->latest('created_at'))
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
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('responsable.name')
                    ->label('Responsable')
                    ->placeholder('—'),

                TextColumn::make('budget')
                    ->label('Budget')
                    ->state(function (Chantier $record) {
                        $service = app(ChantierBudgetService::class);

                        return number_format($service->getBudgetTotal($record), 0, ',', ' ').' €';
                    })
                    ->alignRight(),

                TextColumn::make('avancement')
                    ->label('Avancement')
                    ->state(function (Chantier $record) {
                        $service = app(ChantierBudgetService::class);

                        return $service->getAvancementGlobal($record).' %';
                    })
                    ->alignRight(),

                TextColumn::make('sante')
                    ->label('Santé')
                    ->state(function (Chantier $record) {
                        $service = app(ChantierBudgetService::class);
                        $kpis = $service->getKpis($record);
                        $ecart = $kpis['taux_avancement_vs_conso'];

                        if ($ecart >= 10) {
                            return '✓ Bon';
                        } elseif ($ecart >= 0) {
                            return '~ Moyen';
                        } else {
                            return '⚠ Alerte';
                        }
                    })
                    ->badge()
                    ->color(function (Chantier $record) {
                        $service = app(ChantierBudgetService::class);
                        $kpis = $service->getKpis($record);
                        $ecart = $kpis['taux_avancement_vs_conso'];

                        return match (true) {
                            $ecart >= 10 => 'success',
                            $ecart >= 0 => 'warning',
                            default => 'danger',
                        };
                    }),
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
            ->heading('Chantiers en cours')
            ->description('Chantiers ouverts, actifs ou en pause')
            ->emptyStateHeading('Aucun chantier en cours')
            ->emptyStateDescription('Tous les chantiers sont terminés ou archivés')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated([10, 25]);
    }
}
