<?php

use App\Models\Compta\Ecriture;
use App\Models\Compta\LigneEcriture;
use App\Models\Compta\PlanComptable;
use App\Models\User;
use App\Services\Compta\BalanceService;
use App\Enums\Compta\EcritureStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(BalanceService::class);
    actingAs(User::factory()->create());
});

// ─── Balance Générale ─────────────────────────────────────────────────────

it('génère la balance générale', function () {
    $exercice = createExerciceEnCours();

    $compte1 = PlanComptable::factory()->client()->create();
    $compte2 = PlanComptable::factory()->produit()->create();

    // Prevent observer from firing validation during creation
    $ecriture = Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON]);

    LigneEcriture::factory()->debit(1500)->withCompte($compte1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(500)->withCompte($compte1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(1000)->withCompte($compte2)->create(['ecriture_id' => $ecriture->id]);

    // Force validation direct with DB to bypass Observer validation issues if necessary
    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $balance = $this->service->genererBalanceGenerale($exercice);

    expect($balance)->toHaveCount(2)
        ->and($balance->first()['numero'])->toBe($compte1->numero)
        ->and($balance->first()['total_debit'])->toBe(1500.0)
        ->and($balance->first()['total_credit'])->toBe(500.0)
        ->and($balance->first()['solde_debit'])->toBe(1000.0);
});

it('trie la balance par numéro de compte', function () {
    $exercice = createExerciceEnCours();

    $compte1 = PlanComptable::factory()->create(['numero' => '512000']);
    $compte2 = PlanComptable::factory()->create(['numero' => '411000']);

    $ecriture = Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON]);

    LigneEcriture::factory()->debit(100)->withCompte($compte1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(100)->withCompte($compte2)->create(['ecriture_id' => $ecriture->id]);

    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $balance = $this->service->genererBalanceGenerale($exercice);

    expect($balance->first()['numero'])->toBe('411000')
        ->and($balance->last()['numero'])->toBe('512000');
});

// ─── Balance Auxiliaire ───────────────────────────────────────────────────

it('génère la balance auxiliaire clients', function () {
    $exercice = createExerciceEnCours();

    $client1 = PlanComptable::factory()->client()->create();
    $client2 = PlanComptable::factory()->client()->create();
    $fournisseur = PlanComptable::factory()->fournisseur()->create();

    $ecriture = Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON]);

    LigneEcriture::factory()->debit(1000)->withCompte($client1)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->debit(500)->withCompte($client2)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(1500)->withCompte($fournisseur)->create(['ecriture_id' => $ecriture->id]);

    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $balance = $this->service->genererBalanceAuxiliaire($exercice, '411');

    expect($balance)->toHaveCount(2)
        ->and($balance->first()['solde'])->toBe(1000.0);
});

it('exclut les comptes avec un solde nul de la balance auxiliaire', function () {
    $exercice = createExerciceEnCours();

    $client = PlanComptable::factory()->client()->create();

    $ecriture = Ecriture::factory()->withExercice($exercice)->createQuietly(['status' => EcritureStatus::BROUILLON]);

    // Solde nul
    LigneEcriture::factory()->debit(1000)->withCompte($client)->create(['ecriture_id' => $ecriture->id]);
    LigneEcriture::factory()->credit(1000)->withCompte($client)->create(['ecriture_id' => $ecriture->id]);

    DB::table('ecritures')->where('id', $ecriture->id)->update([
        'status' => EcritureStatus::VALIDE->value,
        'validated_at' => now(),
    ]);

    $balance = $this->service->genererBalanceAuxiliaire($exercice, '411');

    expect($balance)->toBeEmpty();
});

// ─── Vérification Équilibre ───────────────────────────────────────────────

it('vérifie qu\'une balance est équilibrée', function () {
    $balance = collect([
        ['total_debit' => 1000, 'total_credit' => 500, 'solde_debit' => 500, 'solde_credit' => 0],
        ['total_debit' => 200, 'total_credit' => 700, 'solde_debit' => 0, 'solde_credit' => 500],
    ]);

    $verification = $this->service->verifierEquilibre($balance);

    expect($verification['equilibree'])->toBeTrue()
        ->and($verification['total_debit'])->toBe(1200.0)
        ->and($verification['total_credit'])->toBe(1200.0)
        ->and($verification['total_solde_debit'])->toBe(500.0)
        ->and($verification['total_solde_credit'])->toBe(500.0);
});

it('détecte une balance déséquilibrée', function () {
    $balance = collect([
        ['total_debit' => 1000, 'total_credit' => 500, 'solde_debit' => 500, 'solde_credit' => 0],
        ['total_debit' => 200, 'total_credit' => 600, 'solde_debit' => 0, 'solde_credit' => 400],
    ]);

    $verification = $this->service->verifierEquilibre($balance);

    expect($verification['equilibree'])->toBeFalse()
        ->and($verification['ecart_mouvements'])->toBe(100.0)
        ->and($verification['ecart_soldes'])->toBe(100.0);
});
