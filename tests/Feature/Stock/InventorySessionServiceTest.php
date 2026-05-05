<?php

use App\Enums\Article\InventorySessionStatus;
use App\Enums\Article\StockMouvementType;
use App\Models\Article\Article;
use App\Models\Core\Warehouse;
use App\Models\Stock\InventorySession;
use App\Models\Stock\StockMouvement;
use App\Models\User;
use App\Services\Stock\InventorySessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);
use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(InventorySessionService::class);
    $this->user = User::factory()->create();
    $this->warehouse = Warehouse::factory()->create();

    actingAs($this->user);
});

// ─── Helpers ────────────────────────────────────────────────────────────────

function createArticleWithStock(Warehouse $warehouse, float $stock): Article
{
    $article = Article::factory()->create();

    $article->warehouses()->attach($warehouse->id, [
        'actual_stock' => $stock,
        'min_stock' => 0,
        'max_stock' => 0,
        'alert_stock' => 0,
        'bin_location' => null,
    ]);

    return $article;
}

// ─── Ouverture ───────────────────────────────────────────────────────────────

it('ouvre une session et génère les lignes automatiquement', function () {
    createArticleWithStock($this->warehouse, 10);
    createArticleWithStock($this->warehouse, 25);

    $session = $this->service->open($this->warehouse, $this->user);

    expect($session->status)->toBe(InventorySessionStatus::OPEN)
        ->and($session->warehouse_id)->toBe($this->warehouse->id)
        ->and($session->created_by)->toBe($this->user->id)
        ->and($session->reference)->toMatch('/^INV-\d{4}-\d{3}$/')
        ->and($session->lines()->count())->toBe(2);
});

it('génère les lignes avec les quantités théoriques correctes', function () {
    $article = createArticleWithStock($this->warehouse, 42.5);

    $session = $this->service->open($this->warehouse, $this->user);
    $line = $session->lines()->where('article_id', $article->id)->first();

    expect((float) $line->theoretical_quantity)->toBe(42.5)
        ->and($line->counted_quantity)->toBeNull();
});

it('n\'inclut pas les articles sans stock dans les lignes', function () {
    createArticleWithStock($this->warehouse, 10);
    createArticleWithStock($this->warehouse, 0);

    $session = $this->service->open($this->warehouse, $this->user);

    expect($session->lines()->count())->toBe(1);
});

it('refuse d\'ouvrir une session si une session active existe déjà pour ce dépôt', function () {
    $this->service->open($this->warehouse, $this->user);

    expect(fn () => $this->service->open($this->warehouse, $this->user))
        ->toThrow(ValidationException::class);
});

it('permet d\'ouvrir des sessions simultanées sur des dépôts différents', function () {
    $other = Warehouse::factory()->create();

    $session1 = $this->service->open($this->warehouse, $this->user);
    $session2 = $this->service->open($other, $this->user);

    expect($session1->id)->not->toBe($session2->id);
});

// ─── Workflow ─────────────────────────────────────────────────────────────────

it('passe une session de OPEN à COUNTING', function () {
    $session = InventorySession::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    $session = $this->service->startCounting($session);

    expect($session->status)->toBe(InventorySessionStatus::COUNTING);
});

it('refuse de démarrer le comptage si la session n\'est pas OPEN', function () {
    $session = InventorySession::factory()->counting()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    expect(fn () => $this->service->startCounting($session))
        ->toThrow(ValidationException::class);
});

it('ferme une session quand toutes les lignes sont comptées', function () {
    $article = createArticleWithStock($this->warehouse, 10);
    $session = $this->service->open($this->warehouse, $this->user);
    $this->service->startCounting($session);

    $session->lines()->update(['counted_quantity' => 10]);

    $session = $this->service->close($session->fresh());

    expect($session->status)->toBe(InventorySessionStatus::CLOSED)
        ->and($session->closed_at)->not->toBeNull();
});

it('refuse de fermer si des lignes ne sont pas comptées', function () {
    createArticleWithStock($this->warehouse, 10);
    $session = $this->service->open($this->warehouse, $this->user);
    $this->service->startCounting($session);

    expect(fn () => $this->service->close($session->fresh()))
        ->toThrow(ValidationException::class);
});

