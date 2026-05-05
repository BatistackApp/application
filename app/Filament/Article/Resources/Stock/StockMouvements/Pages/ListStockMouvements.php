<?php

namespace App\Filament\Article\Resources\Stock\StockMouvements\Pages;

use App\Filament\Article\Resources\Stock\StockMouvements\Actions\PrintStockMouvementAction;
use App\Filament\Article\Resources\Stock\StockMouvements\StockMouvementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockMouvements extends ListRecords
{
    protected static string $resource = StockMouvementResource::class;

    protected static ?string $title = 'Mouvements de stock';

    protected function getHeaderActions(): array
    {
        return [
            PrintStockMouvementAction::make()
                ->arguments($this->extractActiveFilters()),
        ];
    }

    /**
     * Extrait les valeurs des filtres actifs de la table
     * pour les passer à la modale d'impression.
     */
    private function extractActiveFilters(): array
    {
        $filters = $this->tableFilters ?? [];

        return array_filter([
            'date_from'    => $filters['created_at']['date_from'] ?? null,
            'date_to'      => $filters['created_at']['date_to'] ?? null,
            'warehouse_id' => $filters['warehouse_id']['value'] ?? null,
            'type'         => $filters['type']['value'] ?? null,
            'article_id'   => $filters['article_id']['value'] ?? null,
        ]);
    }
}
