<?php

namespace App\Filament\RH\Resources\RH\Employees\Pages;

use App\Filament\RH\Resources\RH\Employees\EmployeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListEmployees extends ListRecords
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau salarié')
                ->icon(Phosphor::PlusCircle),
        ];
    }
}
