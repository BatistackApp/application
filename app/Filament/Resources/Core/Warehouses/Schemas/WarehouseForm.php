<?php

namespace App\Filament\Resources\Core\Warehouses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')
                            ->label('Désignation')
                            ->required(),

                        TextInput::make('location')
                            ->label('Adresse')
                            ->helperText('Le système calculera automatiquement la position'),

                        Toggle::make('is_active')
                            ->label('Est actif')
                            ->default(true),
                    ]),
            ]);
    }
}