it('peut rouvrir une session CLOSED pour correction', function () {
    $session = InventorySession::factory()->closed()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    $session = $this->service->reopen($session);

    expect($session->status)->toBe(InventorySessionStatus::COUNTING)
        ->and($session->closed_at)->toBeNull();
});

// ─── Validation & ajustements ────────────────────────────────────────────────

it('valide la session et crée les ajustements de stock pour les écarts', function () {
    $article = createArticleWithStock($this->warehouse, 10);
    $session = $this->service->open($this->warehouse, $this->user);
    $this->service->startCounting($session);

    $session->lines()->update(['counted_quantity' => 7]);

    $session = $this->service->close($session->fresh());
    $session = $this->service->validate($session, $this->user);

    expect($session->status)->toBe(InventorySessionStatus::VALIDATED)
        ->and($session->validated_at)->not->toBeNull()
        ->and($session->validated_by)->toBe($this->user->id);

    $mouvement = StockMouvement::where('article_id', $article->id)
        ->where('type', StockMouvementType::ADJUSTEMENT)
        ->first();

    expect($mouvement)->not->toBeNull()
        ->and((float) $mouvement->quantity)->toBe(3.0);

    $stock = (float) $article->warehouses()
        ->where('warehouse_id', $this->warehouse->id)
        ->first()
        ->pivot
        ->actual_stock;

    expect($stock)->toBe(7.0);
});

it('ne crée pas d\'ajustement pour les lignes sans écart', function () {
    $article = createArticleWithStock($this->warehouse, 10);
    $session = $this->service->open($this->warehouse, $this->user);
    $this->service->startCounting($session);

    $session->lines()->update(['counted_quantity' => 10]);

    $session = $this->service->close($session->fresh());
    $this->service->validate($session, $this->user);

    $mouvements = StockMouvement::where('article_id', $article->id)
        ->where('type', StockMouvementType::ADJUSTEMENT)
        ->count();

    expect($mouvements)->toBe(0);
});

it('refuse de valider une session non fermée', function () {
    $session = InventorySession::factory()->counting()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    expect(fn () => $this->service->validate($session, $this->user))
        ->toThrow(ValidationException::class);
});

// ─── Annulation ──────────────────────────────────────────────────────────────

it('annule une session OPEN', function () {
    $session = InventorySession::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    $session = $this->service->cancel($session);

    expect($session->status)->toBe(InventorySessionStatus::CANCELLED);
});

it('annule une session COUNTING', function () {
    $session = InventorySession::factory()->counting()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    $session = $this->service->cancel($session);

    expect($session->status)->toBe(InventorySessionStatus::CANCELLED);
});

it('refuse d\'annuler une session VALIDATED', function () {
    $session = InventorySession::factory()->validated()->create([
        'warehouse_id' => $this->warehouse->id,
        'created_by' => $this->user->id,
    ]);

    expect(fn () => $this->service->cancel($session))
        ->toThrow(ValidationException::class);
});

// ─── Saisie des lignes ───────────────────────────────────────────────────────

it('sauvegarde la quantité comptée d\'une ligne', function () {
    $article = createArticleWithStock($this->warehouse, 10);
    $session = $this->service->open($this->warehouse, $this->user);
    $this->service->startCounting($session);

    $line = $session->lines()->where('article_id', $article->id)->first();
    $line = $this->service->saveLine($line, 8.5);

    expect((float) $line->counted_quantity)->toBe(8.5);
});

it('refuse de saisir une ligne si la session n\'est pas en COUNTING', function () {
    $article = createArticleWithStock($this->warehouse, 10);
    $session = $this->service->open($this->warehouse, $this->user);

    $line = $session->lines()->where('article_id', $article->id)->first();

    expect(fn () => $this->service->saveLine($line, 8.5))
        ->toThrow(ValidationException::class);
});

// ─── Référence unique ────────────────────────────────────────────────────────

it('génère des références uniques pour chaque session', function () {
    $w1 = Warehouse::factory()->create();
    $w2 = Warehouse::factory()->create();
    $w3 = Warehouse::factory()->create();

    $s1 = $this->service->open($w1, $this->user);
    $s2 = $this->service->open($w2, $this->user);
    $s3 = $this->service->open($w3, $this->user);

    expect($s1->reference)->not->toBe($s2->reference)
        ->and($s2->reference)->not->toBe($s3->reference);
});
