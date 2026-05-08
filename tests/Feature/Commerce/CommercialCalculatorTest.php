<?php

use App\Enums\Commerce\TauxTva;
use App\Models\Commerce\CommercialDocument;
use App\Models\Commerce\CommercialDocumentLine;
use App\Models\Commerce\Paiement;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Commerce\CommercialCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->calculator = app(CommercialCalculator::class);
    actingAs(User::factory()->create());

    $this->client = Tiers::factory()->customer()->create();
});

// ─── Calcul ligne ─────────────────────────────────────────────────────────────

it('calcule les totaux d\'une ligne sans remise', function () {
    $document = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    $line = CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 10,
        'prix_unitaire_ht' => 100,
        'taux_tva' => 20,
        'remise_pct' => 0,
        'remise_montant' => 0,
    ]);

    $totaux = $this->calculator->calculateLineTotals($line);

    expect($totaux['montant_brut'])->toBe(1000.0)
        ->and($totaux['remise_totale'])->toBe(0.0)
        ->and($totaux['total_ht'])->toBe(1000.0)
        ->and($totaux['total_tva'])->toBe(200.0)
        ->and($totaux['total_ttc'])->toBe(1200.0);
});

it('applique la remise en pourcentage sur une ligne', function () {
    $document = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    $line = CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 10,
        'prix_unitaire_ht' => 100,
        'taux_tva' => 20,
        'remise_pct' => 10,
        'remise_montant' => 0,
    ]);

    $totaux = $this->calculator->calculateLineTotals($line);

    expect($totaux['montant_brut'])->toBe(1000.0)
        ->and($totaux['remise_totale'])->toBe(100.0)
        ->and($totaux['total_ht'])->toBe(900.0)
        ->and($totaux['total_tva'])->toBe(180.0)
        ->and($totaux['total_ttc'])->toBe(1080.0);
});

it('applique la remise en montant sur une ligne', function () {
    $document = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    $line = CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 10,
        'prix_unitaire_ht' => 100,
        'taux_tva' => 20,
        'remise_pct' => 0,
        'remise_montant' => 50,
    ]);

    $totaux = $this->calculator->calculateLineTotals($line);

    expect($totaux['remise_totale'])->toBe(50.0)
        ->and($totaux['total_ht'])->toBe(950.0)
        ->and($totaux['total_tva'])->toBe(190.0)
        ->and($totaux['total_ttc'])->toBe(1140.0);
});

it('calcule correctement avec TVA réduite', function () {
    $document = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    $line = CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 1,
        'prix_unitaire_ht' => 1000,
        'taux_tva' => TauxTva::TVA_10->getRate(),
        'remise_pct' => 0,
        'remise_montant' => 0,
    ]);

    $totaux = $this->calculator->calculateLineTotals($line);

    expect($totaux['total_ht'])->toBe(1000.0)
        ->and($totaux['total_tva'])->toBe(100.0)
        ->and($totaux['total_ttc'])->toBe(1100.0);
});

// ─── Calcul document ──────────────────────────────────────────────────────────

it('calcule les totaux d\'un document multi-lignes', function () {
    $document = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 10,
        'prix_unitaire_ht' => 100,
        'taux_tva' => 20,
    ]);

    CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 5,
        'prix_unitaire_ht' => 200,
        'taux_tva' => 10,
    ]);

    $totaux = $this->calculator->calculateDocumentTotals($document->fresh());

    expect($totaux['total_ht'])->toBe(2000.0)  // 1000 + 1000
        ->and($totaux['par_taux_tva'])->toHaveCount(2);
});

it('applique la remise globale en pourcentage', function () {
    $document = CommercialDocument::factory()->devis()->create([
        'client_id' => $this->client->id,
        'remise_globale_pct' => 10,
    ]);

    CommercialDocumentLine::factory()->create([
        'commercial_document_id' => $document->id,
        'quantite' => 10,
        'prix_unitaire_ht' => 100,
        'taux_tva' => 20,
    ]);

    $totaux = $this->calculator->calculateDocumentTotals($document->fresh());

    expect($totaux['total_ht'])->toBe(900.0)  // 1000 - 10%
        ->and($totaux['total_ttc'])->toBe(1080.0);
});

it('retourne des zéros si le document n\'a pas de lignes', function () {
    $document = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    $totaux = $this->calculator->calculateDocumentTotals($document);

    expect($totaux['total_ht'])->toBe(0)
        ->and($totaux['total_tva'])->toBe(0)
        ->and($totaux['total_ttc'])->toBe(0);
});

// ─── Calcul solde ─────────────────────────────────────────────────────────────

it('calcule le solde d\'une facture sans paiement', function () {
    $facture = CommercialDocument::factory()->facture()->create([
        'client_id' => $this->client->id,
        'total_ttc' => 1200,
    ]);

    $solde = $this->calculator->calculateSolde($facture);

    expect($solde)->toBe(1200.0);
});

it('calcule le solde d\'une facture partiellement payée', function () {
    $facture = CommercialDocument::factory()->facture()->create([
        'client_id' => $this->client->id,
        'total_ttc' => 1200,
    ]);

    Paiement::factory()->create([
        'facture_id' => $facture->id,
        'montant' => 600,
    ]);

    $solde = $this->calculator->calculateSolde($facture->fresh());

    expect($solde)->toBe(600.0);
});

it('retourne 0 pour une facture entièrement payée', function () {
    $facture = CommercialDocument::factory()->facture()->create([
        'client_id' => $this->client->id,
        'total_ttc' => 1200,
    ]);

    Paiement::factory()->create([
        'facture_id' => $facture->id,
        'montant' => 1200,
    ]);

    $solde = $this->calculator->calculateSolde($facture->fresh());

    expect($solde)->toBe(0.0);
});

it('retourne 0 pour un document non-facture', function () {
    $devis = CommercialDocument::factory()->devis()->create(['client_id' => $this->client->id]);

    $solde = $this->calculator->calculateSolde($devis);

    expect($solde)->toBe(0.0);
});
