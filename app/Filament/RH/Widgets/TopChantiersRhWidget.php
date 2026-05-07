<?php

namespace App\Filament\RH\Widgets;

use App\Enums\RH\PointageStatus;
use App\Models\Chantier\Chantier;
use App\Models\RH\PointageSession;
use App\Services\RH\PointageCoutCalculator;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TopChantiersRhWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $calculator = app(PointageCoutCalculator::class);

        // Récupération sessions du mois
        $sessions = PointageSession::with(['lines.chantier', 'lines.session.employee'])
            ->whereIn('status', [
                PointageStatus::VALIDATED,
                PointageStatus::IMPUTED,
            ])
            ->whereMonth('semaine_du', now()->month)
            ->whereYear('semaine_du', now()->year)
            ->get();

        // Agrégation par chantier
        $parChantier = collect();
        foreach ($sessions as $session) {
            $couts = $calculator->getCoutSession($session);
            foreach ($couts['par_chantier'] as $chantierId => $data) {
                if (! $parChantier->has($chantierId)) {
                    $parChantier[$chantierId] = [
                        'chantier' => $data['chantier'],
                        'heures' => 0,
                        'main_oeuvre' => 0,
                        'trajet' => 0,
                        'total' => 0,
                    ];
                }
                $parChantier[$chantierId]['heures'] += $data['heures'];
                $parChantier[$chantierId]['main_oeuvre'] += $data['main_oeuvre'];
                $parChantier[$chantierId]['trajet'] += $data['trajet'];
                $parChantier[$chantierId]['total'] += $data['total'];
            }
        }

        // Top 5
        $top5 = $parChantier->sortByDesc('heures')->take(5);

        // Si aucune donnée, on retourne une query vide
        if ($top5->isEmpty()) {
            return $table
                ->query(
                    \App\Models\Chantier\Chantier::query()->whereRaw('1 = 0')
                )
                ->columns([
                    TextColumn::make('reference')->label('Référence'),
                    TextColumn::make('nom')->label('Chantier'),
                ])
                ->heading('Top 5 chantiers par heures imputées')
                ->description('Classement du mois en cours')
                ->emptyStateHeading('Aucune heure imputée ce mois')
                ->emptyStateDescription('Les pointages validés apparaîtront ici')
                ->emptyStateIcon('heroicon-o-clock')
                ->paginated(false);
        }

        $ids = $top5->keys()->toArray();

        return $table
            ->query(fn (): Builder => Chantier::query()
                ->whereIn('id', $ids)
                ->orderByRaw('FIELD(id, '.implode(',', $ids).')'))
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->weight('bold'),

                TextColumn::make('nom')
                    ->label('Chantier')
                    ->limit(40),

                TextColumn::make('heures')
                    ->label('Heures')
                    ->state(function ($record) use ($top5) {
                        return number_format($top5[$record->id]['heures'], 1, ',', ' ').' h';
                    })
                    ->alignRight(),

                TextColumn::make('cout_mo')
                    ->label('Coût MO')
                    ->state(function ($record) use ($top5) {
                        return number_format($top5[$record->id]['main_oeuvre'], 2, ',', ' ').' €';
                    })
                    ->alignRight(),

                TextColumn::make('cout_trajet')
                    ->label('Trajet')
                    ->state(function ($record) use ($top5) {
                        return number_format($top5[$record->id]['trajet'], 2, ',', ' ').' €';
                    })
                    ->alignRight()
                    ->placeholder('—'),

                TextColumn::make('cout_total')
                    ->label('Total')
                    ->state(function ($record) use ($top5) {
                        return number_format($top5[$record->id]['total'], 2, ',', ' ').' €';
                    })
                    ->alignRight()
                    ->weight('bold')
                    ->color('primary'),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn ($record) => route(
                        'filament.chantier.resources.chantiers.view',
                        ['record' => $record]
                    )),
            ])
            ->heading('Top 5 chantiers par heures imputées')
            ->description('Classement du mois en cours')
            ->emptyStateHeading('Aucune heure imputée ce mois')
            ->emptyStateDescription('Les pointages validés apparaîtront ici')
            ->emptyStateIcon('heroicon-o-clock')
            ->paginated(false);
    }
}
