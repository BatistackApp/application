<?php

namespace App\Services\Stock;

use App\Enums\Article\AdjustementType;
use App\Enums\Article\InventorySessionStatus;
use App\Enums\Article\StockMouvementType;
use App\Models\Core\Warehouse;
use App\Models\Stock\InventoryLine;
use App\Models\Stock\InventorySession;
use App\Models\User;
use DB;
use Illuminate\Validation\ValidationException;

class InventorySessionService
{
    public function __construct(
        private StockMouvementService $stockService,
    ) {}

    /**
     * Crée une nouvelle session et génère les lignes automatiquement.
     */
    public function open(Warehouse $warehouse, User $user, ?string $notes = null): InventorySession
    {
        return DB::transaction(function () use ($warehouse, $user, $notes) {
            $this->ensureNoActiveSession($warehouse);

            $session = InventorySession::create([
                'warehouse_id' => $warehouse->id,
                'reference' => $this->generateReference(),
                'status' => InventorySessionStatus::OPEN,
                'opened_at' => now(),
                'created_by' => $user->id,
                'notes' => $notes,
            ]);

            $this->generateLines($session, $warehouse);

            return $session;
        });
    }

    /**
     * Passe la session en statut COUNTING.
     */
    public function startCounting(InventorySession $session): InventorySession
    {
        $this->ensureStatus($session, InventorySessionStatus::OPEN);

        $session->update(['status' => InventorySessionStatus::COUNTING]);

        return $session->fresh();
    }

    /**
     * Ferme la session — le comptage est terminé.
     */
    public function close(InventorySession $session): InventorySession
    {
        $this->ensureStatus($session, InventorySessionStatus::COUNTING);

        $uncounted = $session->lines()
            ->whereNull('counted_quantity')
            ->count();

        if ($uncounted > 0) {
            throw ValidationException::withMessages([
                'counted_quantity' => "{$uncounted} ligne(s) n'ont pas encore été comptées.",
            ]);
        }

        $session->update([
            'status' => InventorySessionStatus::CLOSED,
            'closed_at' => now(),
        ]);

        return $session->fresh();
    }

    /**
     * Valide la session et applique les ajustements de stock.
     */
    public function validate(InventorySession $session, User $user): InventorySession
    {
        $this->ensureStatus($session, InventorySessionStatus::CLOSED);

        return DB::transaction(function () use ($session, $user) {
            $lines = $session->lines()
                ->with('article')
                ->get()
                ->filter(fn ($line) => $line->counted_quantity !== null
                    && $line->counted_quantity != $line->theoretical_quantity
                );

            foreach ($lines as $line) {
                $difference = $line->counted_quantity - $line->theoretical_quantity;

                $this->stockService->create(
                    type: StockMouvementType::ADJUSTEMENT,
                    article: $line->article,
                    warehouse: $session->warehouse,
                    quantity: abs($difference),
                    user: $user,
                    options: [
                        'adjustement_type' => $difference > 0
                            ? AdjustementType::GAIN
                            : AdjustementType::LOSS,
                        'reference' => $session->reference,
                        'note' => "Ajustement automatique — inventaire {$session->reference}",
                    ],
                );
            }

            $session->update([
                'status' => InventorySessionStatus::VALIDATED,
                'validated_at' => now(),
                'validated_by' => $user->id,
            ]);

            return $session->fresh();
        });
    }

    /**
     * Annule une session OPEN ou COUNTING.
     */
    public function cancel(InventorySession $session): InventorySession
    {
        if (! in_array($session->status, [
            InventorySessionStatus::OPEN,
            InventorySessionStatus::COUNTING,
            InventorySessionStatus::CLOSED,
        ])) {
            throw ValidationException::withMessages([
                'status' => 'Seules les sessions ouvertes, en cours ou fermées peuvent être annulées.',
            ]);
        }

        $session->update(['status' => InventorySessionStatus::CANCELLED]);

        return $session->fresh();
    }

    /**
     * Remet une session CLOSED en COUNTING pour corriger des saisies.
     */
    public function reopen(InventorySession $session): InventorySession
    {
        $this->ensureStatus($session, InventorySessionStatus::CLOSED);

        $session->update([
            'status' => InventorySessionStatus::COUNTING,
            'closed_at' => null,
        ]);

        return $session->fresh();
    }

    /**
     * Sauvegarde la quantité comptée d'une ligne.
     */
    public function saveLine(InventoryLine $line, float $countedQuantity): InventoryLine
    {
        $this->ensureStatus(
            $line->inventorySession,
            InventorySessionStatus::COUNTING,
        );

        $line->update(['counted_quantity' => $countedQuantity]);

        return $line->fresh();
    }

    /**
     * Génère les lignes depuis le stock actuel du dépôt.
     */
    private function generateLines(InventorySession $session, Warehouse $warehouse): void
    {
        $articles = $warehouse->articles()
            ->wherePivot('actual_stock', '>', 0)
            ->get();

        $lines = $articles->map(fn ($article) => [
            'inventory_session_id' => $session->id,
            'article_id' => $article->id,
            'theoretical_quantity' => $article->pivot->actual_stock,
            'counted_quantity' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        InventoryLine::insert($lines);
    }

    /**
     * Vérifie qu'il n'existe pas de session active pour ce dépôt.
     */
    private function ensureNoActiveSession(Warehouse $warehouse): void
    {
        $exists = InventorySession::where('warehouse_id', $warehouse->id)
            ->whereIn('status', [
                InventorySessionStatus::OPEN,
                InventorySessionStatus::COUNTING,
            ])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'warehouse_id' => 'Une session d\'inventaire est déjà en cours pour ce dépôt.',
            ]);
        }
    }

    /**
     * Vérifie que la session est dans le statut attendu.
     */
    private function ensureStatus(InventorySession $session, InventorySessionStatus $expected): void
    {
        if ($session->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "Action impossible — statut actuel : {$session->status->getLabel()}.",
            ]);
        }
    }

    /**
     * Génère une référence unique de type INV-2026-001.
     */
    private function generateReference(): string
    {
        $year = now()->year;
        $count = InventorySession::whereYear('created_at', $year)->count() + 1;

        return sprintf('INV-%d-%03d', $year, $count);
    }
}
