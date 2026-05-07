<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Pages;

use App\Filament\RH\Resources\RH\PointageSessions\PointageSessionResource;
use App\Models\RH\Employee;
use App\Services\RH\PointageService;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePointageSession extends CreateRecord
{
    protected static string $resource = PointageSessionResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $semaine = Carbon::parse($data['semaine_du'])->startOfWeek();

        return app(PointageService::class)->createSession(
            Employee::find($data['employee_id']),
            $semaine,
            $data['notes'] ?? null,
        );
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
