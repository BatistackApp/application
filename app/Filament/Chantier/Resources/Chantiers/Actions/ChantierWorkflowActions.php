<?php

namespace App\Filament\Chantier\Resources\Chantiers\Actions;

use App\Enums\Chantier\ChantierStatus;
use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ChantierWorkflowActions
{
    public static function open(): Action
    {
        return Action::make('open')
            ->label('Ouvrir le chantier')
            ->icon(Phosphor::FolderOpen)
            ->color('info')
            ->visible(fn (Chantier $record) => $record->status === ChantierStatus::DRAFT)
            ->requiresConfirmation()
            ->modalHeading('Ouvrir le chantier')
            ->modalDescription('Le chantier sera marqué comme ouvert et planifié.')
            ->action(fn (Chantier $record, ChantierService $service) => self::transition($service, $record, fn () => $service->open($record))
            );
    }

    public static function activate(): Action
    {
        return Action::make('activate')
            ->label('Démarrer le chantier')
            ->icon(Phosphor::HardHat)
            ->color('success')
            ->visible(fn (Chantier $record) => in_array($record->status, [
                ChantierStatus::OPEN,
                ChantierStatus::PAUSED,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Démarrer le chantier')
            ->modalDescription('La date de début réelle sera enregistrée automatiquement.')
            ->action(fn (Chantier $record, ChantierService $service) => self::transition($service, $record, fn () => $service->activate($record))
            );
    }

    public static function pause(): Action
    {
        return Action::make('pause')
            ->label('Mettre en pause')
            ->icon(Phosphor::PauseCircle)
            ->color('warning')
            ->visible(fn (Chantier $record) => $record->status === ChantierStatus::ACTIVE)
            ->requiresConfirmation()
            ->modalHeading('Mettre le chantier en pause')
            ->modalDescription('Les imputations restent possibles pendant la pause.')
            ->action(fn (Chantier $record, ChantierService $service) => self::transition($service, $record, fn () => $service->pause($record))
            );
    }

    public static function close(): Action
    {
        return Action::make('close')
            ->label('Terminer le chantier')
            ->icon(Phosphor::CheckCircle)
            ->color('danger')
            ->visible(fn (Chantier $record) => in_array($record->status, [
                ChantierStatus::ACTIVE,
                ChantierStatus::PAUSED,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Terminer le chantier')
            ->modalDescription('La date de fin réelle sera enregistrée. Le chantier passera en statut Terminé.')
            ->action(fn (Chantier $record, ChantierService $service) => self::transition($service, $record, fn () => $service->close($record))
            );
    }

    public static function archive(): Action
    {
        return Action::make('archive')
            ->label('Archiver')
            ->icon(Phosphor::BoxArrowDown)
            ->color('gray')
            ->visible(fn (Chantier $record) => $record->status === ChantierStatus::CLOSED)
            ->requiresConfirmation()
            ->modalHeading('Archiver le chantier')
            ->modalDescription('Le chantier sera archivé et passera en lecture seule.')
            ->action(fn (Chantier $record, ChantierService $service) => self::transition($service, $record, fn () => $service->archive($record))
            );
    }

    private static function transition(
        ChantierService $service,
        Chantier $record,
        \Closure $callback,
    ): void {
        try {
            $callback();

            Notification::make()
                ->title('Statut mis à jour')
                ->body("Le chantier {$record->reference} est maintenant : {$record->fresh()->status->getLabel()}.")
                ->success()
                ->send();
        } catch (ValidationException $e) {
            Notification::make()
                ->title('Transition impossible')
                ->body(collect($e->errors())->flatten()->first())
                ->danger()
                ->send();
        }
    }
}
