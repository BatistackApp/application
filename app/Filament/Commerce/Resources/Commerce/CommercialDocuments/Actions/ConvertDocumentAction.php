<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Actions;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use App\Models\Commerce\CommercialDocument;
use App\Services\Commerce\CommercialDocumentService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ConvertDocumentAction
{
    public static function make(CommercialDocument $document): Action
    {
        // Calculer les conversions disponibles pour ce document
        $conversionsDisponibles = collect(DocumentType::cases())
            ->filter(fn (DocumentType $type) => in_array($document->type, $type->acceptsConversionFrom()))
            ->mapWithKeys(fn (DocumentType $type) => [
                $type->value => $type->getLabel(),
            ])
            ->toArray();

        $visible = ! empty($conversionsDisponibles)
            && in_array($document->status, [
                DocumentStatus::SENT,
                DocumentStatus::ACCEPTED,
                DocumentStatus::DELIVERED,
            ]);

        return Action::make('convert')
            ->label('Convertir en...')
            ->icon(Phosphor::ArrowsClockwise)
            ->color('primary')
            ->visible(fn () => $visible)
            ->form([
                Forms\Components\Select::make('target_type')
                    ->label('Convertir en')
                    ->options($conversionsDisponibles)
                    ->required(),
            ])
            ->action(function (array $data) use ($document) {
                try {
                    $service = app(CommercialDocumentService::class);
                    $targetType = DocumentType::from($data['target_type']);
                    $newDocument = $service->convert($document, $targetType);

                    Notification::make()
                        ->title('Document converti')
                        ->body("{$document->reference} → {$newDocument->reference}")
                        ->success()
                        ->send();

                    redirect(CommercialDocumentResource::getUrl('view', ['record' => $newDocument]));
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur de conversion')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
