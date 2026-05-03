<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Pages;

use App\Filament\Article\Resources\Stock\InventorySessions\InventorySessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventorySession extends CreateRecord
{
    protected static string $resource = InventorySessionResource::class;
}
