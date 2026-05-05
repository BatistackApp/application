<?php

namespace App\Filament\Chantier\Resources\Chantiers\Pages;

use App\Filament\Chantier\Resources\Chantiers\ChantierResource;
use App\Services\Chantier\ChantierService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateChantier extends CreateRecord
{
    protected static string $resource = ChantierResource::class;
    protected static ?string $title = 'Nouveau chantier';

    protected function handleRecordCreation(array $data): Model
    {
        return app(ChantierService::class)->create($data, auth()->user());
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
