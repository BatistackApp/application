<?php

use App\Enums\Compta\CompteSens;
use App\Enums\Compta\EcritureStatus;
use App\Models\Compta\Ecriture;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use App\Models\User;
use App\Services\Compta\GrandLivreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(GrandLivreService::class);
    actingAs(User::factory()->create());
});

// ─── Génération Grand Livre ───────────────────────────────────────────────

it('génère le grand livre pour un exercice', function () {
    $exercice = createExerciceEnCours();
    $compte1 = PlanComptable::factory()->client()->create();
    $compte2 = PlanComptable::factory()->produit()->create();

    // Créer écritures en brouillon d'abord, en ignorant les événements qui valident l'écriture à la sauvegarde
    $ecriture = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->create(['status' => EcritureStatus::BROUILLON]);
    });

    LigneEcriture::factory()->debit(1000)->withCompte($compte1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(1000)->withCompte($compte2)->create(['ecriture_id' => $ecriture->id]);

    // Valider manuellement pour bypasser le cycle d'événements problématique des usines
    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $grandLivre = $this->service->generer($exercice);

    expect($grandLivre)->toHaveCount(2)
        ->and($grandLivre->first()['compte']->id)->toBe($compte1->id)
        ->and($grandLivre->first()['mouvements'])->toHaveCount(1);
});

it('ignore les écritures non validées dans le grand livre', function () {
    $exercice = createExerciceEnCours();
    $compte = PlanComptable::factory()->create();

    // Écriture brouillon
    $ecriture = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->create([
            'status' => EcritureStatus::BROUILLON,
        ]);
    });

    LigneEcriture::factory()->debit(1000)->withCompte($compte)->create(['ecriture_id' => $ecriture->id]);

    $grandLivre = $this->service->generer($exercice);

    expect($grandLivre)->toBeEmpty();
});

it('filtre le grand livre par période', function () {
    $exercice = createExerciceEnCours();
    $compte = PlanComptable::factory()->create();

    // Écriture en janvier
    $ecriture1 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->create([
            'date_ecriture' => now()->startOfYear()->addDays(5),
            'status' => EcritureStatus::BROUILLON,
        ]);
    });
    LigneEcriture::factory()->debit(1000)->withCompte($compte)->create(['ecriture_id' => $ecriture1->id]);

    // Écriture en mars
    $ecriture2 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->create([
            'date_ecriture' => now()->startOfYear()->addMonths(2)->addDays(5),
            'status' => EcritureStatus::BROUILLON,
        ]);
    });
    LigneEcriture::factory()->debit(500)->withCompte($compte)->create(['ecriture_id' => $ecriture2->id]);

    // Valider manuellement
    DB::table('ecritures')->whereIn('id', [$ecriture1->id, $ecriture2->id])->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    // Filtrer uniquement janvier
    $grandLivre = $this->service->generer(
        $exercice,
        now()->startOfYear(),
        now()->startOfYear()->endOfMonth()
    );

    expect($grandLivre->first()['mouvements'])->toHaveCount(1)
        ->and($grandLivre->first()['total_debit'])->toBe(1000.0);
});

// ─── Calcul Soldes ────────────────────────────────────────────────────────

it('calcule le solde débiteur d\'un compte client', function () {
    $compte = PlanComptable::factory()->client()->create();

    $mouvements = collect([
        (object) ['sens' => CompteSens::DEBIT, 'montant' => 1500],
        (object) ['sens' => CompteSens::CREDIT, 'montant' => 500],
    ]);

    $solde = $this->service->calculerSolde($compte, $mouvements);

    expect($solde)->toBe(1000.0); // Débiteur
});

it('calcule le solde créditeur d\'un compte produit', function () {
    $compte = PlanComptable::factory()->produit()->create();

    $mouvements = collect([
        (object) ['sens' => CompteSens::DEBIT, 'montant' => 200],
        (object) ['sens' => CompteSens::CREDIT, 'montant' => 1000],
    ]);

    $solde = $this->service->calculerSolde($compte, $mouvements);

    expect($solde)->toBe(800.0); // Créditeur
});

it('retourne un solde zéro pour un compte équilibré', function () {
    $compte = PlanComptable::factory()->create();

    $mouvements = collect([
        (object) ['sens' => CompteSens::DEBIT, 'montant' => 1000],
        (object) ['sens' => CompteSens::CREDIT, 'montant' => 1000],
    ]);

    $solde = $this->service->calculerSolde($compte, $mouvements);

    expect($solde)->toBe(0.0);
});

// ─── Solde à date ─────────────────────────────────────────────────────────

it('calcule le solde d\'un compte à une date donnée', function () {
    $exercice = createExerciceEnCours();
    $compte = PlanComptable::factory()->client()->create();

    // Mouvement en janvier
    $ecriture1 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->create([
            'date_ecriture' => now()->startOfYear()->addDays(5),
            'status' => EcritureStatus::BROUILLON,
        ]);
    });
    LigneEcriture::factory()->debit(1000)->withCompte($compte)->create(['ecriture_id' => $ecriture1->id]);

    // Mouvement en février
    $ecriture2 = Ecriture::withoutEvents(function () use ($exercice) {
        return Ecriture::factory()->withExercice($exercice)->create([
            'date_ecriture' => now()->startOfYear()->addMonth()->addDays(5),
            'status' => EcritureStatus::BROUILLON,
        ]);
    });
    LigneEcriture::factory()->credit(300)->withCompte($compte)->create(['ecriture_id' => $ecriture2->id]);

    // Valider manuellement
    DB::table('ecritures')->whereIn('id', [$ecriture1->id, $ecriture2->id])->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    // Solde au 31 janvier (avant le crédit de février)
    $soldeJanvier = $this->service->getSoldeCompteAuDate($compte, now()->startOfYear()->endOfMonth());

    expect($soldeJanvier)->toBe(1000.0);

    // Solde au 28 février (après le crédit)
    $soldeFevrier = $this->service->getSoldeCompteAuDate($compte, now()->startOfYear()->addMonth()->endOfMonth());

    expect($soldeFevrier)->toBe(700.0);
});
