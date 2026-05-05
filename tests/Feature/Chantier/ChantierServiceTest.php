<?php

use App\Enums\Chantier\ChantierStatus;
use App\Models\Chantier\Chantier;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Chantier\ChantierService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(ChantierService::class);
    $this->user = User::factory()->create();
    $this->tiers = Tiers::factory()->customer()->create();

    actingAs($this->user);
});

// ─── Création ────────────────────────────────────────────────────────────────

it('crée un chantier en statut DRAFT avec une référence auto', function () {
    $chantier = $this->service->create([
        'nom' => 'Chantier Test',
        'client_id' => $this->tiers->id,
    ], $this->user);

    expect($chantier->status)->toBe(ChantierStatus::DRAFT)
        ->and($chantier->reference)->toMatch('/^CH-\d{4}-\d{3}$/')
        ->and($chantier->nom)->toBe('Chantier Test')
        ->and($chantier->client_id)->toBe($this->tiers->id)
        ->and($chantier->responsable_id)->toBe($this->user->id);
});

it('assigne le créateur comme responsable par défaut', function () {
    $chantier = $this->service->create([
        'nom' => 'Chantier Test',
        'client_id' => $this->tiers->id,
    ], $this->user);

    expect($chantier->responsable_id)->toBe($this->user->id);
});

it('respecte le responsable fourni si différent du créateur', function () {
    $responsable = User::factory()->create();

    $chantier = $this->service->create([
        'nom' => 'Chantier Test',
        'client_id' => $this->tiers->id,
        'responsable_id' => $responsable->id,
    ], $this->user);

    expect($chantier->responsable_id)->toBe($responsable->id);
});

it('génère des références uniques pour chaque chantier', function () {
    $c1 = $this->service->create(['nom' => 'C1', 'client_id' => $this->tiers->id], $this->user);
    $c2 = $this->service->create(['nom' => 'C2', 'client_id' => $this->tiers->id], $this->user);
    $c3 = $this->service->create(['nom' => 'C3', 'client_id' => $this->tiers->id], $this->user);

    expect($c1->reference)->not->toBe($c2->reference)
        ->and($c2->reference)->not->toBe($c3->reference);
});

// ─── Transitions de statut ────────────────────────────────────────────────────

it('passe un chantier de DRAFT à OPEN', function () {
    $chantier = Chantier::factory()->draft()->create([
        'client_id' => $this->tiers->id,
    ]);

    $chantier = $this->service->open($chantier);

    expect($chantier->status)->toBe(ChantierStatus::OPEN);
});

it('passe un chantier de OPEN à ACTIVE et enregistre la date de début réelle', function () {
    $chantier = Chantier::factory()->open()->create([
        'client_id' => $this->tiers->id,
        'date_debut_reelle' => null,
    ]);

    $chantier = $this->service->activate($chantier);

    expect($chantier->status)->toBe(ChantierStatus::ACTIVE)
        ->and($chantier->date_debut_reelle)->not->toBeNull();
});

it('ne réécrit pas la date de début réelle si elle existe déjà', function () {
    $dateExistante = now()->subDays(5)->toDateString();

    $chantier = Chantier::factory()->open()->create([
        'client_id' => $this->tiers->id,
        'date_debut_reelle' => $dateExistante,
    ]);

    $chantier = $this->service->activate($chantier);

    expect($chantier->date_debut_reelle->toDateString())->toBe($dateExistante);
});

it('passe un chantier de ACTIVE à PAUSED', function () {
    $chantier = Chantier::factory()->active()->create([
        'client_id' => $this->tiers->id,
    ]);

    $chantier = $this->service->pause($chantier);

    expect($chantier->status)->toBe(ChantierStatus::PAUSED);
});

it('passe un chantier de PAUSED à ACTIVE', function () {
    $chantier = Chantier::factory()->paused()->create([
        'client_id' => $this->tiers->id,
    ]);

    $chantier = $this->service->activate($chantier);

    expect($chantier->status)->toBe(ChantierStatus::ACTIVE);
});

it('ferme un chantier ACTIVE et enregistre la date de fin réelle', function () {
    $chantier = Chantier::factory()->active()->create([
        'client_id' => $this->tiers->id,
        'date_fin_reelle' => null,
    ]);

    $chantier = $this->service->close($chantier);

    expect($chantier->status)->toBe(ChantierStatus::CLOSED)
        ->and($chantier->date_fin_reelle)->not->toBeNull();
});

it('archive un chantier CLOSED', function () {
    $chantier = Chantier::factory()->closed()->create([
        'client_id' => $this->tiers->id,
    ]);

    $chantier = $this->service->archive($chantier);

    expect($chantier->status)->toBe(ChantierStatus::ARCHIVED);
});

// ─── Transitions interdites ───────────────────────────────────────────────────

it('refuse de passer directement de DRAFT à ACTIVE', function () {
    $chantier = Chantier::factory()->draft()->create([
        'client_id' => $this->tiers->id,
    ]);

    expect(fn () => $this->service->activate($chantier))
        ->toThrow(ValidationException::class);
});

it('refuse de passer de ARCHIVED à tout autre statut', function () {
    $chantier = Chantier::factory()->create([
        'client_id' => $this->tiers->id,
        'status' => ChantierStatus::ARCHIVED,
    ]);

    expect(fn () => $this->service->open($chantier))
        ->toThrow(ValidationException::class)
        ->and(fn () => $this->service->activate($chantier))
        ->toThrow(ValidationException::class);

});

it('refuse de passer de OPEN à CLOSED directement', function () {
    $chantier = Chantier::factory()->open()->create([
        'client_id' => $this->tiers->id,
    ]);

    expect(fn () => $this->service->close($chantier))
        ->toThrow(ValidationException::class);
});

it('refuse de passer de DRAFT à CLOSED directement', function () {
    $chantier = Chantier::factory()->draft()->create([
        'client_id' => $this->tiers->id,
    ]);

    expect(fn () => $this->service->close($chantier))
        ->toThrow(ValidationException::class);
});

// ─── Mise à jour ─────────────────────────────────────────────────────────────

it('met à jour les informations d\'un chantier', function () {
    $chantier = Chantier::factory()->draft()->create([
        'client_id' => $this->tiers->id,
        'nom' => 'Ancien nom',
    ]);

    $chantier = $this->service->update($chantier, ['nom' => 'Nouveau nom']);

    expect($chantier->nom)->toBe('Nouveau nom');
});
