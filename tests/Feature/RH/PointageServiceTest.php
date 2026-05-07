<?php

use App\Enums\RH\Periode;
use App\Enums\RH\PointageStatus;
use App\Enums\RH\TypeHeure;
use App\Models\Chantier\Chantier;
use App\Models\RH\Employee;
use App\Models\RH\PointageLine;
use App\Models\RH\PointageSession;
use App\Models\User;
use App\Notifications\RH\PointageRejeteNotification;
use App\Notifications\RH\PointageSoumisNotification;
use App\Notifications\RH\PointageValideNotification;
use App\Services\RH\PointageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(PointageService::class);
    $this->admin = User::factory()->create();

    // Création d'un environnement de test minimal
    $this->employee = Employee::factory()->create();
    $this->chantier = Chantier::factory()->create();
});

test('a session cannot be submitted without valid lines', function () {
    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::DRAFT,
    ]);

    // Tentative de soumission d'une session vide
    expect(fn () => $this->service->submit($session))
        ->toThrow(ValidationException::class);
});

test('a session can be submitted for validation when it has hours', function () {
    Notification::fake();

    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::DRAFT,
    ]);

    // Ajout d'une ligne valide
    PointageLine::factory()->create([
        'pointage_session_id' => $session->id,
        'chantier_id' => $this->chantier->id,
        'heures' => 8.00,
        'type_heure' => TypeHeure::NORMALE,
        'periode' => Periode::JOURNEE_COMPLETE,
    ]);

    $this->service->submit($session);

    expect($session->fresh()->status)->toBe(PointageStatus::SUBMITTED)
        ->and($session->fresh()->submitted_at)->not->toBeNull();

    // Note: PointageSoumisNotification est envoyée aux utilisateurs ayant le rôle 'rh_manager'
    // On pourrait vérifier l'envoi si des utilisateurs managers existaient
});

test('validating a session updates status and notifies employee', function () {
    Notification::fake();

    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::SUBMITTED,
    ]);

    $this->service->validate($session, $this->admin);

    $session->refresh();
    expect($session->status)->toBe(PointageStatus::IMPUTED)
        ->and($session->validated_by)->toBe($this->admin->id)
        ->and($session->validated_at)->not->toBeNull();

    Notification::assertSentTo($this->employee->user, PointageValideNotification::class);
});

test('rejecting a session sets status and records reason', function () {
    Notification::fake();
    $reason = 'Mauvais Calcule';

    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::SUBMITTED,
    ]);

    $reason = 'Heures supplémentaires non justifiées sur le chantier A';
    $this->service->reject($session, $this->admin, $reason);

    $session->refresh();
    expect($session->status)->toBe(PointageStatus::REJECTED)
        ->and($session->rejection_reason)->toBe($reason)
        ->and($session->rejected_at)->not->toBeNull();

    Notification::assertSentTo($this->employee->user, PointageRejeteNotification::class);
});
