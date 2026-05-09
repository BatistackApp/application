<?php

use App\Enums\Compta\CompteSens;
use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\Ecriture;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use App\Models\User;
use App\Services\Compta\EcritureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(EcritureService::class);
    actingAs(User::factory()->create());
});

// ─── Création ─────────────────────────────────────────────────────────────

it('crée une écriture équilibrée en brouillon', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();
    $comptes = createComptesStandard();

    $lignes = [
        [
            'compte_id' => $comptes['client']->id,
            'sens' => CompteSens::DEBIT->value,
            'montant' => 1200,
            'libelle' => 'Vente client',
        ],
        [
            'compte_id' => $comptes['produit']->id,
            'sens' => CompteSens::CREDIT->value,
            'montant' => 1000,
            'libelle' => 'Produit HT',
        ],
        [
            'compte_id' => $comptes['tva_collectee']->id,
            'sens' => CompteSens::CREDIT->value,
            'montant' => 200,
            'libelle' => 'TVA 20%',
        ],
    ];

    $ecriture = $this->service->create($journal, now(), 'Facture client', $lignes);

    expect($ecriture->status)->toBe(EcritureStatus::BROUILLON)
        ->and($ecriture->lignes()->count())->toBe(3)
        ->and($ecriture->total_debit)->toBe(1200.0)
        ->and($ecriture->total_credit)->toBe(1200.0)
        ->and($ecriture->isEquilibree())->toBeTrue();
});

it('génère un numéro de pièce unique', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();
    $comptes = createComptesStandard();

    $lignes = [
        ['compte_id' => $comptes['client']->id, 'sens' => CompteSens::DEBIT->value, 'montant' => 100],
        ['compte_id' => $comptes['produit']->id, 'sens' => CompteSens::CREDIT->value, 'montant' => 100],
    ];

    $ecriture1 = $this->service->create($journal, now(), 'Test 1', $lignes);
    $ecriture2 = $this->service->create($journal, now(), 'Test 2', $lignes);

    expect($ecriture1->numero_piece)->not->toBe($ecriture2->numero_piece)
        ->and($ecriture1->numero_piece)->toContain($journal->code);
});

it('refuse de créer une écriture hors exercice actif', function () {
    $journal = createJournalVentes();
    $comptes = createComptesStandard();

    $lignes = [
        ['compte_id' => $comptes['client']->id, 'sens' => CompteSens::DEBIT->value, 'montant' => 100],
        ['compte_id' => $comptes['produit']->id, 'sens' => CompteSens::CREDIT->value, 'montant' => 100],
    ];

    $dateHorsExercice = now()->addYears(10);

    expect(fn () => $this->service->create($journal, $dateHorsExercice, 'Test', $lignes))
        ->toThrow(ValidationException::class);
});

// ─── Validation ───────────────────────────────────────────────────────────

it('valide une écriture équilibrée', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();
    $compte1 = PlanComptable::factory()->create(['numero' => '601000']);
    $compte2 = PlanComptable::factory()->create(['numero' => '401000']);

    $ecriture = Ecriture::factory()->create([
        'exercice_comptable_id' => $exercice->id,
        'journal_id' => $journal->id,
        'status' => EcritureStatus::BROUILLON,
        'date_ecriture' => now(),
    ]);

    LigneEcriture::factory()->debit(1000)->withCompte($compte1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(1000)->withCompte($compte2)->create(['ecriture_id' => $ecriture->id]);

    $ecriture = $this->service->valider($ecriture->fresh());

    expect($ecriture->status)->toBe(EcritureStatus::VALIDE)
        ->and($ecriture->validated_at)->not->toBeNull()
        ->and($ecriture->validated_by)->toBe(auth()->id());
});

it('refuse de valider une écriture déséquilibrée', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();
    $compte1 = PlanComptable::factory()->create(['numero' => '602000']);
    $compte2 = PlanComptable::factory()->create(['numero' => '402000']);

    $ecriture = Ecriture::factory()->create([
        'exercice_comptable_id' => $exercice->id,
        'journal_id' => $journal->id,
        'status' => EcritureStatus::BROUILLON,
        'date_ecriture' => now(),
    ]);

    LigneEcriture::factory()->debit(1000)->withCompte($compte1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(900)->withCompte($compte2)->create(['ecriture_id' => $ecriture->id]);

    expect(fn () => $this->service->valider($ecriture->fresh()))
        ->toThrow(ValidationException::class);
});

it('refuse de valider une écriture avec moins de 2 lignes', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();
    $compte = PlanComptable::factory()->create(['numero' => '603000']);

    $ecriture = Ecriture::factory()->create([
        'exercice_comptable_id' => $exercice->id,
        'journal_id' => $journal->id,
        'status' => EcritureStatus::BROUILLON,
        'date_ecriture' => now(),
    ]);

    LigneEcriture::factory()->debit(1000)->withCompte($compte)->create(['ecriture_id' => $ecriture->id]);

    expect(fn () => $this->service->valider($ecriture->fresh()))
        ->toThrow(ValidationException::class);
});

// ─── Extourne ─────────────────────────────────────────────────────────────

it('extourne une écriture validée', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();
    $compte1 = PlanComptable::factory()->create(['numero' => '604000']);
    $compte2 = PlanComptable::factory()->create(['numero' => '404000']);

    $ecriture = Ecriture::factory()->create([
        'exercice_comptable_id' => $exercice->id,
        'journal_id' => $journal->id,
        'status' => EcritureStatus::BROUILLON,
        'date_ecriture' => now(),
        'validated_at' => now(),
        'validated_by' => auth()->id(),
    ]);

    LigneEcriture::factory()
        ->debit(1000)
        ->withCompte($compte1)
        ->create(['ecriture_id' => $ecriture->id]);

    LigneEcriture::factory()
        ->credit(1000)
        ->withCompte($compte2)
        ->create(['ecriture_id' => $ecriture->id]);

    $this->service->valider($ecriture->fresh());

    $ecritureExtourne = $this->service->extourner($ecriture->fresh(), now());

    expect($ecriture->fresh()->status)->toBe(EcritureStatus::EXTOURNE)
        ->and($ecritureExtourne->status)->toBe(EcritureStatus::VALIDE)
        ->and($ecritureExtourne->lignes()->count())->toBe(2);

    // Vérifier inversion des sens
    $ligneOriginale = $ecriture->lignes->first();
    $ligneExtourne = $ecritureExtourne->lignes->where('compte_id', $ligneOriginale->compte_id)->first();

    expect($ligneExtourne->sens)->toBe($ligneOriginale->sens->opposite());
});

it('refuse d\'extourner une écriture brouillon', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();

    $ecriture = Ecriture::factory()->create([
        'exercice_comptable_id' => $exercice->id,
        'journal_id' => $journal->id,
        'status' => EcritureStatus::BROUILLON,
        'date_ecriture' => now(),
    ]);

    expect(fn () => $this->service->extourner($ecriture, now()))
        ->toThrow(ValidationException::class);
});

// ─── Suppression ──────────────────────────────────────────────────────────

it('supprime une écriture brouillon', function () {
    $exercice = createExerciceEnCours();
    $journal = createJournalVentes();

    $ecriture = Ecriture::factory()->create([
        'exercice_comptable_id' => $exercice->id,
        'journal_id' => $journal->id,
        'status' => EcritureStatus::BROUILLON,
        'date_ecriture' => now(),
    ]);

    $this->service->delete($ecriture);

    expect(Ecriture::find($ecriture->id))->toBeNull();
});
