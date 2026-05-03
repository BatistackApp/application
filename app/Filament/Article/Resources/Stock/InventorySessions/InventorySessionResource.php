<?php

namespace App\Filament\Article\Resources\Stock\InventorySessions;

use App\Filament\Article\Resources\Stock\InventorySessions\Pages\CreateInventorySession;
use App\Filament\Article\Resources\Stock\InventorySessions\Pages\EditInventorySession;
use App\Filament\Article\Resources\Stock\InventorySessions\Pages\ListInventorySessions;
use App\Filament\Article\Resources\Stock\InventorySessions\Pages\ViewInventorySession;
use App\Filament\Article\Resources\Stock\InventorySessions\Schemas\InventorySessionForm;
use App\Filament\Article\Resources\Stock\InventorySessions\Schemas\InventorySessionInfolist;
use App\Filament\Article\Resources\Stock\InventorySessions\Tables\InventorySessionsTable;
use App\Models\Stock\InventorySession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class InventorySessionResource extends Resource
{
    protected static ?string $model = InventorySession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;
    protected static ?string $navigationLabel = 'Inventaires';
    protected static ?string $modelLabel = 'Inventaire';
    protected static ?string $pluralModelLabel = 'Inventaires';
    protected static string | UnitEnum | null $navigationGroup = 'Stocks';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return InventorySessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return InventorySessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InventorySessionsTable::configure($table);
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
            'index' => ListInventorySessions::route('/'),
            'create' => CreateInventorySession::route('/create'),
            'view' => ViewInventorySession::route('/{record}'),
            'edit' => EditInventorySession::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
