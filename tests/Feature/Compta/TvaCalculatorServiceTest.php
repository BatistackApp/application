<?php

use App\Enums\Compta\CompteType;
use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\Ecriture;
use App\Models\Compta\Journal;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use App\Models\User;
use App\Services\Compta\TvaCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(TvaCalculatorService::class);
    actingAs(User::factory()->create());
});

// ─── TVA Collectée ────────────────────────────────────────────────────────

it('calcule la TVA collectée pour une période', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();

    // Créer comptes
    $compteTvaCollectee = PlanComptable::factory()->tvaCollectee()->create();
    $compteProduit = PlanComptable::factory()->produit()->create();
    $compteClient = PlanComptable::factory()->client()->create();

    // Créer écriture validée avec TVA collectée
    $ecriture = Ecriture::withoutEvents(function () use ($exercice, $journal) {
        return Ecriture::factory()
            ->withExercice($exercice)
            ->withJournal($journal)
            ->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });

    // Client débit 1200
    LigneEcriture::factory()
        ->debit(1200)
        ->withCompte($compteClient)
        ->create(['ecriture_id' => $ecriture->id]);

    // Produit crédit 1000
    LigneEcriture::factory()
        ->credit(1000)
        ->withCompte($compteProduit)
        ->create(['ecriture_id' => $ecriture->id]);

    // TVA collectée crédit 200
    LigneEcriture::factory()
        ->credit(200)
        ->withCompte($compteTvaCollectee)
        ->create(['ecriture_id' => $ecriture->id]);

    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $tvaCollectee = $this->service->getTvaCollectee($exercice->date_debut, $exercice->date_fin);

    expect($tvaCollectee['total'])->toBe(200.0)
        ->and($tvaCollectee['montant_20'])->toBe(200.0);
});

it('ignore les écritures en brouillon pour le calcul de TVA', function () {
    $exercice = createExerciceEnCours();
    $compteTva = PlanComptable::factory()->tvaCollectee()->create();

    // Écriture brouillon
    $ecriture = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()
            ->withExercice($exercice)
            ->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });

    LigneEcriture::factory()
        ->credit(200)
        ->withCompte($compteTva)
        ->create(['ecriture_id' => $ecriture->id]);

    $tvaCollectee = $this->service->getTvaCollectee($exercice->date_debut, $exercice->date_fin);

    expect($tvaCollectee['total'])->toBe(0.0);
});

// ─── TVA Déductible ───────────────────────────────────────────────────────

it('calcule la TVA déductible sur biens et services', function () {
    $exercice = createExerciceEnCours();
    $journal = Journal::factory()->achats()->create();

    $compteTvaDeductible = PlanComptable::factory()->tvaDeductible()->create();
    $compteCharge = PlanComptable::factory()->charge()->create();
    $compteFournisseur = PlanComptable::factory()->fournisseur()->create();

    $ecriture = Ecriture::withoutEvents(function () use ($exercice, $journal) {
        return Ecriture::factory()
            ->withExercice($exercice)
            ->withJournal($journal)
            ->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });

    // Charge débit 1000
    LigneEcriture::factory()->debit(1000)->withCompte($compteCharge)->create(['ecriture_id' => $ecriture->id]);

    // TVA déductible débit 200
    LigneEcriture::factory()->debit(200)->withCompte($compteTvaDeductible)->create(['ecriture_id' => $ecriture->id]);

    // Fournisseur crédit 1200
    LigneEcriture::factory()->credit(1200)->withCompte($compteFournisseur)->create(['ecriture_id' => $ecriture->id]);

    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $tvaDeductible = $this->service->getTvaDeductible($exercice->date_debut, $exercice->date_fin);

    expect($tvaDeductible['total'])->toBe(200.0)
        ->and($tvaDeductible['biens_services'])->toBe(200.0)
        ->and($tvaDeductible['immobilisations'])->toBe(0.0);
});

it('calcule la TVA déductible sur immobilisations', function () {
    $exercice = createExerciceEnCours();

    $compteTvaImmo = PlanComptable::factory()->create([
        'numero' => '445620',
        'libelle' => 'TVA déductible sur immobilisations',
        'type' => CompteType::CLASSE_4,
    ]);

    $ecriture = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()
            ->withExercice($exercice)
            ->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });

    LigneEcriture::factory()
        ->debit(5000)
        ->withCompte($compteTvaImmo)
        ->create(['ecriture_id' => $ecriture->id]);

    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $tvaDeductible = $this->service->getTvaDeductible($exercice->date_debut, $exercice->date_fin);

    expect($tvaDeductible['immobilisations'])->toBe(5000.0);
});

// ─── Solde TVA ────────────────────────────────────────────────────────────

it('calcule le solde de TVA (à payer)', function () {
    $exercice = createExerciceEnCours();

    $compteTvaCollectee = PlanComptable::factory()->tvaCollectee()->create();
    $compteTvaDeductible = PlanComptable::factory()->tvaDeductible()->create();

    // TVA collectée 1000
    $ecriture1 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });
    LigneEcriture::factory()->credit(1000)->withCompte($compteTvaCollectee)->create(['ecriture_id' => $ecriture1->id]);

    // TVA déductible 400
    $ecriture2 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });
    LigneEcriture::factory()->debit(400)->withCompte($compteTvaDeductible)->create(['ecriture_id' => $ecriture2->id]);

    DB::table('ecritures')->whereIn('id', [$ecriture1->id, $ecriture2->id])->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $solde = $this->service->getSoldeTva($exercice->date_debut, $exercice->date_fin);

    expect($solde)->toBe(600.0); // TVA à payer
});

it('calcule le solde de TVA (crédit)', function () {
    $exercice = createExerciceEnCours();

    $compteTvaCollectee = PlanComptable::factory()->tvaCollectee()->create();
    $compteTvaDeductible = PlanComptable::factory()->tvaDeductible()->create();

    // TVA collectée 500
    $ecriture1 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });
    LigneEcriture::factory()->credit(500)->withCompte($compteTvaCollectee)->create(['ecriture_id' => $ecriture1->id]);

    // TVA déductible 800
    $ecriture2 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON, 'date_ecriture' => now()]);
    });
    LigneEcriture::factory()->debit(800)->withCompte($compteTvaDeductible)->create(['ecriture_id' => $ecriture2->id]);

    DB::table('ecritures')->whereIn('id', [$ecriture1->id, $ecriture2->id])->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $solde = $this->service->getSoldeTva($exercice->date_debut, $exercice->date_fin);

    expect($solde)->toBe(-300.0); // Crédit de TVA
});
