<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Pages;

use App\Filament\Article\Resources\Stock\InventorySessions\Actions\InventoryWorkflowActions;
use App\Filament\Article\Resources\Stock\InventorySessions\Actions\PrintInventoryAction;
use App\Filament\Article\Resources\Stock\InventorySessions\InventorySessionResource;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewInventorySession extends ViewRecord
{
    protected static string $resource = InventorySessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PrintInventoryAction::make(),
            InventoryWorkflowActions::startCounting(),
            InventoryWorkflowActions::close(),
            InventoryWorkflowActions::reopen(),
            InventoryWorkflowActions::validate(),
            InventoryWorkflowActions::cancel(),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Inventaire : {$this->getRecord()->reference}";
    }
}
