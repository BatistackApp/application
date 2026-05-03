<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Pages;

use App\Filament\Article\Resources\Stock\InventorySessions\InventorySessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInventorySession extends ViewRecord
{
    protected static string $resource = InventorySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
