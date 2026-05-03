<?php

namespace App\Filament\Article\Resources\ArticleCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArticleCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Désignation')
                                    ->required(),

                                Select::make('parent_id')
                                    ->relationship('parent', 'name'),

                                TextInput::make('order')
                                    ->label('Ordre')
                                    ->default(0)
                                    ->numeric(),

                                Toggle::make('is_active')
                                    ->label('Actif')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
