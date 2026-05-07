<?php

namespace App\Filament\RH\Resources\RH\Employees\Schemas;

use App\Enums\Civility;
use App\Enums\RH\JourSemaine;
use App\Enums\RH\TypeContrat;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([
                    Wizard\Step::make('Informations Salariales')
                        ->schema([
                            Grid::make(5)
                                ->schema([
                                    Select::make('civility')
                                        ->label('Civilité')
                                        ->columns(1)
                                        ->options(Civility::class),

                                    TextInput::make('first_name')
                                        ->columnSpan(2)
                                        ->label('Nom')
                                        ->required(),

                                    TextInput::make('last_name')
                                        ->columnSpan(2)
                                        ->label('Prenom')
                                        ->required(),
                                ]),

                            Fieldset::make('Adresse Postale')
                                ->schema([
                                    Textarea::make('address')
                                        ->label('Adresse Postal')
                                        ->columnSpanFull()
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
                                ]),

                            Fieldset::make('Coordonnée')
                                ->schema([
                                    Grid::make(3)
                                        ->columnSpanFull()
                                        ->schema([
                                            TextInput::make('phone')
                                                ->label('Téléphone Fixe'),

                                            TextInput::make('mobile')
                                                ->label('Téléphone Portable'),

                                            TextInput::make('email')
                                                ->label('Email')
                                                ->required(),
                                        ]),
                                ]),

                            Fieldset::make('RGPD')
                                ->schema([
                                    Checkbox::make('dgpd_concilient')
                                        ->helperText("L'acceptation créera un espace utilisateur et la génération d'un mot de passe transmis au salarié")
                                        ->label('Accepte la réutilisation de ces données'),
                                ]),
                        ]),

                    Wizard\Step::make('Informations Contractuelles')
                        ->schema([
                            Grid::make(2)
                                ->columnSpanFull()
                                ->schema([
                                    Select::make('type_contrat')
                                        ->label('Type de contrat')
                                        ->options(TypeContrat::class)
                                        ->required(),

                                    TextInput::make('taux_horaire')
                                        ->label('Taux horaire')
                                        ->required()
                                        ->numeric()
                                        ->prefix('€'),

                                    DatePicker::make('date_embauche')
                                        ->label('Date embauche')
                                        ->required(),

                                    DatePicker::make('date_fin_contrat')
                                        ->label('Date fin contrat'),
                                ]),

                            Section::make('Planning Hebdomadaire')
                                ->columnSpanFull()
                                ->description('Définissez les jours travaillés par défaut pour ce salarié (Surcharge la configuration globale)')
                                ->schema([
                                    CheckboxList::make('jours_travailles')
                                        ->options(JourSemaine::class)
                                        ->columns(4)
                                        ->label('Jours de travail'),
                                ]),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
