<?php

use App\Enums\Chantier\ChantierBudgetType;
use App\Enums\Chantier\ChantierCoutType;
use App\Enums\Chantier\ChantierTaskStatus;
use App\Models\Chantier\Chantier;
use App\Models\Chantier\ChantierBudgetLine;
use App\Models\Chantier\ChantierCout;
use App\Models\Chantier\ChantierTask;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Notifications\Chantier\BudgetDepassementNotification;
use App\Services\Chantier\ChantierBudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ChantierBudgetService::class);
    $this->user = User::factory()->create();
    $this->tiers = Tiers::factory()->customer()->create();
    $this->chantier = Chantier::factory()->active()->create([
        'client_id' => $this->tiers->id,
    ]);

    actingAs($this->user);
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function addBudgetLine(Chantier $chantier, ChantierBudgetType $type, float $quantite, float $coutUnitaire): ChantierBudgetLine
{
    return ChantierBudgetLine::factory()->create([
        'chantier_id' => $chantier->id,
        'type' => $type,
        'designation' => 'Test ligne',
        'quantite' => $quantite,
        'cout_unitaire' => $coutUnitaire,
    ]);
}

function addCout(Chantier $chantier, ChantierCoutType $type, float $montant): ChantierCout
{
    return ChantierCout::factory()->create([
        'chantier_id' => $chantier->id,
        'type' => $type,
        'montant_ht' => $montant,
        'date_imputation' => now()->toDateString(),
    ]);
}

// ─── Budget total ─────────────────────────────────────────────────────────────

it('calcule le budget total depuis les lignes budget', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 10, 50);
    addBudgetLine($this->chantier, ChantierBudgetType::MATERIAUX, 5, 200);

    $total = $this->service->getBudgetTotal($this->chantier);

    expect($total)->toBe(1500.0);
});

it('retourne 0 si aucune ligne budget', function () {
    expect($this->service->getBudgetTotal($this->chantier))->toBe(0.0);
});

it('calcule le cout_total via la colonne virtuelle quantite × cout_unitaire', function () {
    $ligne = addBudgetLine($this->chantier, ChantierBudgetType::LOCATION, 3, 150);

    expect((float) $ligne->fresh()->cout_total)->toBe(450.0);
});

// ─── Coût réel ────────────────────────────────────────────────────────────────

it('calcule le coût réel depuis les imputations', function () {
    addCout($this->chantier, ChantierCoutType::MAIN_OEUVRE, 300);
    addCout($this->chantier, ChantierCoutType::MATERIAUX, 500);

    expect($this->service->getCoutReel($this->chantier))->toBe(800.0);
});

it('retourne 0 si aucun coût imputé', function () {
    expect($this->service->getCoutReel($this->chantier))->toBe(0.0);
});

// ─── KPI globaux ──────────────────────────────────────────────────────────────

it('calcule correctement les KPI globaux', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 10, 100);
    addCout($this->chantier, ChantierCoutType::MAIN_OEUVRE, 600);

    $kpis = $this->service->getKpis($this->chantier);

    expect($kpis['budget_total'])->toBe(1000.0)
        ->and($kpis['cout_reel'])->toBe(600.0)
        ->and($kpis['reste_a_depenser'])->toBe(400.0)
        ->and($kpis['taux_consommation'])->toBe(60.0)
        ->and($kpis['en_depassement'])->toBeFalse();
});

it('détecte un dépassement de budget', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 1, 500);
    addCout($this->chantier, ChantierCoutType::MAIN_OEUVRE, 800);

    $kpis = $this->service->getKpis($this->chantier);

    expect($kpis['en_depassement'])->toBeTrue()
        ->and($kpis['reste_a_depenser'])->toBe(-300.0);
});

// ─── Budget par type ──────────────────────────────────────────────────────────

it('groupe le budget par type', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 10, 50);
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 5, 50);
    addBudgetLine($this->chantier, ChantierBudgetType::MATERIAUX, 2, 200);

    $budgetParType = $this->service->getBudgetParType($this->chantier);

    expect($budgetParType['main_oeuvre']['budget'])->toBe(750.0)
        ->and($budgetParType['materiaux']['budget'])->toBe(400.0)
        ->and($budgetParType['location']['budget'])->toBe(0.0);
});

it('calcule les écarts par type', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 10, 100);
    addCout($this->chantier, ChantierCoutType::MAIN_OEUVRE, 800);

    $ecarts = $this->service->getEcartParType($this->chantier);

    expect($ecarts['main_oeuvre']['budget'])->toBe(1000.0)
        ->and($ecarts['main_oeuvre']['reel'])->toBe(800.0)
        ->and($ecarts['main_oeuvre']['ecart'])->toBe(200.0)
        ->and($ecarts['main_oeuvre']['en_depassement'])->toBeFalse()
        ->and($ecarts['main_oeuvre']['taux_conso'])->toBe(80.0);
});

it('détecte le dépassement par type', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MATERIAUX, 1, 500);
    addCout($this->chantier, ChantierCoutType::MATERIAUX, 700);

    $ecarts = $this->service->getEcartParType($this->chantier);

    expect($ecarts['materiaux']['en_depassement'])->toBeTrue()
        ->and($ecarts['materiaux']['ecart'])->toBe(-200.0);
});

