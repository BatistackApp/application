<?php

use App\Enums\Article\AdjustementType;
use App\Enums\Article\StockMouvementType;
use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use App\Models\Stock\StockMouvement;
use App\Models\User;
use App\Services\Stock\StockMouvementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(StockMouvementService::class);
    $this->user = User::factory()->create();
    $this->article = Article::factory()->create();
    $this->warehouse = Warehouse::factory()->create();

    actingAs($this->user);
});

// ─── Helpers ────────────────────────────────────────────────────────────────

function attachWithStock(Article $article, Warehouse $warehouse, float $stock): void
{
    $article->warehouses()->attach($warehouse->id, [
        'actual_stock' => $stock,
        'min_stock' => 0,
        'max_stock' => 0,
        'alert_stock' => 0,
        'bin_location' => null,
    ]);
}

function getPivotStock(Article $article, Warehouse $warehouse): float
{
    return (float) $article->warehouses()
        ->where('warehouse_id', $warehouse->id)
        ->first()
        ->pivot
        ->actual_stock;
}

// ─── Entrée ─────────────────────────────────────────────────────────────────

it('crée un mouvement d\'entrée et incrémente le stock', function () {
    $mouvement = $this->service->create(
        type: StockMouvementType::ENTRY,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 10,
        user: $this->user,
    );

    expect($mouvement)->toBeInstanceOf(StockMouvement::class)
        ->and($mouvement->type)->toBe(StockMouvementType::ENTRY)
        ->and($mouvement->quantity)->toBe('10.000')
        ->and(getPivotStock($this->article, $this->warehouse))->toBe(10.0);

});

it('cumule les entrées successives', function () {
    $this->service->create(StockMouvementType::ENTRY, $this->article, $this->warehouse, 10, $this->user);
    $this->service->create(StockMouvementType::ENTRY, $this->article, $this->warehouse, 5, $this->user);

    expect(getPivotStock($this->article, $this->warehouse))->toBe(15.0);
});

it('crée automatiquement le pivot si l\'article n\'est pas rattaché au dépôt', function () {
    expect($this->article->warehouses()->count())->toBe(0);

    $this->service->create(StockMouvementType::ENTRY, $this->article, $this->warehouse, 10, $this->user);

    expect($this->article->warehouses()->count())->toBe(1)
        ->and(getPivotStock($this->article, $this->warehouse))->toBe(10.0);
});

// ─── Sortie ──────────────────────────────────────────────────────────────────

it('crée un mouvement de sortie et décrémente le stock', function () {
    attachWithStock($this->article, $this->warehouse, 20);

    $this->service->create(StockMouvementType::EXIT, $this->article, $this->warehouse, 5, $this->user);

    expect(getPivotStock($this->article, $this->warehouse))->toBe(15.0);
});

it('refuse une sortie si le stock est insuffisant', function () {
    attachWithStock($this->article, $this->warehouse, 5);

    expect(fn () => $this->service->create(
        StockMouvementType::EXIT, $this->article, $this->warehouse, 10, $this->user
    ))->toThrow(ValidationException::class);
});

it('refuse une quantité nulle ou négative', function () {
    expect(fn () => $this->service->create(
        StockMouvementType::ENTRY, $this->article, $this->warehouse, 0, $this->user
    ))->toThrow(ValidationException::class)
        ->and(fn () => $this->service->create(
            StockMouvementType::ENTRY, $this->article, $this->warehouse, -5, $this->user
        ))->toThrow(ValidationException::class);

});

// ─── Transfert ───────────────────────────────────────────────────────────────

it('transfère du stock entre deux dépôts', function () {
    $target = Warehouse::factory()->create();
    attachWithStock($this->article, $this->warehouse, 30);

    $this->service->create(
        type: StockMouvementType::TRANSFER,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 10,
        user: $this->user,
        options: ['target_warehouse_id' => $target->id],
    );

    expect(getPivotStock($this->article, $this->warehouse))->toBe(20.0)
        ->and(getPivotStock($this->article, $target))->toBe(10.0);
});

it('refuse un transfert sans dépôt destination', function () {
    attachWithStock($this->article, $this->warehouse, 20);

    expect(fn () => $this->service->create(
        StockMouvementType::TRANSFER, $this->article, $this->warehouse, 5, $this->user
    ))->toThrow(ValidationException::class);
});

it('refuse un transfert vers le même dépôt', function () {
    attachWithStock($this->article, $this->warehouse, 20);

    expect(fn () => $this->service->create(
        type: StockMouvementType::TRANSFER,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 5,
        user: $this->user,
        options: ['target_warehouse_id' => $this->warehouse->id],
    ))->toThrow(ValidationException::class);
});

it('refuse un transfert si stock insuffisant', function () {
    $target = Warehouse::factory()->create();
    attachWithStock($this->article, $this->warehouse, 5);

    expect(fn () => $this->service->create(
        type: StockMouvementType::TRANSFER,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 10,
        user: $this->user,
        options: ['target_warehouse_id' => $target->id],
    ))->toThrow(ValidationException::class);
});

// ─── Ajustement ──────────────────────────────────────────────────────────────

it('applique un ajustement de gain', function () {
    attachWithStock($this->article, $this->warehouse, 10);

    $this->service->create(
        type: StockMouvementType::ADJUSTEMENT,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 5,
        user: $this->user,
        options: ['adjustement_type' => AdjustementType::GAIN],
    );

    expect(getPivotStock($this->article, $this->warehouse))->toBe(15.0);
});

it('applique un ajustement de perte', function () {
    attachWithStock($this->article, $this->warehouse, 10);

    $this->service->create(
        type: StockMouvementType::ADJUSTEMENT,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 3,
        user: $this->user,
        options: ['adjustement_type' => AdjustementType::LOSS],
    );

    expect(getPivotStock($this->article, $this->warehouse))->toBe(7.0);
});

it('refuse un ajustement sans type', function () {
    attachWithStock($this->article, $this->warehouse, 10);

    expect(fn () => $this->service->create(
        StockMouvementType::ADJUSTEMENT, $this->article, $this->warehouse, 5, $this->user
    ))->toThrow(ValidationException::class);
});

it('refuse un ajustement de perte si stock insuffisant', function () {
    attachWithStock($this->article, $this->warehouse, 2);

    expect(fn () => $this->service->create(
        type: StockMouvementType::ADJUSTEMENT,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 5,
        user: $this->user,
        options: ['adjustement_type' => AdjustementType::LOSS],
    ))->toThrow(ValidationException::class);
});

// ─── Retour ──────────────────────────────────────────────────────────────────

it('crée un mouvement de retour et incrémente le stock', function () {
    attachWithStock($this->article, $this->warehouse, 5);

    $this->service->create(
        type: StockMouvementType::RETURN,
        article: $this->article,
        warehouse: $this->warehouse,
        quantity: 3,
        user: $this->user,
    );

    expect(getPivotStock($this->article, $this->warehouse))->toBe(8.0);
});
