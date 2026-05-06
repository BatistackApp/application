<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Pages;

use App\Filament\RH\Resources\RH\PointageSessions\PointageSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPointageSessions extends ListRecords
{
    protected static string $resource = PointageSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
