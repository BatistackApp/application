<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Pages;

use App\Filament\Article\Resources\Stock\InventorySessions\InventorySessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditInventorySession extends EditRecord
{
    protected static string $resource = InventorySessionResource::class;

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
