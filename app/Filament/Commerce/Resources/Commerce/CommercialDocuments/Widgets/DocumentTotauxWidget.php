<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Widgets;

use App\Models\Commerce\CommercialDocument;
use App\Services\Commerce\CommercialCalculator;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class DocumentTotauxWidget extends StatsOverviewWidget
{
    public ?CommercialDocument $record = null;

    protected static ?int $sort = 99;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $calculator = app(CommercialCalculator::class);
        $totaux = $calculator->calculateDocumentTotals($this->record);

        $stats = [
            Stat::make('Total HT', number_format($totaux['total_ht'], 2, ',', ' ').' €')
                ->description('Montant hors taxes')
                ->color('primary'),

            Stat::make('Total TVA', number_format($totaux['total_tva'], 2, ',', ' ').' €')
                ->description('TVA collectée')
                ->color('warning'),

            Stat::make('Total TTC', number_format($totaux['total_ttc'], 2, ',', ' ').' €')
                ->description('Montant toutes taxes')
                ->color('success'),
        ];

        // Solde uniquement pour les factures
        if ($this->record->isFacture()) {
            $solde = $calculator->calculateSolde($this->record);
            $stats[] = Stat::make('Solde à payer', number_format($solde, 2, ',', ' ').' €')
                ->description($solde > 0 ? 'Montant restant dû' : 'Facture soldée ✓')
                ->color($solde > 0 ? 'danger' : 'success');
        }

        return $stats;
    }
}
