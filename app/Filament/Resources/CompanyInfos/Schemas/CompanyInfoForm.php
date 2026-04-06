<?php

namespace App\Filament\Resources\CompanyInfos\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Nakanakaii\FilamentCountries\Forms\Components\CountrySelect;

class CompanyInfoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Générales')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Raison social')
                            ->required(),

                        Textarea::make('adresse')
                            ->label('Adresse postale')
                            ->required(),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('code_postal')
                                    ->label('Code postal')
                                    ->required(),

                                TextInput::make('ville')
                                    ->label('Ville')
                                    ->required(),

                                CountrySelect::make('pays')
                                    ->label('Pays')
                                    ->required(),

                                TextInput::make('siret')
                                    ->label('Siret')
                                    ->mask('99999999999999')
                                    ->required(),

                                TextInput::make('num_tva')
                                    ->label('Numero TVA'),

                                TextInput::make('ape')
                                    ->label('APE/NAF')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Coordonnées')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('telephone')
                                    ->label('Telephone')
                                    ->tel(),

                                TextInput::make('fax')
                                    ->label('Fax')
                                    ->tel(),

                                TextInput::make('email')
                                    ->email()
                                    ->label('Adresse Mail'),

                                TextInput::make('site_web')
                                    ->label('Site Web'),
                            ]),
                    ]),

                Section::make('Logo')
                    ->columnSpanFull()
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('logo')
                            ->label('Logo')
                            ->collection('core')
                            ->visibility('public')
                            ->downloadable()
                            ->openable()
                            ->live()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
