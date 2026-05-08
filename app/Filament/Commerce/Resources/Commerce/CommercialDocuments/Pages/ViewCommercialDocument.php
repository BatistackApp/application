<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages;

use App\Enums\Commerce\DocumentStatus;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Actions\CommercialWorkflowActions;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Actions\ConvertDocumentAction;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Actions\PrintDocumentAction;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Widgets\DocumentTotauxWidget;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCommercialDocument extends ViewRecord
{
    protected static string $resource = CommercialDocumentResource::class;

    protected function getHeaderActions(): array
    {
        $document = $this->getRecord();

        return [
            // Impression PDF
            PrintDocumentAction::make($document),

            // Actions workflow
            ...CommercialWorkflowActions::make($document),

            // Conversion vers autre type
            ConvertDocumentAction::make($document),

            // Edition (si brouillon)
            EditAction::make()
                ->visible(fn () => $document->status === DocumentStatus::DRAFT),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            DocumentTotauxWidget::class,
        ];
    }
}
