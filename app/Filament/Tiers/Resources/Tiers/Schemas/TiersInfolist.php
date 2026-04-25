<?php

namespace App\Filament\Tiers\Resources\Tiers\Schemas;

use App\Models\Tiers\Tiers;
use App\Services\Core\SirenService;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class TiersInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Générales')
                    ->columnSpanFull()
                    ->columns(3)
                    ->components([
                        TextEntry::make('code')
                            ->label('Référence')
                            ->weight('bold')
                            ->copyable(),
                        TextEntry::make('name')
                            ->label('Nom / Raison Sociale')
                            ->weight('bold'),
                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('civility')
                            ->label('Civilité'),
                        TextEntry::make('typology')
                            ->label('Typologie')
                            ->badge(),
                        TextEntry::make('category')
                            ->label('Catégorie')
                            ->badge(),
                    ]),

                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Identifiants Légaux')
                            ->columns(3)
                            ->components([
                                TextEntry::make('siren')
                                    ->label('SIREN')
                                    ->icon(function (SirenService $service, Tiers $record) {
                                        return $service->exists($record->siren) ? Phosphor::CheckCircle : Phosphor::XCircle;
                                    })
                                    ->iconColor(function (SirenService $service, Tiers $record) {
                                        return $service->exists($record->siren) ? 'success' : 'danger';
                                    })
                                    ->tooltip(function (SirenService $service, Tiers $record) {
                                        return $service->exists($record->siren) ? 'Siren Valide' : 'Siren Invalide';
                                    })
                                    ->placeholder('Non renseigné'),
                                TextEntry::make('naf')
                                    ->label('Code NAF')
                                    ->placeholder('Non renseigné'),
                                TextEntry::make('num_tva')
                                    ->label('Numéro TVA')
                                    ->placeholder('Non renseigné'),
                            ]),

                        Section::make('Coordonnées & Autres')
                            ->columns(2)
                            ->components([
                                TextEntry::make('website')
                                    ->label('Site Web')
                                    ->url(fn($record) => $record->website, true)
                                    ->color('primary')
                                    ->placeholder('Aucun site web'),
                                IconEntry::make('dgpd_concilient')
                                    ->label('Conformité RGPD')
                                    ->boolean(),
                            ]),

                        Section::make('Gestion')
                            ->schema([
                                TextEntry::make('setting.outstanding')
                                    ->label('Encours')
                                    ->color('primary')
                                    ->money('EUR'),

                                IconEntry::make('setting.followup')
                                    ->label('Relance')
                                    ->boolean(),
                            ]),
                    ])
            ]);
    }
}
