<?php

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierBudgetLine;
use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\CommercialDocumentLine;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Commerce\CommercialDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CommercialDocumentService::class);
    actingAs(User::factory()->create());
    $this->client = Tiers::factory()->customer()->create();
});

// ─── Création ─────────────────────────────────────────────────────────────────

it('crée un devis avec une référence auto', function () {
    $devis = $this->service->create(DocumentType::DEVIS, $this->client);

    expect($devis->type)->toBe(DocumentType::DEVIS)
        ->and($devis->status)->toBe(DocumentStatus::DRAFT)
        ->and($devis->reference)->toStartWith('DEV-'.now()->year.'-')
        ->and($devis->client_id)->toBe($this->client->id)
        ->and($devis->date_validite)->not->toBeNull();
});

it('crée une facture avec une date d\'échéance par défaut à 30 jours', function () {
    $facture = $this->service->create(DocumentType::FACTURE, $this->client);

    expect($facture->type)->toBe(DocumentType::FACTURE)
        ->and($facture->date_echeance)->not->toBeNull()
        ->and($facture->date_echeance->diffInDays(now()))->toBeLessThanOrEqual(30);
});

it('génère des références séquentielles par type', function () {
    $devis1 = $this->service->create(DocumentType::DEVIS, $this->client);
    $devis2 = $this->service->create(DocumentType::DEVIS, $this->client);
    $facture = $this->service->create(DocumentType::FACTURE, $this->client);

    $year = now()->year;

    expect($devis1->reference)->toBe("DEV-{$year}-001")
        ->and($devis2->reference)->toBe("DEV-{$year}-002")
        ->and($facture->reference)->toBe("FAC-{$year}-001");
});

// ─── Validation ───────────────────────────────────────────────────────────────

it('valide un document DRAFT avec des lignes', function () {
    $devis = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $devis->id,
    ]);

    $devis = $this->service->validate($devis);

    expect($devis->status)->toBe(DocumentStatus::SENT);
});

it('refuse de valider un document sans lignes', function () {
    $devis = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    expect(fn () => $this->service->validate($devis))
        ->toThrow(ValidationException::class);
});

it('refuse de valider un document déjà envoyé', function () {
    $devis = CommercialDocument::factory()->devis()->sent()->create(['client_id' => $this->client->id]);

    CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $devis->id,
    ]);

    expect(fn () => $this->service->validate($devis))
        ->toThrow(ValidationException::class);
});

// ─── Workflow ─────────────────────────────────────────────────────────────────

it('accepte un document envoyé', function () {
    $devis = CommercialDocument::factory()->devis()->sent()->create(['client_id' => $this->client->id]);

    $devis = $this->service->accept($devis);

    expect($devis->status)->toBe(DocumentStatus::ACCEPTED);
});

it('refuse un devis avec motif', function () {
    $devis = CommercialDocument::factory()->devis()->sent()->create(['client_id' => $this->client->id]);

    $devis = $this->service->refuse($devis, 'Prix trop élevé');

    expect($devis->status)->toBe(DocumentStatus::REFUSED)
        ->and($devis->notes)->toContain('Prix trop élevé');
});

it('annule un document', function () {
    $devis = CommercialDocument::factory()->devis()->sent()->create(['client_id' => $this->client->id]);

    $devis = $this->service->cancel($devis);

    expect($devis->status)->toBe(DocumentStatus::CANCELLED);
});

// ─── Conversion ───────────────────────────────────────────────────────────────

it('convertit un devis accepté en bon de commande', function () {
    $devis = CommercialDocument::factory()->devis()->accepted()->create(['client_id' => $this->client->id]);

    CommercialDocumentLine::factory()->count(3)->create([
        'commercial_document_id' => $devis->id,
    ]);

    $bdc = $this->service->convert($devis, DocumentType::BON_COMMANDE);

    expect($bdc->type)->toBe(DocumentType::BON_COMMANDE)
        ->and($bdc->status)->toBe(DocumentStatus::DRAFT)
        ->and($bdc->client_id)->toBe($this->client->id)
        ->and($bdc->parent_document_id)->toBe($devis->id)
        ->and($bdc->lines()->count())->toBe(3);
});

it('convertit un BL en facture en reprenant les lignes', function () {
    $bl = CommercialDocument::factory()->bonLivraison()->delivered()->create(['client_id' => $this->client->id]);

    CommercialDocumentLine::factory()->count(2)->create([
        'commercial_document_id' => $bl->id,
    ]);

    $facture = $this->service->convert($bl, DocumentType::FACTURE);

    expect($facture->type)->toBe(DocumentType::FACTURE)
        ->and($facture->lines()->count())->toBe(2);
});

it('refuse une conversion non autorisée', function () {
    $facture = CommercialDocument::factory()->facture()->sent()->create(['client_id' => $this->client->id]);

    expect(fn () => $this->service->convert($facture, DocumentType::DEVIS))
        ->toThrow(ValidationException::class);
});

// ─── Facturation par avancement ───────────────────────────────────────────────

it('crée une facture d\'avancement chantier', function () {
    $chantier = Chantier::factory()->active()->create([
        'client_id' => $this->client->id,
    ]);

    // cout_total est une colonne virtuelle = quantite × cout_unitaire
    // On passe uniquement quantite + cout_unitaire → cout_total = 100 × 500 = 50 000 €
    ChantierBudgetLine::factory()->create([
        'chantier_id' => $chantier->id,
        'quantite' => 100,
        'cout_unitaire' => 500,
    ]);

    $facture = $this->service->createFactureAvancement($chantier, 30);

    expect($facture->type)->toBe(DocumentType::FACTURE)
        ->and($facture->chantier_id)->toBe($chantier->id)
        ->and($facture->avancement_pct)->toBe('30.00')
        ->and($facture->lines()->count())->toBe(1);
});

it('crée une facture d\'acompte à partir du chantier', function () {
    $chantier = \App\Models\Chantier\Chantier::factory()->active()->create([
        'client_id' => $this->client->id,
    ]);

    // Idem — on ne touche pas à cout_total
    \App\Models\Chantier\ChantierBudgetLine::factory()->create([
        'chantier_id'   => $chantier->id,
        'quantite'      => 100,
        'cout_unitaire' => 500,
    ]);

    $acompte = $this->service->createFactureAvancement($chantier, 30, isAcompte: true);

    expect($acompte->type)->toBe(DocumentType::FACTURE_ACOMPTE)
        ->and($acompte->avancement_pct)->toBe('30.00');
});
