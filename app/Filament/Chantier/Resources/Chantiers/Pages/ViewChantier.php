<?php

namespace App\Filament\Chantier\Resources\Chantiers\Pages;

use App\Filament\Chantier\Resources\Chantiers\Actions\ChantierWorkflowActions;
use App\Filament\Chantier\Resources\Chantiers\Actions\PrintChantierAction;
use App\Filament\Chantier\Resources\Chantiers\ChantierResource;
use App\Filament\Chantier\Resources\Chantiers\Widgets\ChantierKpi;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewChantier extends ViewRecord
{
    protected static string $resource = ChantierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PrintChantierAction::make(),
            EditAction::make()->icon('heroicon-s-pencil'),
            ChantierWorkflowActions::open(),
            ChantierWorkflowActions::activate(),
            ChantierWorkflowActions::pause(),
            ChantierWorkflowActions::close(),
            ChantierWorkflowActions::archive(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ChantierKpi::make(['record' => $this->getRecord()]),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Chantier : {$this->getRecord()->reference}";
    }
}
