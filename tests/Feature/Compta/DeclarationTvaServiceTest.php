<?php

use App\Enums\Compta\RegimeTva;
use App\Models\Compta\DeclarationTva;
use App\Models\Compta\ExerciceComptable;
use App\Models\User;
use App\Services\Compta\DeclarationTvaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->service = app(DeclarationTvaService::class);
    actingAs(User::factory()->create());
});

// ─── Génération ───────────────────────────────────────────────────────────

it('génère une déclaration de TVA mensuelle', function () {
    $exercice = createExerciceEnCours();

    $declaration = $this->service->generer(
        $exercice,
        RegimeTva::REEL_NORMAL,
        now()->startOfMonth(),
        now()->endOfMonth()
    );

    expect($declaration)->toBeInstanceOf(DeclarationTva::class)
        ->and($declaration->regime)->toBe(RegimeTva::REEL_NORMAL)
        ->and($declaration->periode)->toBe(now()->format('Y-m'))
        ->and((bool) $declaration->validee)->toBeFalse();
});

it('génère une déclaration de TVA trimestrielle', function () {
    $exercice = createExerciceEnCours();

    $dateDebut = now()->startOfYear();
    $dateFin = now()->startOfYear()->addMonths(2)->endOfMonth();

    $declaration = $this->service->generer(
        $exercice,
        RegimeTva::REEL_SIMPLIFIE,
        $dateDebut,
        $dateFin
    );

    expect($declaration->regime)->toBe(RegimeTva::REEL_SIMPLIFIE)
        ->and($declaration->periode)->toBe(now()->year.'-T1');
});

it('refuse de créer une déclaration en doublon', function () {
    $exercice = createExerciceEnCours();

    $this->service->generer(
        $exercice,
        RegimeTva::REEL_NORMAL,
        now()->startOfMonth(),
        now()->endOfMonth()
    );

    expect(fn () => $this->service->generer(
        $exercice,
        RegimeTva::REEL_NORMAL,
        now()->startOfMonth(),
        now()->endOfMonth()
    ))->toThrow(ValidationException::class);
});

it('calcule correctement la TVA due avec crédit précédent', function () {
    $exercice = createExerciceEnCours();

    DeclarationTva::factory()
        ->validee()
        ->avecCredit()
        ->create([
            'exercice_comptable_id' => $exercice->id,
            'periode' => now()->subMonth()->format('Y-m'),
            'date_debut' => now()->subMonth()->startOfMonth(),
            'date_fin' => now()->subMonth()->endOfMonth(),
            'tva_nette' => -500.0,
        ]);

    $declaration = $this->service->generer(
        $exercice,
        RegimeTva::REEL_NORMAL,
        now()->startOfMonth(),
        now()->endOfMonth()
    );

    expect((float) $declaration->credit_periode_precedente)->toBe(500.0);
});

// ─── Validation ───────────────────────────────────────────────────────────

it('valide une déclaration de TVA', function () {
    $declaration = DeclarationTva::factory()->create([
        'total_tva_collectee' => 1000,
        'validee' => false,
    ]);

    $declaration = $this->service->valider($declaration);

    expect((bool) $declaration->validee)->toBeTrue()
        ->and($declaration->validee_at)->not->toBeNull()
        ->and($declaration->validee_by)->toBe(auth()->id());
});

it('refuse de valider une déclaration sans TVA collectée', function () {
    $declaration = DeclarationTva::factory()->create([
        'total_tva_collectee' => 0,
        'validee' => false,
    ]);

    expect(fn () => $this->service->valider($declaration))
        ->toThrow(ValidationException::class);
});

// ─── Transmission ─────────────────────────────────────────────────────────

it('marque une déclaration comme transmise', function () {
    $declaration = DeclarationTva::factory()->validee()->create();

    $declaration = $this->service->marquerTransmise($declaration);

    expect((bool) $declaration->transmise)->toBeTrue()
        ->and($declaration->transmise_at)->not->toBeNull();
});

it('refuse de transmettre une déclaration non validée', function () {
    $declaration = DeclarationTva::factory()->create(['validee' => false]);

    expect(fn () => $this->service->marquerTransmise($declaration))
        ->toThrow(ValidationException::class);
});

// ─── Export CA3 ───────────────────────────────────────────────────────────

it('exporte une déclaration au format CA3', function () {
    $declaration = DeclarationTva::factory()->validee()->create([
        'base_tva_collectee_20' => 10000.0,
        'montant_tva_collectee_20' => 2000.0,
        'total_tva_collectee' => 2000.0,
        'tva_deductible_biens_services' => 500.0,
        'total_tva_deductible' => 500.0,
        'tva_nette' => 1500.0,
        'tva_due' => 1500.0,
    ]);

    $ca3 = $this->service->exporterCa3($declaration);

    expect($ca3)->toBeArray()
        ->and((float) $ca3['a1_base_20'])->toBe(10000.0)
        ->and((float) $ca3['a1_tva_20'])->toBe(2000.0)
        ->and((float) $ca3['b2_biens'])->toBe(500.0)
        ->and((float) $ca3['c3_tva_due'])->toBe(1500.0)
        ->and((bool) $ca3['validee'])->toBeTrue();
});

// ─── Génération automatique ───────────────────────────────────────────────

it('génère automatiquement les déclarations mensuelles pour un exercice', function () {
    $exercice = ExerciceComptable::factory()->create([
        'date_debut' => now()->startOfYear(),
        'date_fin' => now()->startOfYear()->addMonths(2)->endOfMonth(),
    ]);

    $declarations = $this->service->genererAutomatique($exercice, RegimeTva::REEL_NORMAL);

    // 3 mois = 3 déclarations
    expect($declarations)->toHaveCount(3)
        ->and($declarations[0]->periode)->toBe(now()->startOfYear()->format('Y-m'));
});

it('génère automatiquement les déclarations trimestrielles pour un exercice', function () {
    $exercice = ExerciceComptable::factory()->create([
        'date_debut' => now()->startOfYear(),
        'date_fin' => now()->startOfYear()->addMonths(5)->endOfMonth(),
    ]);

    $declarations = $this->service->genererAutomatique($exercice, RegimeTva::REEL_SIMPLIFIE);

    // 6 mois = 2 trimestres
    expect($declarations)->toHaveCount(2);
});

it('refuse de générer des déclarations pour un régime franchise', function () {
    $exercice = createExerciceEnCours();

    expect(fn () => $this->service->genererAutomatique($exercice, RegimeTva::FRANCHISE))
        ->toThrow(ValidationException::class);
});
