<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Pages;

use App\Filament\RH\Resources\RH\PointageSessions\Actions\PointageWorkflowActions;
use App\Filament\RH\Resources\RH\PointageSessions\Actions\PrintPointageAction;
use App\Filament\RH\Resources\RH\PointageSessions\PointageSessionResource;
use App\Filament\RH\Widgets\PointageSessionKpi;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewPointageSession extends ViewRecord
{
    protected static string $resource = PointageSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PrintPointageAction::make(),
            PointageWorkflowActions::submit(),
            PointageWorkflowActions::validate(),
            PointageWorkflowActions::reject(),
            PointageWorkflowActions::reopen(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            PointageSessionKpi::make([
                'record' => $this->getRecord(),
            ]),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        $record = $this->getRecord();

        return "{$record->employee->user->name} — {$record->label_semaine}";
    }
}
