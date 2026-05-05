<?php

namespace App\Services\Stock;

use App\Enums\Article\AdjustementType;
use App\Enums\Article\SerialNumberStatus;
use App\Enums\Article\StockMouvementType;
use App\Enums\Article\TrackingType;
use App\Models\Article\Article;
use App\Models\Article\ArticleSerialNumber;
use App\Models\Core\Warehouse;
use App\Models\Stock\StockMouvement;
use App\Models\User;
use DB;
use Illuminate\Validation\ValidationException;

class StockMouvementService
{
    /**
     * Point d'entrée unique pour créer un mouvement de stock.
     * Toute la logique métier passe par ici.
     */
    public function create(
        StockMouvementType $type,
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        ?User $user = null,
        array $options = [],
    ): StockMouvement {
        return DB::transaction(function () use ($type, $article, $warehouse, $quantity, $user, $options) {
            $this->validateMovement($type, $article, $warehouse, $quantity, $options);

            $mouvement = StockMouvement::create([
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'target_warehouse_id' => $options['target_warehouse_id'] ?? null,
                'user_id' => $user?->id,
                'serial_number_id' => $options['serial_number_id'] ?? null,
                'ouvrage_id' => $options['ouvrage_id'] ?? null,
                'type' => $type,
                'adjustement_type' => $options['adjustement_type'] ?? null,
                'quantity' => $quantity,
                'unit_cost_ht' => $options['unit_cost_ht'] ?? null,
                'reference' => $options['reference'] ?? null,
                'note' => $options['note'] ?? null,
            ]);

            $this->applyStockEffect($mouvement, $article, $warehouse, $quantity, $options);

            if ($article->tracking_type === TrackingType::SERIAL_NUMBER && isset($options['serial_number_id'])) {
                $this->applySerialNumberEffect($type, $options['serial_number_id'], $options);
            }

            return $mouvement;
        });
    }

    /**
     * Valide les règles métier avant d'appliquer le mouvement.
     */
    private function validateMovement(
        StockMouvementType $type,
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        array $options,
    ): void {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'La quantité doit être supérieure à zéro.',
            ]);
        }

        if ($type === StockMouvementType::TRANSFER) {
            if (empty($options['target_warehouse_id'])) {
                throw ValidationException::withMessages([
                    'target_warehouse_id' => 'Un dépôt de destination est requis pour un transfert.',
                ]);
            }

            if ($options['target_warehouse_id'] === $warehouse->id) {
                throw ValidationException::withMessages([
                    'target_warehouse_id' => 'Le dépôt source et destination doivent être différents.',
                ]);
            }
        }

        if ($type === StockMouvementType::ADJUSTEMENT && empty($options['adjustement_type'])) {
            throw ValidationException::withMessages([
                'adjustement_type' => "Le type d'ajustement est requis.",
            ]);
        }

        // Vérification du stock disponible pour les mouvements sortants
        if (in_array($type, [StockMouvementType::EXIT, StockMouvementType::TRANSFER])) {
            $this->validateSufficientStock($article, $warehouse, $quantity);
        }

        if ($type === StockMouvementType::ADJUSTEMENT
            && isset($options['adjustement_type'])
            && $options['adjustement_type'] === AdjustementType::LOSS) {
            $this->validateSufficientStock($article, $warehouse, $quantity);
        }
    }

    /**
     * Vérifie que le stock disponible est suffisant.
     */
    private function validateSufficientStock(Article $article, Warehouse $warehouse, float $quantity): void
    {
        $pivot = $this->getOrCreatePivot($article, $warehouse);

        if ($pivot->actual_stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Stock insuffisant. Disponible : {$pivot->actual_stock}, demandé : {$quantity}.",
            ]);
        }
    }

    /**
     * Applique l'effet du mouvement sur le(s) pivot(s) article_warehouse.
     */
    private function applyStockEffect(
        StockMouvement $mouvement,
        Article $article,
        Warehouse $warehouse,
        float $quantity,
        array $options,
    ): void {
        match ($mouvement->type) {
            StockMouvementType::ENTRY,
            StockMouvementType::RETURN => $this->incrementStock($article, $warehouse, $quantity),

            StockMouvementType::EXIT => $this->decrementStock($article, $warehouse, $quantity),

            StockMouvementType::TRANSFER => $this->transferStock(
                $article,
                $warehouse,
                Warehouse::find($options['target_warehouse_id']),
                $quantity,
            ),

            StockMouvementType::ADJUSTEMENT => $this->adjustStock(
                $article,
                $warehouse,
                $quantity,
                $options['adjustement_type'],
            ),
        };
    }

    private function incrementStock(Article $article, Warehouse $warehouse, float $quantity): void
    {
        $pivot = $this->getOrCreatePivot($article, $warehouse);

        $article->warehouses()->updateExistingPivot($warehouse->id, [
            'actual_stock' => $pivot->actual_stock + $quantity,
        ]);
    }

    private function decrementStock(Article $article, Warehouse $warehouse, float $quantity): void
    {
        $pivot = $this->getOrCreatePivot($article, $warehouse);

        $article->warehouses()->updateExistingPivot($warehouse->id, [
            'actual_stock' => max(0, $pivot->actual_stock - $quantity),
        ]);
    }

    private function transferStock(Article $article, Warehouse $source, Warehouse $target, float $quantity): void
    {
        $this->decrementStock($article, $source, $quantity);
        $this->incrementStock($article, $target, $quantity);
    }

    private function adjustStock(Article $article, Warehouse $warehouse, float $quantity, AdjustementType $type): void
    {
        match ($type) {
            AdjustementType::GAIN => $this->incrementStock($article, $warehouse, $quantity),
            AdjustementType::LOSS => $this->decrementStock($article, $warehouse, $quantity),
        };
    }

    /**
     * Met à jour le statut d'un numéro de série selon le type de mouvement.
     */
    private function applySerialNumberEffect(
        StockMouvementType $type,
        int $serialNumberId,
        array $options,
    ): void {
        $serial = ArticleSerialNumber::findOrFail($serialNumberId);

        $status = match ($type) {
            StockMouvementType::ENTRY,
            StockMouvementType::RETURN, StockMouvementType::TRANSFER => SerialNumberStatus::IN_STOCK,
            StockMouvementType::EXIT => SerialNumberStatus::SOLD,
            StockMouvementType::ADJUSTEMENT => $options['adjustement_type'] === AdjustementType::LOSS
                ? SerialNumberStatus::LOST
                : SerialNumberStatus::IN_STOCK,
        };

        $serial->update([
            'status' => $status,
            'warehouse_id' => match ($type) {
                StockMouvementType::ENTRY, StockMouvementType::RETURN => $serial->warehouse_id, // <-- Correction ici
                StockMouvementType::TRANSFER => $options['target_warehouse_id'],
                default => null, // Pour EXIT, ADJUSTEMENT (LOSS)
            },
        ]);
    }

    /**
     * Récupère ou crée la ligne pivot article_warehouse.
     * Retourne le pivot avec les valeurs actuelles.
     */
    public function getOrCreatePivot(Article $article, Warehouse $warehouse): object
    {
        $pivot = $article->warehouses()->where('warehouse_id', $warehouse->id)->first()?->pivot;

        if (! $pivot) {
            $article->warehouses()->attach($warehouse->id, [
                'min_stock' => 0,
                'max_stock' => 0,
                'alert_stock' => 0,
                'actual_stock' => 0,
                'bin_location' => null,
            ]);

            $pivot = $article->warehouses()->where('warehouse_id', $warehouse->id)->first()->pivot;
        }

        return $pivot;
    }
}
