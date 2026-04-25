<?php

namespace App\Filament\Tiers\Resources\Tiers\Schemas;

use App\Enums\Civility;
use App\Enums\Tiers\TiersAddressType;
use App\Enums\Tiers\TiersCategory;
use App\Enums\Tiers\TiersTypology;
use App\Rules\SirenSiretExistsRule;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class TiersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Informations Générales')
                        ->columns(4)
                        ->schema([
                            TextInput::make('code')
                                ->label('Code')
                                ->disabled(),

                            Select::make('civility')
                                ->label('Civilité')
                                ->options(Civility::class),

                            TextInput::make('name')
                                ->label('Nom / Raison Social')
                                ->required()
                                ->columnSpan(2),

                            Select::make('typology')
                                ->label('Typologie')
                                ->options(TiersTypology::class)
                                ->required(),

                            Select::make('category')
                                ->label('Type de tiers')
                                ->options(TiersCategory::class)
                                ->required(),

                            TextInput::make('siren')
                                ->label('Siren / Siret')
                                ->required()
                                ->maxLength(14)
                                ->rules([new SirenSiretExistsRule]),

                            TextInput::make('ape')
                                ->label('Code APE / NAF')
                                ->required()
                                ->maxLength(5),

                            TextInput::make('website')
                                ->url()
                                ->label('Site Web'),

                            Checkbox::make('dgpd_concilient')
                                ->label('Autorise la réutilisation de ses données personnelles')
                                ->columnSpan(3),
                        ]),

                    Wizard\Step::make('Adresses du Tiers')
                        ->schema([
                            Repeater::make('addresses')
                                ->label('')
                                ->columns(6)
                                ->schema([
                                    Select::make('address_type')
                                        ->label('Type')
                                        ->options(TiersAddressType::class)
                                        ->required(),

                                    TextInput::make('address_name')
                                        ->label('Désignation')
                                        ->columnSpan(3)
                                        ->required(),

                                    Textarea::make('address')
                                        ->label('Adresse')
                                        ->rows(3)
                                        ->columnSpan(2)
                                        ->required(),

                                    Grid::make(3)
                                        ->columnSpanFull()
                                        ->schema([
                                            TextInput::make('postal_code')
                                                ->label('Code postal')
                                                ->required(),

                                            TextInput::make('city')
                                                ->label('Ville')
                                                ->required(),

                                            TextInput::make('country')
                                                ->label('Pays')
                                                ->required(),
                                        ]),

                                    TextInput::make('phone')
                                        ->label('Téléphone')
                                        ->tel()
                                        ->columnSpan(3),

                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->columnSpan(3),

                                ]),
                        ]),

                    Wizard\Step::make('Contact Principal')
                        ->columns(3)
                        ->schema([
                            TextInput::make('first_name')
                                ->label('Nom'),

                            TextInput::make('last_name')
                                ->label('Prenom'),

                            TextInput::make('fonction')
                                ->label('Fonction'),

                            TextInput::make('tel_fix')
                                ->label('Tel fixe'),

                            TextInput::make('tel_portable')
                                ->label('Tel portable'),

                            TextInput::make('email')
                                ->label('Email'),

                            Checkbox::make('dgcp_concilient')
                                ->label('Autorise la réutilisation de ses données personnelles'),
                        ]),

                    Wizard\Step::make('Gestion / Données')
                        ->schema([
                            TextInput::make('outstanding')
                                ->label('Encours')
                                ->numeric()
                                ->prefix('€'),

                            Checkbox::make('followup')
                                ->label('Relance du tiers')
                                ->live(),

                            Repeater::make('followup_terms')
                                ->label('Rappel des relances')
                                ->visible(fn (Get $get) => $get('followup') === true)
                                ->schema([
                                    TextInput::make('first_terms')
                                        ->label('Première relance')
                                        ->numeric()
                                        ->helperText('jours après l\'échéance'),

                                    TextInput::make('second_terms')
                                        ->label('Seconde relance')
                                        ->numeric()
                                        ->helperText('jours après la relance de niv 1'),

                                    TextInput::make('third_terms')
                                        ->label('Troisème relance')
                                        ->numeric()
                                        ->helperText('jours après la relance de niv 2'),
                                ]),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
