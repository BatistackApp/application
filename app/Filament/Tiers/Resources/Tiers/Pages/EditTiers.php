<?php

namespace App\Filament\Tiers\Resources\Tiers\Pages;

use App\Filament\Tiers\Resources\Tiers\TiersResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTiers extends EditRecord
{
    protected static string $resource = TiersResource::class;
    protected static ?string $title = 'Editer tier';
    protected static ?string $breadcrumb = 'Editer tier';
}
