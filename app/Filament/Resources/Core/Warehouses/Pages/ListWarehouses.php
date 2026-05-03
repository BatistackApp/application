<?php

namespace App\Filament\Resources\Core\Warehouses\Pages;

use App\Filament\Resources\Core\Warehouses\WarehouseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;
    protected static ?string $title = 'Liste des dépots';
    protected static ?string $breadcrumb = 'Listing';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-s-plus')
                ->label('Nouveau dépot'),
        ];
    }
}
