<?php

namespace App\Filament\Chantier\Resources\Chantiers\RelationManagers;

use App\Enums\Chantier\ChantierBudgetType;
use App\Models\Article\Article;
use App\Models\Chantier\ChantierBudgetLine;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class BudgetLinesRelationManager extends RelationManager
{
    protected static string $relationship = 'budgetLines';

    protected static ?string $title = 'Budget prévisionnel';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options(ChantierBudgetType::class)
                            ->required()
                            ->live(),

                        Select::make('article_id')
                            ->label('Article du catalogue')
                            ->options(Article::orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                if ($state) {
                                    $article = Article::find($state);
                                    $set('designation', $article->name);
                                    $set('unite', $article->unit->getAbrv());
                                }
                            })
                            ->visible(fn (Get $get) => $get('type') === ChantierBudgetType::MATERIAUX->value)
                            ->placeholder('Optionnel — lier à un article'),
                    ]),

                TextInput::make('designation')
                    ->label('Désignation')
                    ->required()
                    ->columnSpanFull(),

                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('quantite')
                            ->label('Quantité')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.001)
                            ->required(),

                        TextInput::make('unite')
                            ->label('Unité')
                            ->placeholder('h, u, m², kg...'),

                        TextInput::make('cout_unitaire')
                            ->label('Coût unitaire HT')
                            ->numeric()
                            ->suffix('€')
                            ->required(),
                    ]),

                Textarea::make('note')
                    ->label('Note')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('designation')
                    ->label('Désignation')
                    ->searchable()
                    ->description(fn (ChantierBudgetLine $record) => $record->note),

                TextColumn::make('quantite')
                    ->label('Qté')
                    ->numeric(decimalPlaces: 3)
                    ->alignRight(),

                TextColumn::make('unite')
                    ->label('Unité')
                    ->placeholder('—'),

                TextColumn::make('cout_unitaire')
                    ->label('Prix U. HT')
                    ->money('EUR')
                    ->alignRight(),

                TextColumn::make('cout_total')
                    ->label('Total HT')
                    ->money('EUR')
                    ->alignRight()
                    ->weight('bold'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(ChantierBudgetType::class),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Phosphor::PlusCircle)
                    ->label('Ajouter une ligne'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return $this->getOwnerRecord()->status->value === 'archived';
    }
}
