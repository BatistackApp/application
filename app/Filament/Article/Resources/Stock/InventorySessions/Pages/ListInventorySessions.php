<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Pages;

use App\Filament\Article\Resources\Stock\InventorySessions\InventorySessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListInventorySessions extends ListRecords
{
    protected static string $resource = InventorySessionResource::class;
    protected static ?string $title = 'Sessions d\'inventaire';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvel inventaire')
                ->icon(Phosphor::PlusCircle),
        ];
    }
}
