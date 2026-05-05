<?php

namespace App\Filament\Chantier\Resources\Chantiers\Schemas;

use App\Models\Chantier\Chantier;
use App\Services\Chantier\ChantierBudgetService;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ChantierInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('reference')
                            ->label('Référence')
                            ->weight('bold')
                            ->copyable(),

                        TextEntry::make('nom')
                            ->label('Chantier')
                            ->weight('bold'),

                        TextEntry::make('status')
                            ->label('Statut')
                            ->badge(),

                        TextEntry::make('client.name')
                            ->label('Client'),

                        TextEntry::make('responsable.name')
                            ->label('Responsable')
                            ->placeholder('—'),

                        TextEntry::make('ville')
                            ->label('Localisation')
                            ->formatStateUsing(fn (Chantier $record) => trim(
                                ($record->adresse ? $record->adresse.', ' : '')
                                .$record->code_postal.' '.$record->ville
                            ))
                            ->placeholder('—'),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Planification')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('date_debut_prevue')
                                    ->label('Début prévu')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),

                                TextEntry::make('date_fin_prevue')
                                    ->label('Fin prévue')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),

                                TextEntry::make('date_debut_reelle')
                                    ->label('Début réel')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),

                                TextEntry::make('date_fin_reelle')
                                    ->label('Fin réelle')
                                    ->date('d/m/Y')
                                    ->placeholder('—'),
                            ]),

                        Section::make('Budget & Avancement')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('budget_total')
                                    ->label('Budget prévisionnel')
                                    ->state(fn (Chantier $record) => app(ChantierBudgetService::class)
                                        ->getBudgetTotal($record)
                                    )
                                    ->money('EUR'),

                                TextEntry::make('cout_reel')
                                    ->label('Coût réel engagé')
                                    ->state(fn (Chantier $record) => app(ChantierBudgetService::class)
                                        ->getCoutReel($record)
                                    )
                                    ->money('EUR')
                                    ->color(fn (Chantier $record) => app(ChantierBudgetService::class)
                                        ->getCoutReel($record) >
                                    app(ChantierBudgetService::class)->getBudgetTotal($record)
                                        ? 'danger' : 'success'
                                    ),

                                TextEntry::make('avancement')
                                    ->label('Avancement global')
                                    ->state(fn (Chantier $record) => app(ChantierBudgetService::class)
                                        ->getAvancementGlobal($record).' %'
                                    ),

                                TextEntry::make('taux_consommation')
                                    ->label('Taux consommation')
                                    ->state(function (Chantier $record) {
                                        $service = app(ChantierBudgetService::class);
                                        $budget = $service->getBudgetTotal($record);
                                        $reel = $service->getCoutReel($record);

                                        return $budget > 0
                                            ? round(($reel / $budget) * 100, 1).' %'
                                            : '0 %';
                                    }),
                            ]),
                    ]),
            ]);
    }
}
