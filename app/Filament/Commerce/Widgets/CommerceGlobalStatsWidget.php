<?php

namespace App\Filament\Commerce\Widgets;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Commerce\CommercialDocument;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommerceGlobalStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // CA du mois (factures payées + partiellement payées)
        $caMois = CommercialDocument::factures()
            ->whereIn('status', [
                DocumentStatus::PAID,
                DocumentStatus::PARTIALLY_PAID,
                DocumentStatus::SENT,
            ])
            ->whereMonth('date_document', now()->month)
            ->whereYear('date_document', now()->year)
            ->sum('total_ht');

        // Devis en cours (envoyés, non acceptés/refusés)
        $nbDevisEnCours = CommercialDocument::where('type', DocumentType::DEVIS)
            ->where('status', DocumentStatus::SENT)
            ->count();

        // Montant total devis en cours
        $montantDevisEnCours = CommercialDocument::where('type', DocumentType::DEVIS)
            ->where('status', DocumentStatus::SENT)
            ->sum('total_ht');

        // Factures impayées
        $nbImpayes = CommercialDocument::impayes()->count();

        // Montant total impayé
        $montantImpayes = CommercialDocument::impayes()->sum('total_ttc')
            - CommercialDocument::impayes()
                ->withSum('paiements', 'montant')
                ->get()
                ->sum('paiements_sum_montant');

        return [
            Stat::make('CA du mois', number_format($caMois, 2, ',', ' ').' €')
                ->description('Factures émises en '.now()->translatedFormat('F'))
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->color('success')
                ->chart([30000, 35000, 28000, 42000, $caMois]),

            Stat::make('Devis en cours', number_format($nbDevisEnCours))
                ->description(number_format($montantDevisEnCours, 0, ',', ' ').' € en attente')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info'),

            Stat::make('Factures impayées', number_format($nbImpayes))
                ->description(number_format(max(0, $montantImpayes), 2, ',', ' ').' € à encaisser')
                ->descriptionIcon('heroicon-o-exclamation-circle')
                ->color($nbImpayes > 0 ? 'danger' : 'success'),

            Stat::make('Taux de transformation', $this->getTauxTransformation().' %')
                ->description('Devis acceptés / émis (12 mois)')
                ->descriptionIcon('heroicon-o-chart-pie')
                ->color('primary'),
        ];
    }

    protected function getTauxTransformation(): float
    {
        $emis = CommercialDocument::where('type', DocumentType::DEVIS)
            ->whereIn('status', [
                DocumentStatus::SENT,
                DocumentStatus::ACCEPTED,
                DocumentStatus::REFUSED,
            ])
            ->whereYear('date_document', now()->year)
            ->count();

        if ($emis === 0) {
            return 0;
        }

        $acceptes = CommercialDocument::where('type', DocumentType::DEVIS)
            ->where('status', DocumentStatus::ACCEPTED)
            ->whereYear('date_document', now()->year)
            ->count();

        return round(($acceptes / $emis) * 100, 1);
    }
}
