<?php

namespace App\Filament\Commerce\Widgets;

use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use App\Models\Commerce\CommercialDocument;
use App\Services\Commerce\RelanceService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class FacturesImpayeesWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CommercialDocument::impayes()
                    ->with(['client', 'paiements', 'relances'])
                    ->orderBy('date_echeance')
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Référence')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->limit(30)
                    ->searchable(),

                TextColumn::make('date_echeance')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('danger'),

                TextColumn::make('jours_retard')
                    ->label('Retard')
                    ->state(function (CommercialDocument $record) {
                        $jours = app(RelanceService::class)->getJoursRetard($record);

                        return $jours.' j';
                    })
                    ->badge()
                    ->color(function (CommercialDocument $record) {
                        $jours = app(RelanceService::class)->getJoursRetard($record);

                        return match (true) {
                            $jours <= 7 => 'warning',
                            default => 'danger',
                        };
                    }),

                TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('EUR')
                    ->alignRight(),

                TextColumn::make('solde')
                    ->label('Solde dû')
                    ->state(fn (CommercialDocument $record) => $record->solde)
                    ->money('EUR')
                    ->weight('bold')
                    ->color('danger')
                    ->alignRight(),

                TextColumn::make('nb_relances')
                    ->label('Relances')
                    ->state(fn (CommercialDocument $record) => $record->relances->count())
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('relancer')
                    ->label('Relancer')
                    ->icon(Phosphor::Bell)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Créer une relance')
                    ->modalDescription('Une relance email sera créée pour cette facture.')
                    ->action(function (CommercialDocument $record) {
                        app(RelanceService::class)->createRelance(
                            $record,
                            auth()->user(),
                            'email',
                            "Relance concernant la facture {$record->reference} d'un montant de "
                            .number_format($record->solde, 2, ',', ' ').' € impayée.'
                        );

                        Notification::make()
                            ->title('Relance créée')
                            ->body("Relance enregistrée pour {$record->reference}.")
                            ->success()
                            ->send();
                    }),

                Action::make('view')
                    ->label('Voir')
                    ->icon(Phosphor::Eye)
                    ->url(fn (CommercialDocument $record) => CommercialDocumentResource::getUrl('view', ['record' => $record])),
            ])
            ->heading('Factures impayées')
            ->description('Factures dont l\'échéance est dépassée')
            ->emptyStateHeading('Aucune facture impayée')
            ->emptyStateDescription('Tous les règlements sont à jour ✓')
            ->emptyStateIcon('heroicon-o-check-badge')
            ->paginated([5, 10]);
    }
}
