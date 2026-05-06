<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Pages;

use App\Filament\RH\Resources\RH\PointageSessions\PointageSessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPointageSession extends ViewRecord
{
    protected static string $resource = PointageSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
