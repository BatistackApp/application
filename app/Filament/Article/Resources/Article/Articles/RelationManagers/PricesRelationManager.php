<?php

namespace App\Filament\Article\Resources\Article\Articles\RelationManagers;

use App\Enums\Tiers\TiersCategory;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';
    protected static ?string $title = 'Prix';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columnSpanFull()
                    ->schema([
                        Select::make('price_type')
                            ->label('Type de prix')
                            ->options(TiersCategory::class)
                            ->required(),

                        TextInput::make('amount')
                            ->label('Prix Unitaire HT')
                            ->numeric()
                            ->suffix('€')
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('price_type')
                    ->label('Type de prix'),

                TextColumn::make('amount')
                    ->label('Prix Unitaire HT')
                    ->money('EUR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::Plus)
                    ->color('primary')
                    ->tooltip('Ajouter un prix')
                    ->modalHeading('Nouveau Prix')
                    ->iconButton(),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editer un Prix'),
                DeleteAction::make()
                    ->modalHeading('Supprimer un prix'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->modalHeading('Supprimer la selection des prix'),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
