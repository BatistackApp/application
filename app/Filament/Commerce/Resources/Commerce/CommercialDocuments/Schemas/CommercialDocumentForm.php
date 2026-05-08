<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Schemas;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\DocumentType;
use App\Models\Commerce\CommercialDocument;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class CommercialDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('type')
                                    ->label('Type de document')
                                    ->options(DocumentType::class)
                                    ->required()
                                    ->live()
                                    ->disabled(fn (?CommercialDocument $record) => $record?->exists),

                                TextInput::make('reference')
                                    ->label('Référence')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(fn () => 'Auto-généré'),

                                Select::make('status')
                                    ->label('Statut')
                                    ->options(DocumentStatus::class)
                                    ->default(DocumentStatus::DRAFT)
                                    ->disabled(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('client_id')
                                    ->label('Client')
                                    ->relationship('client', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                    ]),

                                Select::make('chantier_id')
                                    ->label('Chantier (optionnel)')
                                    ->relationship('chantier', 'reference')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(3)
                            ->schema([
                                DatePicker::make('date_document')
                                    ->label('Date du document')
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('date_validite')
                                    ->label('Date de validité')
                                    ->visible(fn (Get $get) => $get('type') === DocumentType::DEVIS->value),

                                DatePicker::make('date_echeance')
                                    ->label('Date d\'échéance')
                                    ->visible(fn (Get $get) => in_array($get('type'), [
                                        DocumentType::FACTURE->value,
                                        DocumentType::FACTURE_ACOMPTE->value,
                                    ])),
                            ]),
                    ]),

                Section::make('Remise globale')
                    ->description('Remise appliquée sur le total du document')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('remise_globale_pct')
                                    ->label('Remise (%)')
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default(0),

                                TextInput::make('remise_globale_montant')
                                    ->label('Remise (montant)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Facturation par avancement')
                    ->description('Pour facturation liée à l\'avancement d\'un chantier')
                    ->schema([
                        TextInput::make('avancement_pct')
                            ->label('Pourcentage d\'avancement')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->visible(fn (Get $get) => in_array($get('type'), [
                        DocumentType::FACTURE->value,
                        DocumentType::FACTURE_ACOMPTE->value,
                    ]))
                    ->collapsible()
                    ->collapsed(),

                Section::make('Notes et conditions')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes / Objet')
                            ->rows(3),

                        Textarea::make('conditions_reglement')
                            ->label('Conditions de règlement')
                            ->default('Paiement à 30 jours fin de mois')
                            ->rows(2),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
