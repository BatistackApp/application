<?php

namespace App\Filament\Tiers\Resources\Tiers\Pages;

use App\Filament\Tiers\Resources\Tiers\TiersResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTiers extends ViewRecord
{
    protected static string $resource = TiersResource::class;

    protected static ?string $title = 'Fiche du tier';

    protected static ?string $breadcrumb = 'Fiche du tier';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public static function setTitle(?string $title): void
    {
        $record = self::getRecord();
        self::$title = 'Fiche du tier '.$record->name;
    }
}
