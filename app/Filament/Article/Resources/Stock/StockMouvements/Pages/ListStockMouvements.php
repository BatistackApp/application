<?php

namespace App\Filament\Article\Resources\Stock\StockMouvements\Pages;

use App\Filament\Article\Resources\Stock\StockMouvements\StockMouvementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStockMouvements extends ListRecords
{
    protected static string $resource = StockMouvementResource::class;
}
