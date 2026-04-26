<?php

namespace App\Filament\Tiers\Resources\Tiers\RelationManagers;

use App\Enums\Tiers\TiersAddressType;
use App\Models\Tiers\TiersAddress;
use Filament\Actions\ActionGroup;
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
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Adresses Postale';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->columnSpanFull()
                    ->schema([
                        Select::make('address_type')
                            ->label('Type')
                            ->reactive()
                            ->options(TiersAddressType::class),

                        TextInput::make('address_name')
                            ->label('Désignation')
                            ->columnSpan(2)
                            ->required(),
                    ]),

                Textarea::make('address')
                    ->label('Adresse')
                    ->columnSpanFull()
                    ->rows(3)
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

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('phone')
                            ->label('Téléphone'),

                        TextInput::make('email')
                            ->label('Email'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('address_name')
                    ->label('Désignation')
                    ->searchable(),

                TextColumn::make('address_type')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Adresse')
                    ->formatStateUsing(fn (TiersAddress $record) => $record->getFullAddress()),

                TextColumn::make('phone')
                    ->label('Téléphone'),

                TextColumn::make('email')
                    ->label('Email'),
            ])
            ->headerActions([
                CreateAction::make()->label('Ajouter une adresse'),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
