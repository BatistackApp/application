<?php

namespace App\Filament\Article\Resources\Article\Ouvrages\Pages;

use App\Filament\Article\Resources\Article\Ouvrages\OuvrageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOuvrage extends ViewRecord
{
    protected static string $resource = OuvrageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
