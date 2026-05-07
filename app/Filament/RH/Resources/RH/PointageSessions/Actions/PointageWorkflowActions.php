<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Actions;

use App\Enums\RH\PointageStatus;
use App\Models\RH\PointageSession;
use App\Services\RH\PointageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PointageWorkflowActions
{
    public static function submit(): Action
    {
        return Action::make('submit')
            ->label('Soumettre')
            ->icon(Phosphor::PaperPlane)
            ->color('warning')
            ->visible(fn (PointageSession $record) => $record->status === PointageStatus::DRAFT)
            ->requiresConfirmation()
            ->modalHeading('Soumettre le pointage')
            ->modalDescription('La session sera soumise pour validation. Vous ne pourrez plus modifier les lignes.')
            ->action(function (PointageSession $record, PointageService $service) {
                try {
                    $service->submit($record);

                    Notification::make()
                        ->title('Pointage soumis')
                        ->body('La session est en attente de validation.')
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Soumission impossible')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function validate(): Action
    {
        return Action::make('validate')
            ->label('Valider')
            ->icon(Phosphor::CheckCircle)
            ->color('success')
            ->visible(fn (PointageSession $record) => $record->status === PointageStatus::SUBMITTED)
            ->requiresConfirmation()
            ->modalHeading('Valider le pointage')
            ->modalDescription('Les coûts seront automatiquement imputés aux chantiers concernés.')
            ->action(function (PointageSession $record, PointageService $service) {
                try {
                    $service->validate($record, auth()->user());

                    Notification::make()
                        ->title('Pointage validé')
                        ->body('Les coûts ont été imputés aux chantiers.')
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Validation impossible')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->label('Rejeter')
            ->icon(Phosphor::XCircle)
            ->color('danger')
            ->visible(fn (PointageSession $record) => $record->status === PointageStatus::SUBMITTED)
            ->modalHeading('Rejeter le pointage')
            ->modalDescription('Précisez le motif de rejet. Le responsable sera notifié.')
            ->schema([
                Textarea::make('rejection_reason')
                    ->label('Motif de rejet')
                    ->required()
                    ->rows(3)
                    ->placeholder('Ex : Heures incomplètes sur le chantier CH-2026-001...'),
            ])
            ->action(function (PointageSession $record, array $data, PointageService $service) {
                try {
                    $service->reject($record, auth()->user(), $data['rejection_reason']);

                    Notification::make()
                        ->title('Pointage rejeté')
                        ->body('Le salarié a été notifié du rejet.')
                        ->warning()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Rejet impossible')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function reopen(): Action
    {
        return Action::make('reopen')
            ->label('Rouvrir')
            ->icon(Phosphor::ArrowCounterClockwise)
            ->color('gray')
            ->visible(fn (PointageSession $record) => $record->status === PointageStatus::REJECTED)
            ->requiresConfirmation()
            ->modalHeading('Rouvrir la session')
            ->modalDescription('La session repassera en brouillon pour correction.')
            ->action(function (PointageSession $record, PointageService $service) {
                try {
                    $service->reopen($record);

                    Notification::make()
                        ->title('Session rouverte')
                        ->body('La session est de nouveau en brouillon.')
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Impossible')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }
}
