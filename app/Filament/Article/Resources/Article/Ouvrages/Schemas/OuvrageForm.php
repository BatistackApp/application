<?php

namespace App\Filament\Article\Resources\Article\Ouvrages\Schemas;

use App\Services\Article\OuvrageService;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OuvrageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('sku')
                                    ->columnSpan(1)
                                    ->label('SKU')
                                    ->hint('Référence interne')
                                    ->hintActions([
                                        Action::make('generateSKU')
                                            ->label('Générer')
                                            ->action(function (Set $set) {
                                                return $set('sku', app(OuvrageService::class)->generateWithRetry());
                                            }),
                                    ])
                                    ->required(),

                                TextInput::make('name')
                                    ->label('Désignation')
                                    ->required()
                                    ->columnSpan(2),
                            ]),

                        RichEditor::make('description')
                            ->label('Description'),
                    ]),
            ]);
    }
}
