<?php

namespace App\Filament\Article\Resources\Article\Articles\Schemas;

use App\Enums\Article\TrackingType;
use App\Enums\Tiers\TiersCategory;
use App\Enums\UnitOfMesure;
use App\Services\Article\ArticleService;
use App\Services\Core\DeviceDetector;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Marcelorodrigo\FilamentBarcodeScannerField\Forms\Components\BarcodeInput;

class ArticleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information générale')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('sku')
                                    ->label('SKU')
                                    ->hint('Référence interne')
                                    ->hintActions([
                                        Action::make('generateSKU')
                                            ->label('Générer')
                                            ->action(function (Set $set) {
                                                return $set('sku', app(ArticleService::class)->generateSKU());
                                            }),
                                    ])
                                    ->required(),

                                Select::make('article_category_id')
                                    ->label('Catégorie')
                                    ->relationship('articleCategory', 'name')
                                    ->preload()
                                    ->searchable(),

                                Select::make('default_supplier_id')
                                    ->label('Fournisseur par defaut')
                                    ->relationship(
                                        name: 'supplier',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn(Builder $query) => $query->where('category', TiersCategory::Supplier),
                                    )
                                    ->preload()
                                    ->searchable(),
                            ]),

                        TextInput::make('name')
                            ->label('Désignation')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => $set('description', $get('name')))
                            ->required(),

                        RichEditor::make('description')
                            ->label('Description'),
                    ]),

                Section::make('Autres Informations')
                    ->columnSpanFull()
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Select::make('unit')
                            ->label('Unité')
                            ->options(UnitOfMesure::class)
                            ->default(UnitOfMesure::UNIT),

                        Select::make('tracking_type')
                            ->label('Type de tracking')
                            ->options(TrackingType::class)
                            ->default(TrackingType::QUANTITY),

                        BarcodeInput::make('barcode')
                            ->visible(fn () => DeviceDetector::isSmartphone())
                            ->label('Code barre'),

                        BarcodeInput::make('qr_code_base')
                            ->visible(fn () => DeviceDetector::isSmartphone())
                            ->label('QR Code Interne'),

                        TextInput::make('poids')
                            ->label('Poids')
                            ->suffix('Kg'),

                        TextInput::make('volume')
                            ->label('Volume')
                            ->suffix('m3'),

                    ]),
            ]);
    }
}
