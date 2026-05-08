<?php

namespace App\Filament\Commerce\Resources\Commerce\CommercialDocuments\RelationManagers;

use App\Enums\Commerce\TauxTva;
use App\Models\Article\Article;
use App\Models\Article\Ouvrage;
use App\Services\Commerce\CommercialCalculator;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LinesRelationManager extends RelationManager
{
    protected static string $relationship = 'lines';

    protected static ?string $title = 'Lignes du document';

    protected static ?string $recordTitleAttribute = 'designation';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('article_id')
                            ->label('Article')
                            ->relationship('article', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $article = Article::find($state);
                                    $set('designation', $article->name);
                                    $set('unite', $article->unit->getAbrv());
                                    $set('prix_unitaire_ht', $article->prix_vente_ht ?? 0);
                                    $set('ouvrage_id', null);
                                }
                            }),

                        Select::make('ouvrage_id')
                            ->label('Ouvrage')
                            ->relationship('ouvrage', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $ouvrage = Ouvrage::find($state);
                                    $set('designation', $ouvrage->sku.' - '.$ouvrage->name);
                                    $set('unite', $ouvrage->unit->getAbrv());
                                    $set('prix_unitaire_ht', $ouvrage->prix_total_ht ?? 0);
                                    $set('article_id', null);
                                }
                            }),
                    ]),

                TextInput::make('designation')
                    ->label('Désignation')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Grid::make(3)
                    ->schema([
                        TextInput::make('quantite')
                            ->label('Quantité')
                            ->numeric()
                            ->required()
                            ->default(1)
                            ->minValue(0),

                        TextInput::make('unite')
                            ->label('Unité')
                            ->maxLength(20)
                            ->placeholder('u, m², ml...'),

                        TextInput::make('prix_unitaire_ht')
                            ->label('Prix unitaire HT')
                            ->numeric()
                            ->required()
                            ->prefix('€')
                            ->minValue(0),
                    ]),

                Grid::make(3)
                    ->schema([
                        Select::make('taux_tva')
                            ->label('Taux TVA')
                            ->options(TauxTva::class)
                            ->default(TauxTva::TVA_20->value)
                            ->required(),

                        TextInput::make('remise_pct')
                            ->label('Remise (%)')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0),

                        TextInput::make('remise_montant')
                            ->label('Remise (montant)')
                            ->numeric()
                            ->prefix('€')
                            ->minValue(0)
                            ->default(0),
                    ]),

                Hidden::make('ordre')
                    ->default(fn (RelationManager $livewire) => $livewire->getOwnerRecord()->lines()->max('ordre') + 1
                    ),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('designation')
            ->columns([
                TextColumn::make('ordre')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Désignation')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('article.sku')
                    ->label('Réf. Article')
                    ->placeholder('—'),

                TextColumn::make('quantite')
                    ->label('Quantité')
                    ->state(fn ($record) => number_format($record->quantite, 2, ',', ' ').' '.$record->unite)
                    ->alignRight(),

                TextColumn::make('prix_unitaire_ht')
                    ->label('P.U. HT')
                    ->money('EUR')
                    ->alignRight(),

                TextColumn::make('taux_tva')
                    ->label('TVA')
                    ->state(fn ($record) => number_format($record->taux_tva, 1, ',', ' ').' %')
                    ->alignRight(),

                TextColumn::make('total_ht')
                    ->label('Total HT')
                    ->money('EUR')
                    ->weight('bold')
                    ->alignRight(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function () {
                        $calculator = app(CommercialCalculator::class);
                        $calculator->recalculateDocument($this->getOwnerRecord());
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function () {
                        $calculator = app(CommercialCalculator::class);
                        $calculator->recalculateDocument($this->getOwnerRecord());
                    }),
                DeleteAction::make()
                    ->after(function () {
                        $calculator = app(CommercialCalculator::class);
                        $calculator->recalculateDocument($this->getOwnerRecord());
                    }),
            ])
            ->groupedBulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function () {
                            $calculator = app(CommercialCalculator::class);
                            $calculator->recalculateDocument($this->getOwnerRecord());
                        }),
                ]),
            ])
            ->reorderable('ordre')
            ->defaultSort('ordre');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
