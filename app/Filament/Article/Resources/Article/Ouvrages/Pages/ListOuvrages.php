<?php

namespace App\Filament\Article\Resources\Article\Ouvrages\Pages;

use App\Filament\Article\Resources\Article\Ouvrages\OuvrageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOuvrages extends ListRecords
{
    protected static string $resource = OuvrageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
