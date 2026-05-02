<?php

namespace App\Filament\Article\Resources\Stock\StockMouvements;

use App\Filament\Article\Resources\Stock\StockMouvements\Pages\CreateStockMouvement;
use App\Filament\Article\Resources\Stock\StockMouvements\Pages\EditStockMouvement;
use App\Filament\Article\Resources\Stock\StockMouvements\Pages\ListStockMouvements;
use App\Filament\Article\Resources\Stock\StockMouvements\Schemas\StockMouvementForm;
use App\Filament\Article\Resources\Stock\StockMouvements\Tables\StockMouvementsTable;
use App\Models\Stock\StockMouvement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StockMouvementResource extends Resource
{
    protected static ?string $model = StockMouvement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;
    protected static ?string $navigationLabel = 'Mouvements de stocks';
    protected static string | UnitEnum | null $navigationGroup = 'Stocks';

    public static function form(Schema $schema): Schema
    {
        return StockMouvementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockMouvementsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMouvements::route('/'),
            'create' => CreateStockMouvement::route('/create'),
            'edit' => EditStockMouvement::route('/{record}/edit'),
        ];
    }
}
