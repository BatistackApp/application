<?php

namespace App\Filament\RH\Resources\RH\PointageSessions\Actions;

use App\Models\RH\PointageSession;
use App\Services\RH\PointageDocumentGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PrintPointageAction
{
    public static function make(): Action
    {
        return Action::make('print_pointage')
            ->label('Imprimer')
            ->icon(Phosphor::Printer)
            ->color('gray')
            ->modalHeading('Impression')
            ->schema([
                Select::make('rapport')
                    ->label('Document')
                    ->options([
                        'fiche' => 'Fiche de pointage hebdomadaire',
                        'recap' => 'Récapitulatif mensuel',
                    ])
                    ->default('fiche')
                    ->required(),
            ])
            ->action(function (
                PointageSession $record,
                array $data,
                PointageDocumentGenerator $generator,
            ) {
                try {
                    $path = match ($data['rapport']) {
                        'fiche' => $generator->fichePointage($record),
                        'recap' => $generator->recapHeuresMois(
                            $record->employee,
                            $record->semaine_du->copy()->startOfMonth(),
                        ),
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
