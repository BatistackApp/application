<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Pages;

use App\Filament\Commerce\Resources\Commerce\CommercialDocuments\CommercialDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCommercialDocument extends EditRecord
{
    protected static string $resource = CommercialDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
