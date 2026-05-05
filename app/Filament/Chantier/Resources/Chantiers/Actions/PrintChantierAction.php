<?php

namespace App\Filament\Chantier\Resources\Chantiers\Actions;

use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierDocumentGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PrintChantierAction
{
    public static function make(): Action
    {
        return Action::make('print_chantier')
            ->label('Imprimer')
            ->icon(Phosphor::Printer)
            ->color('gray')
            ->modalHeading('Impression')
            ->schema([
                Select::make('rapport')
                    ->label('Document')
                    ->options([
                        'rentabilite' => 'Rapport de rentabilité',
                        'budget' => 'Fiche budget prévisionnelle',
                    ])
                    ->default('rentabilite')
                    ->required(),
            ])
            ->action(function (Chantier $record, array $data, ChantierDocumentGenerator $generator) {
                try {
                    $path = match ($data['rapport']) {
                        'rentabilite' => $generator->rapportRentabilite($record),
                        'budget' => $generator->ficheBudget($record),
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
