<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\RelationManagers;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\ModePaiement;
use App\Services\Commerce\CommercialCalculator;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaiementsRelationManager extends RelationManager
{
    protected static string $relationship = 'paiements';

    protected static ?string $title = 'Paiements reçus';

    public function isReadOnly(): bool
    {
        $document = $this->getOwnerRecord();

        // Paiements uniquement pour les factures non annulées
        return ! $document->isFacture()
            || $document->status === DocumentStatus::CANCELLED
            || $document->status === DocumentStatus::PAID;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        DatePicker::make('date_paiement')
                            ->label('Date de paiement')
                            ->default(now())
                            ->required(),

                        Select::make('mode_paiement')
                            ->label('Mode de paiement')
                            ->options(ModePaiement::class)
                            ->default(ModePaiement::VIREMENT)
                            ->required(),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('montant')
                            ->label('Montant')
                            ->numeric()
                            ->prefix('€')
                            ->required()
                            ->minValue(0.01)
                            ->default(fn () => $this->getOwnerRecord()->solde),

                        TextInput::make('reference_paiement')
                            ->label('Référence')
                            ->maxLength(255)
                            ->placeholder('Référence virement, n° chèque...'),
                    ]),

                Textarea::make('note')
                    ->label('Note')
                    ->rows(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date_paiement')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('mode_paiement')
                    ->label('Mode')
                    ->badge(),

                TextColumn::make('reference_paiement')
                    ->label('Référence')
                    ->placeholder('—'),

                TextColumn::make('montant')
                    ->label('Montant')
                    ->money('EUR')
                    ->weight('bold')
                    ->color('success')
                    ->alignRight(),

                TextColumn::make('note')
                    ->label('Note')
                    ->placeholder('—')
                    ->limit(40),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Enregistrer un paiement')
                    ->after(function () {
                        $this->updateStatutFacture();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function () {
                        $this->updateStatutFacture();
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $this->updateStatutFacture();
                    }),
            ])
            ->heading('Paiements')
            ->description(function () {
                $document = $this->getOwnerRecord();
                if (! $document->isFacture()) {
                    return null;
                }
                $solde = $document->solde;
                return $solde > 0
                    ? 'Solde restant : '.number_format($solde, 2, ',', ' ').' €'
                    : 'Facture intégralement réglée ✓';
            })
            ->defaultSort('date_paiement', 'desc');
    }

    /**
     * Met à jour le statut de la facture après paiement.
     */
    protected function updateStatutFacture(): void
    {
        $document = $this->getOwnerRecord()->fresh();
        $calculator = app(CommercialCalculator::class);
        $solde = $calculator->calculateSolde($document);

        if ($solde <= 0) {
            $document->update(['status' => DocumentStatus::PAID]);
        } elseif ($document->paiements()->count() > 0) {
            $document->update(['status' => DocumentStatus::PARTIALLY_PAID]);
        }
    }
}
