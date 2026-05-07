<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Pages;

use App\Filament\RH\Resources\RH\PointageSessions\PointageSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPointageSession extends EditRecord
{
    protected static string $resource = PointageSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
