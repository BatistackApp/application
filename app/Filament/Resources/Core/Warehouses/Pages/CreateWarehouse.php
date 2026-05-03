<?php

namespace App\Filament\Resources\Core\Warehouses\Pages;

use App\Filament\Resources\Core\Warehouses\WarehouseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;
    protected static ?string $title = 'Création d\'un dépot';
    protected static ?string $breadcrumb = 'Création';
}
