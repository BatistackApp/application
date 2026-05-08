<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\Schemas;

use App\Enums\Commerce\DocumentType;
use App\Models\Commerce\CommercialDocument;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommercialDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('reference')
                                    ->label('Référence')
                                    ->weight('bold')
                                    ->size('lg'),

                                TextEntry::make('type')
                                    ->label('Type')
                                    ->badge(),

                                TextEntry::make('status')
                                    ->label('Statut')
                                    ->badge(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('client.name')
                                    ->label('Client'),

                                TextEntry::make('chantier.reference')
                                    ->label('Chantier')
                                    ->placeholder('Aucun chantier lié'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('date_document')
                                    ->label('Date')
                                    ->date('d/m/Y'),

                                TextEntry::make('date_validite')
                                    ->label('Validité')
                                    ->date('d/m/Y')
                                    ->visible(fn (CommercialDocument $record) => $record->type === DocumentType::DEVIS),

                                TextEntry::make('date_echeance')
                                    ->label('Échéance')
                                    ->date('d/m/Y')
                                    ->visible(fn (CommercialDocument $record) => $record->isFacture()),
                            ]),
                    ]),

                Section::make('Montants')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('total_ht')
                                    ->label('Total HT')
                                    ->money('EUR'),

                                TextEntry::make('total_tva')
                                    ->label('Total TVA')
                                    ->money('EUR'),

                                TextEntry::make('total_ttc')
                                    ->label('Total TTC')
                                    ->money('EUR')
                                    ->weight('bold')
                                    ->size('lg')
                                    ->color('success'),

                                TextEntry::make('solde')
                                    ->label('Solde à payer')
                                    ->state(fn (CommercialDocument $record) => $record->solde)
                                    ->money('EUR')
                                    ->color(fn (CommercialDocument $record) => $record->solde > 0 ? 'danger' : 'success')
                                    ->visible(fn (CommercialDocument $record) => $record->isFacture()),
                            ]),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('Aucune note'),

                        TextEntry::make('conditions_reglement')
                            ->label('Conditions de règlement')
                            ->placeholder('Non spécifiées'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
