<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions\Actions;

use App\Enums\Article\InventorySessionStatus;
use App\Models\Stock\InventorySession;
use App\Services\Stock\InventorySessionDocumentGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PrintInventoryAction
{
    public static function make(): Action
    {
        return Action::make('print_inventory')
            ->label('Imprimer')
            ->icon(Phosphor::Printer)
            ->color('gray')
            ->modalHeading('Impression de la session d\'inventaire')
            ->mountUsing(function (array $arguments, $form, InventorySession $record) {
                $form->fill([
                    'rapport' => $record->status === InventorySessionStatus::OPEN
                        ? 'fiche_comptage'
                        : 'rapport_ecarts',
                ]);
            })
            ->schema(fn (InventorySession $record) => [
                Select::make('rapport')
                    ->label('Type de document')
                    ->options(array_filter([
                        'fiche_comptage' => 'Fiche de comptage (vierge)',
                        'rapport_ecarts' => $record->status !== InventorySessionStatus::OPEN
                            ? "Rapport d'écarts"
                            : null,
                    ]))
                    ->required(),
            ])
            ->action(function (InventorySession $record, array $data, InventorySessionDocumentGenerator $generator) {
                try {
                    $path = match ($data['rapport']) {
                        'fiche_comptage' => $generator->ficheDeComptage($record),
                        'rapport_ecarts' => $generator->rapportEcarts($record),
                    };

                    return response()->download($path);
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Erreur de génération')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
