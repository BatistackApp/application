<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Actions;

use App\Enums\Article\InventorySessionStatus;
use App\Models\Stock\InventorySession;
use App\Services\Stock\InventorySessionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class InventoryWorkflowActions
{
    public static function startCounting(): Action
    {
        return Action::make('start_counting')
            ->label('Démarrer le comptage')
            ->icon(Phosphor::Play)
            ->color('info')
            ->visible(fn (InventorySession $record) => $record->status === InventorySessionStatus::OPEN)
            ->requiresConfirmation()
            ->modalHeading('Démarrer le comptage')
            ->modalDescription('Les lignes d\'inventaire sont prêtes. Confirmez pour passer en mode comptage.')
            ->action(function (InventorySession $record, InventorySessionService $service) {
                try {
                    $service->startCounting($record);

                    Notification::make()
                        ->title('Comptage démarré')
                        ->body("La session {$record->reference} est maintenant en cours de comptage.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function close(): Action
    {
        return Action::make('close_session')
            ->label('Fermer le comptage')
            ->icon(Phosphor::Lock)
            ->color('warning')
            ->visible(fn (InventorySession $record) => $record->status === InventorySessionStatus::COUNTING)
            ->requiresConfirmation()
            ->modalHeading('Fermer le comptage')
            ->modalDescription('Toutes les lignes doivent être comptées. Cette action est réversible.')
            ->action(function (InventorySession $record, InventorySessionService $service) {
                try {
                    $service->close($record);

                    Notification::make()
                        ->title('Session fermée')
                        ->body("La session {$record->reference} est fermée et en attente de validation.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Comptage incomplet')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function validate(): Action
    {
        return Action::make('validate_session')
            ->label('Valider l\'inventaire')
            ->icon(Phosphor::CheckCircle)
            ->color('success')
            ->visible(fn (InventorySession $record) => $record->status === InventorySessionStatus::CLOSED)
            ->requiresConfirmation()
            ->modalHeading('Valider l\'inventaire')
            ->modalDescription('Les ajustements de stock seront appliqués automatiquement. Cette action est irréversible.')
            ->action(function (InventorySession $record, InventorySessionService $service) {
                try {
                    $service->validate($record, auth()->user());

                    Notification::make()
                        ->title('Inventaire validé')
                        ->body("Les ajustements de stock ont été appliqués pour la session {$record->reference}.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Erreur de validation')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function reopen(): Action
    {
        return Action::make('reopen_session')
            ->label('Rouvrir le comptage')
            ->icon(Phosphor::ArrowCounterClockwise)
            ->color('gray')
            ->visible(fn (InventorySession $record) => $record->status === InventorySessionStatus::CLOSED)
            ->requiresConfirmation()
            ->modalHeading('Rouvrir la session')
            ->modalDescription('La session repassera en mode comptage pour correction.')
            ->action(function (InventorySession $record, InventorySessionService $service) {
                try {
                    $service->reopen($record);

                    Notification::make()
                        ->title('Session rouverte')
                        ->body("La session {$record->reference} est de nouveau en cours de comptage.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel_session')
            ->label('Annuler la session')
            ->icon(Phosphor::XCircle)
            ->color('danger')
            ->visible(fn (InventorySession $record) => in_array($record->status, [
                InventorySessionStatus::OPEN,
                InventorySessionStatus::COUNTING,
                InventorySessionStatus::CLOSED,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Annuler la session')
            ->modalDescription('La session sera annulée. Aucun ajustement ne sera appliqué.')
            ->action(function (InventorySession $record, InventorySessionService $service) {
                try {
                    $service->cancel($record);

                    Notification::make()
                        ->title('Session annulée')
                        ->body("La session {$record->reference} a été annulée.")
                        ->success()
                        ->send();
                } catch (ValidationException $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body(collect($e->errors())->flatten()->first())
                        ->danger()
                        ->send();
                }
            });
    }
}
