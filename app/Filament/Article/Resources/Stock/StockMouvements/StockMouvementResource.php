<?php

namespace App\Filament\Article\Resources\Stock\StockMouvements;

use App\Filament\Article\Resources\Stock\StockMouvements\Pages\ListStockMouvements;
use App\Filament\Article\Resources\Stock\StockMouvements\Tables\StockMouvementsTable;
use App\Models\Stock\StockMouvement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class StockMouvementResource extends Resource
{
    protected static ?string $model = StockMouvement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static ?string $navigationLabel = 'Mouvements de stocks';

    protected static ?string $modelLabel = 'Mouvement';

    protected static ?string $pluralModelLabel = 'Mouvements de stock';

    protected static string|UnitEnum|null $navigationGroup = 'Stocks';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return StockMouvementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockMouvements::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }
}
