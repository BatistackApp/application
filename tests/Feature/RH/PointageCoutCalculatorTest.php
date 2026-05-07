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
use App\Services\RH\PointageCoutCalculator;
use App\Services\RH\PointageService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(PointageService::class);
    $this->calculator = app(PointageCoutCalculator::class);
    $this->admin = User::factory()->create();

    $this->employee = Employee::factory()->create([
        'taux_horaire' => 20.00,
    ]);
    $this->chantier = Chantier::factory()->create();
});

test('it calculates standard hours cost correctly via getCoutSession', function () {
    $session = PointageSession::factory()->create(['employee_id' => $this->employee->id]);

    PointageLine::factory()->create([
        'pointage_session_id' => $session->id,
        'chantier_id' => $this->chantier->id,
        'date' => now()->startOfWeek(),
        'periode' => Periode::JOURNEE_COMPLETE,
        'heures' => 5,
        'type_heure' => TypeHeure::NORMALE,
    ]);

    $couts = $this->calculator->getCoutSession($session);

    expect((float) $couts['total'])->toBe(100.00);
});

test('it applies overtime surcharges correctly without unique constraint violation', function () {
    $session = PointageSession::factory()->create(['employee_id' => $this->employee->id]);
    $date = now()->startOfWeek();

    PointageLine::factory()->create([
        'pointage_session_id' => $session->id,
        'chantier_id' => $this->chantier->id,
        'date' => $date,
        'periode' => Periode::MATIN,
        'heures' => 2,
        'type_heure' => TypeHeure::SUPPLEMENTAIRE,
    ]);

    PointageLine::factory()->create([
        'pointage_session_id' => $session->id,
        'chantier_id' => $this->chantier->id,
        'date' => $date,
        'periode' => Periode::APREM,
        'heures' => 1,
        'type_heure' => TypeHeure::SUPPLEMENTAIRE,
    ]);

    $couts = $this->calculator->getCoutSession($session);

    expect((float) $couts['total'])->toBe(60.00);
});

test('a session can be submitted for validation when it has hours', function () {
    Notification::fake();

    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::DRAFT,
    ]);

    PointageLine::factory()->create([
        'pointage_session_id' => $session->id,
        'chantier_id' => $this->chantier->id,
        'date' => now()->startOfWeek(),
        'heures' => 8.00,
        'type_heure' => TypeHeure::NORMALE,
        'periode' => Periode::JOURNEE_COMPLETE,
    ]);

    $this->service->submit($session);

    expect($session->fresh()->status)->toBe(PointageStatus::SUBMITTED)
        ->and($session->fresh()->submitted_at)->not->toBeNull();
});

test('validating a session triggers imputation and sets status to IMPUTED', function () {
    Notification::fake();

    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::SUBMITTED,
    ]);

    PointageLine::factory()->create([
        'pointage_session_id' => $session->id,
        'chantier_id' => $this->chantier->id,
        'date' => now()->startOfWeek(),
        'heures' => 7.00,
        'type_heure' => TypeHeure::NORMALE,
        'periode' => Periode::JOURNEE_COMPLETE,
    ]);

    $this->service->validate($session, $this->admin);

    $session->refresh();

    expect($session->status)->toBe(PointageStatus::IMPUTED)
        ->and($session->validated_by)->toBe($this->admin->id)
        ->and($session->validated_at)->not->toBeNull();
});

test('rejecting a session records reason and notifies employee', function () {
    Notification::fake();

    $session = PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => PointageStatus::SUBMITTED,
    ]);

    $reason = 'Justification HS manquante';

    $this->service->reject($session, $this->admin, $reason);

    $session->refresh();
    expect($session->status)->toBe(PointageStatus::REJECTED)
        ->and($session->rejection_reason)->toBe($reason);

    /** @noinspection PhpUnhandledExceptionInspection */
    Notification::assertSentTo($this->employee->user, PointageRejeteNotification::class);
});

test('creating a session prevents duplicates for the same week', function () {
    $date = now()->startOfWeek();

    PointageSession::factory()->create([
        'employee_id' => $this->employee->id,
        'semaine_du' => $date->format('Y-m-d'),
    ]);

    expect(fn () => $this->service->createSession($this->employee, Carbon::parse($date)))
        ->toThrow(ValidationException::class);
});
