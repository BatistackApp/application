<?php

namespace App\Filament\Resources\Core\Warehouses;

use App\Filament\Resources\Core\Warehouses\Pages\CreateWarehouse;
use App\Filament\Resources\Core\Warehouses\Pages\EditWarehouse;
use App\Filament\Resources\Core\Warehouses\Pages\ListWarehouses;
use App\Filament\Resources\Core\Warehouses\Schemas\WarehouseForm;
use App\Filament\Resources\Core\Warehouses\Tables\WarehousesTable;
use App\Models\Core\Warehouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WarehouseResource extends Resource
{
    protected static ?string $model = Warehouse::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Dépot';
    protected static string | UnitEnum | null $navigationGroup = 'Système';
    protected static ?string $modelLabel = 'Dépot';
    protected static ?string $pluralModelLabel = 'Depots';

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehousesTable::configure($table);
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }
}