// ─── Avancement global ────────────────────────────────────────────────────────

it('retourne 0 si aucune tâche', function () {
    expect($this->service->getAvancementGlobal($this->chantier))->toBe(0.0);
});

it('calcule l\'avancement global non pondéré si aucune ligne budget liée', function () {
    ChantierTask::factory()->create([
        'chantier_id' => $this->chantier->id,
        'avancement_pct' => 50,
        'status' => ChantierTaskStatus::IN_PROGRESS,
        'date_debut' => now()->subDays(5),
        'date_fin' => now()->addDays(5),
    ]);

    ChantierTask::factory()->create([
        'chantier_id' => $this->chantier->id,
        'avancement_pct' => 100,
        'status' => ChantierTaskStatus::DONE,
        'date_debut' => now()->subDays(10),
        'date_fin' => now()->subDays(1),
    ]);

    $avancement = $this->service->getAvancementGlobal($this->chantier);

    expect($avancement)->toBe(75.0);
});

it('calcule l\'avancement pondéré par budget alloué', function () {
    $ligne1 = addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 10, 100);
    $ligne2 = addBudgetLine($this->chantier, ChantierBudgetType::MATERIAUX, 1, 3000);

    $task1 = ChantierTask::factory()->create([
        'chantier_id' => $this->chantier->id,
        'avancement_pct' => 100,
        'status' => ChantierTaskStatus::DONE,
        'date_debut' => now()->subDays(10),
        'date_fin' => now()->subDays(1),
    ]);
    $task1->budgetLines()->attach($ligne1->id, ['allocation_pct' => 100]);

    $task2 = ChantierTask::factory()->create([
        'chantier_id' => $this->chantier->id,
        'avancement_pct' => 0,
        'status' => ChantierTaskStatus::TODO,
        'date_debut' => now(),
        'date_fin' => now()->addDays(10),
    ]);
    $task2->budgetLines()->attach($ligne2->id, ['allocation_pct' => 100]);

    // task1 : budget 1000€ × 100% = 1000
    // task2 : budget 3000€ × 0%   = 0
    // avancement = (1000×100 + 3000×0) / 4000 = 25%
    $avancement = $this->service->getAvancementGlobal($this->chantier);

    expect($avancement)->toBe(25.0);
});

// ─── Imputation manuelle ──────────────────────────────────────────────────────

it('impute un coût manuellement sur un chantier', function () {
    $cout = $this->service->imputerCout($this->chantier, [
        'type' => ChantierCoutType::DIVERS,
        'designation' => 'Déplacement',
        'montant_ht' => 120,
    ]);

    expect($cout->chantier_id)->toBe($this->chantier->id)
        ->and((float)$cout->montant_ht)->toBe(120.0)
        ->and($cout->user_id)->toBe(auth()->id())
        ->and($this->service->getCoutReel($this->chantier))->toBe(120.0);

});

it('envoie une notification si le budget est dépassé après imputation', function () {
    $responsable = User::factory()->create();
    $this->chantier->update(['responsable_id' => $responsable->id]);

    addBudgetLine($this->chantier, ChantierBudgetType::DIVERS, 1, 100);

    Notification::fake();

    $this->service->imputerCout($this->chantier, [
        'type' => ChantierCoutType::DIVERS,
        'designation' => 'Frais divers',
        'montant_ht' => 150,
    ]);

    Notification::assertSentTo(
        $responsable,
        BudgetDepassementNotification::class,
    );
});

it('n\'envoie pas de notification si le budget n\'est pas dépassé', function () {
    $responsable = User::factory()->create();
    $this->chantier->update(['responsable_id' => $responsable->id]);

    addBudgetLine($this->chantier, ChantierBudgetType::DIVERS, 1, 500);

    Notification::fake();

    $this->service->imputerCout($this->chantier, [
        'type' => ChantierCoutType::DIVERS,
        'designation' => 'Frais divers',
        'montant_ht' => 200,
    ]);

    Notification::assertNotSentTo(
        $responsable,
        BudgetDepassementNotification::class,
    );
});

// ─── Ecart avancement/consommation ───────────────────────────────────────────

it('calcule l\'écart avancement vs consommation', function () {
    addBudgetLine($this->chantier, ChantierBudgetType::MAIN_OEUVRE, 10, 100);
    addCout($this->chantier, ChantierCoutType::MAIN_OEUVRE, 400);

    ChantierTask::factory()->create([
        'chantier_id' => $this->chantier->id,
        'avancement_pct' => 60,
        'status' => ChantierTaskStatus::IN_PROGRESS,
        'date_debut' => now()->subDays(5),
        'date_fin' => now()->addDays(5),
    ]);

    // Consommation = 400/1000 = 40%
    // Avancement   = 60%
    // Écart        = 60 - 40 = +20 (on avance plus vite qu'on dépense)
    $ecart = $this->service->getEcartAvancementConso($this->chantier);

    expect($ecart)->toBe(20.0);
});
