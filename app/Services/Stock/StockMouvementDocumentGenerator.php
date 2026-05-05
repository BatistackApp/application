<?php

namespace App\Services\Stock;

use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use App\Models\Stock\StockMouvement;
use App\Services\Core\DocumentService;
use Carbon\Carbon;

class StockMouvementDocumentGenerator extends DocumentService
{
    /**
     * Journal chronologique des mouvements avec filtres appliqués.
     */
    public function journal(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $warehouseId = null,
        ?string $type = null,
        ?int $articleId = null,
    ): string {
        $query = StockMouvement::query()
            ->with(['article', 'warehouse', 'targetWarehouse', 'user'])
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->orderBy('created_at', 'desc');

        if ($warehouseId) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                    ->orWhere('target_warehouse_id', $warehouseId);
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        if ($articleId) {
            $query->where('article_id', $articleId);
        }

        $mouvements = $query->get();

        $data = [
            'mouvements' => $mouvements,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'warehouse' => $warehouseId ? Warehouse::find($warehouseId) : null,
            'type' => $type,
            'total_entries' => $mouvements->whereIn('type', ['entry', 'return'])->sum('quantity'),
            'total_exits' => $mouvements->whereIn('type', ['exit'])->sum('quantity'),
            'title' => 'Journal des mouvements de stock',
        ];

        $filename = 'journal_stock_'.$dateFrom->format('Ymd').'_'.$dateTo->format('Ymd');

        return $this->generate('pdf.stock.journal', $data, $filename, 'stock', 'landscape');
    }

    /**
     * Récapitulatif des flux par article sur la période.
     */
    public function recapArticle(
        Carbon $dateFrom,
        Carbon $dateTo,
        ?int $warehouseId = null,
    ): string {
        $query = StockMouvement::query()
            ->with(['article.articleCategory'])
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('
                article_id,
                SUM(CASE WHEN type IN ("entry", "return") THEN quantity ELSE 0 END) as total_entrees,
                SUM(CASE WHEN type = "exit" THEN quantity ELSE 0 END) as total_sorties,
                SUM(CASE WHEN type = "transfer" THEN quantity ELSE 0 END) as total_transferts,
                SUM(CASE WHEN type = "adjustement" AND adjustement_type = "gain" THEN quantity ELSE 0 END) as total_gain,
                SUM(CASE WHEN type = "adjustement" AND adjustement_type = "loss" THEN quantity ELSE 0 END) as total_perte,
                COUNT(*) as nb_mouvements
            ')
            ->groupBy('article_id');

        $lignes = $query->get()->map(function ($row) {
            $row->article = Article::with('articleCategory')->find($row->article_id);

            return $row;
        });

        $data = [
            'lignes' => $lignes,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'warehouse' => $warehouseId ? Warehouse::find($warehouseId) : null,
            'title' => 'Récapitulatif des mouvements par article',
        ];

        $filename = 'recap_article_'.$dateFrom->format('Ymd').'_'.$dateTo->format('Ymd');

        return $this->generate('pdf.stock.recap_article', $data, $filename, 'stock', 'landscape');
    }

    /**
     * Récapitulatif des flux par dépôt sur la période.
     */
    public function recapDepot(
        Carbon $dateFrom,
        Carbon $dateTo,
    ): string {
        $query = StockMouvement::query()
            ->with(['warehouse'])
            ->whereBetween('created_at', [$dateFrom->startOfDay(), $dateTo->endOfDay()])
            ->selectRaw('
                warehouse_id,
                SUM(CASE WHEN type IN ("entry", "return") THEN quantity ELSE 0 END) as total_entrees,
                SUM(CASE WHEN type = "exit" THEN quantity ELSE 0 END) as total_sorties,
                SUM(CASE WHEN type = "transfer" THEN quantity ELSE 0 END) as total_transferts,
                COUNT(*) as nb_mouvements
            ')
            ->groupBy('warehouse_id');

        $lignes = $query->get()->map(function ($row) {
            $row->warehouse = Warehouse::find($row->warehouse_id);

            return $row;
        });

        $data = [
            'lignes' => $lignes,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'title' => 'Récapitulatif des mouvements par dépôt',
        ];

        $filename = 'recap_depot_'.$dateFrom->format('Ymd').'_'.$dateTo->format('Ymd');

        return $this->generate('pdf.stock.recap_depot', $data, $filename, 'stock');
    }
}
