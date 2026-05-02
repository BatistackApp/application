<?php

namespace App\Filament\Article\Resources\Article\Ouvrages\Pages;

use App\Filament\Article\Resources\Article\Ouvrages\OuvrageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOuvrage extends EditRecord
{
    protected static string $resource = OuvrageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
