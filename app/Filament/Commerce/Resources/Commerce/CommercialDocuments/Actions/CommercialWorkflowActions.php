<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Actions;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Commerce\CommercialDocument;
use App\Services\Commerce\CommercialDocumentService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class CommercialWorkflowActions
{
    public static function make(CommercialDocument $document): array
    {
        return [
            self::validateAction($document),
            self::acceptAction($document),
            self::refuseAction($document),
            self::deliverAction($document),
            self::cancelAction($document),
        ];
    }

    /**
     * Valider (DRAFT → SENT).
     */
    protected static function validateAction(CommercialDocument $document): Action
    {
        return Action::make('validate')
            ->label('Valider & Envoyer')
            ->icon(Phosphor::PaperPlane)
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Valider le document')
            ->modalDescription('Le document sera marqué comme envoyé au client.')
            ->visible(fn () => $document->status === DocumentStatus::DRAFT)
            ->action(function () use ($document) {
                try {
                    app(CommercialDocumentService::class)->validate($document);

                    Notification::make()
                        ->title('Document validé')
                        ->body("{$document->reference} marqué comme envoyé.")
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }

    /**
     * Accepter (SENT → ACCEPTED).
     */
    protected static function acceptAction(CommercialDocument $document): Action
    {
        return Action::make('accept')
            ->label('Marquer accepté')
            ->icon(Phosphor::CheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->visible(fn () => $document->status === DocumentStatus::SENT
                && in_array($document->type, [
                    DocumentType::DEVIS,
                    DocumentType::BON_COMMANDE,
                ]))
            ->action(function () use ($document) {
                app(CommercialDocumentService::class)->accept($document);

                Notification::make()
                    ->title('Document accepté')
                    ->success()
                    ->send();
            });
    }

    /**
     * Refuser (SENT → REFUSED) — Devis uniquement.
     */
    protected static function refuseAction(CommercialDocument $document): Action
    {
        return Action::make('refuse')
            ->label('Marquer refusé')
            ->icon(Phosphor::XCircle)
            ->color('danger')
            ->form([
                Forms\Components\Textarea::make('motif')
                    ->label('Motif du refus')
                    ->rows(3)
                    ->placeholder('Raison du refus par le client...'),
            ])
            ->visible(fn () => $document->status === DocumentStatus::SENT
                && $document->type === DocumentType::DEVIS)
            ->action(function (array $data) use ($document) {
                app(CommercialDocumentService::class)->refuse($document, $data['motif'] ?? null);

                Notification::make()
                    ->title('Devis refusé')
                    ->warning()
                    ->send();
            });
    }

    /**
     * Livrer (SENT → DELIVERED) — BL uniquement.
     */
    protected static function deliverAction(CommercialDocument $document): Action
    {
        return Action::make('deliver')
            ->label('Confirmer livraison')
            ->icon(Phosphor::Package)
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn () => $document->status === DocumentStatus::SENT
                && $document->type === DocumentType::BON_LIVRAISON)
            ->action(function () use ($document) {
                app(CommercialDocumentService::class)->markAsDelivered($document);

                Notification::make()
                    ->title('Livraison confirmée')
                    ->success()
                    ->send();
            });
    }

    /**
     * Annuler.
     */
    protected static function cancelAction(CommercialDocument $document): Action
    {
        return Action::make('cancel')
            ->label('Annuler')
            ->icon(Phosphor::Prohibit)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Annuler le document')
            ->modalDescription('Cette action est irréversible.')
            ->visible(fn () => ! in_array($document->status, [
                DocumentStatus::PAID,
                DocumentStatus::CANCELLED,
            ]))
            ->action(function () use ($document) {
                app(CommercialDocumentService::class)->cancel($document);

                Notification::make()
                    ->title('Document annulé')
                    ->warning()
                    ->send();
            });
    }
}
