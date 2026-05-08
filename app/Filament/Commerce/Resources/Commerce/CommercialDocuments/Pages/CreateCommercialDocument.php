<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages;

use App\Enums\Commerce\DocumentType;
use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use App\Services\Commerce\CommercialDocumentService;
use Filament\Resources\Pages\CreateRecord;

class CreateCommercialDocument extends CreateRecord
{
    protected static string $resource = CommercialDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Générer la référence avant création
        $service = app(CommercialDocumentService::class);
        $type = DocumentType::from($data['type']);
        $data['reference'] = $service->generateReference($type);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
