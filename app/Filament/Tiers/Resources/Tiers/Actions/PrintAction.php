<?php

namespace App\Filament\Tiers\Resources\Tiers\Actions;

use App\Models\Tiers\Tiers;
use App\Services\Tiers\TiersDocumentGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PrintAction
{
    public static function action(): Action
    {
        return Action::make('print_list')
            ->iconButton()
            ->tooltip('Imprimer Liste')
            ->icon(Phosphor::Printer)
            ->schema([
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'ficheTiers' => 'Fiche Tiers',
                        'listeTiers' => 'Liste Tiers',
                    ])
                    ->live(),

                Select::make('tiers_id')
                    ->label('Tiers')
                    ->options(Tiers::all()->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn (Get $get) => $get('type') === 'ficheTiers'),
            ])
            ->action(function (TiersDocumentGenerator $generator, array $data) {
                if ($data['type'] === 'ficheTiers') {
                    $tiers = Tiers::find($data['tiers_id']);
                }
                $pdf = match ($data['type']) {
                    'listeTiers' => $generator->listeTiers(),
                    'ficheTiers' => $generator->ficheTiers($tiers)
                };

                return response()->download($pdf);
            });
    }
}
